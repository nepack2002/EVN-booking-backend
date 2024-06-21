<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\DeviceId;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class NotificationGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo hàng ngày tới admin và lái xe';

    /**
     * Execute the console command.
     */


    public function handle()
    {
        $schedules = Schedule::where('datetime', '>=', Carbon::now())->get();
        $cars = Car::all();
        $admins = User::where('role', 'admin')->orWhere('role', 'qtct')->get();

        $user_ids = [];
        $currentTime = now();
        $formattedTime = date('Y-m-d H:i:s', $currentTime->timestamp);

        foreach ($cars as $car) {
            $user_ids[$car->id] = $car->user_id; // Mảng user_id theo thứ tự xe

            // Kiểm tra hạn đăng kiểm
            $carTime = Carbon::parse($car->han_dang_kiem_tiep_theo);
            if (!$carTime->isPast()) {
                $diffInDays = $carTime->diffInDays($formattedTime);
                // Neu han dang kiem con duoi 30 ngay va admin chua sua lich dang kiem tiep theo
                if ($diffInDays <= 30) {
                    $message = "Bạn còn $diffInDays ngày cho hạn đăng kiểm {$carTime->format('d/m/Y')} sắp tới của xe {$car->ten_xe}: {$car->bien_so_xe}. Bạn lưu ý đăng kiểm xe đsung thời hạn.";

                    //Gui thong bao cho tai xe
                    $notification = new Notification();
                    $notification->user_id = $car->user_id;
                    $notification->message = $message;
                    $this->sendNotificationByUserId($car->user_id, $message);
                    $notification->save();

                    //Gui thong bao cho admin
                    foreach ($admins as $admin) {
                        $notification = new Notification();
                        $notification->user_id = $admin->id;
                        $notification->message = $message;
                        $this->sendNotificationByUserId($admin->id, $message);
                        $notification->save();
                    }
                } else {
                    $this->info("Thời gian hạn đăng kiểm: " . $carTime);
                    $this->info("Thời gian còn lại: " . $diffInDays . " ngày");
                }
            }

            // Kiểm tra hạn bảo dưỡng gần nhất da duoc 6 thang hay chua
            $diffInDays = $currentTime->diffInDays($car->ngay_bao_duong_gan_nhat);
            if ($diffInDays >= 180) {
                $message = "Đã $diffInDays ngày từ ngày bảo dưỡng gần nhất của xe {$car->ten_xe} - {$car->bien_so_xe}. Bạn lưu ý đưa xe bảo dưỡng đúng ngày";
                $notification = new Notification();
                $notification->user_id = $car->user_id;
                $notification->message = $message;
                $this->sendNotificationByUserId($car->user_id, $message);
                $notification->save();

                //Gui thong bao cho admin
                foreach ($admins as $admin) {
                    $notification = new Notification();
                    $notification->user_id = $admin->id;
                    $notification->message = $message;
                    $this->sendNotificationByUserId($admin->id, $message);
                    $notification->save();
                }
            }

            // Kiểm tra hạn bảo dưỡng gần nhất da duoc 6 thang hay chua
            $diffInDays = $currentTime->diffInDays($car->ngay_sua_chua_lon_gan_nhat);
            if ($diffInDays >= 180) {
                $message = "Đã $diffInDays ngày từ sửa chữa lớn gần nhất của xe {$car->ten_xe} - {$car->bien_so_xe}. Bạn lưu ý đưa xe bảo dưỡng đúng ngày";
                $notification = new Notification();
                $notification->user_id = $car->user_id;
                $notification->message = $message;
                $this->sendNotificationByUserId($car->user_id, $message);
                $notification->save();

                //Gui thong bao cho admin
                foreach ($admins as $admin) {
                    $notification = new Notification();
                    $notification->user_id = $admin->id;
                    $notification->message = $message;
                    $this->sendNotificationByUserId($admin->id, $message);
                    $notification->save();
                }
            }
        }

        foreach ($schedules as $schedule) {
            $scheduleTime = Carbon::parse($schedule->datetime);
            $user_id = $user_ids[$schedule->car_id];

            if (!$scheduleTime->isPast()) {
                $diffInDays = $scheduleTime->diffInDays($formattedTime);

                // Kiểm tra nếu còn 1 ngày thêm thông báo tương ứng
                if ($diffInDays === 0 || $diffInDays === 1) {
                    //Gui thong bao cho tai xe
                    if ($diffInDays === 1) {
                        $message = "Ngày mai bạn có lịch: {$schedule->program} vào lúc {$scheduleTime->format('H:i:s d/m/Y')}. Xe di chuyển từ {$schedule->location} tới {$schedule->location_2}";
                    } else {
                        $message = "Hôm nay bạn có lịch: {$schedule->program} vào lúc {$scheduleTime->format('H:i:s d/m/Y')}. Xe di chuyển từ {$schedule->location} tới {$schedule->location_2}";
                    }
                    $notification = new Notification();
                    $notification->user_id = $user_id;
                    $notification->message = $message;
                    $this->sendNotificationByUserId($user_id, $message);
                    $notification->save();
                } else {
                    $this->info("Thời gian lịch hẹn: " . $scheduleTime);
                    $this->info("Thời gian còn lại: " . $diffInDays . " ngày");
                }
            }
        }
    }

    public function sendNotificationByUserId($userId, $message)
    {
        $devices = DeviceId::where('user_id', $userId)->get();
        $include_player_ids = [];

        foreach ($devices as $device) {
            $include_player_ids[] = $device->onesignal_id;
        }
        $this->info("$userId: $message");
        $this->sendNotification($include_player_ids, $message);
    }

    public function sendNotification($onesignal_id, $message): void
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => env('ONESIGNAL_APP_ID'),
            'include_player_ids' => $onesignal_id,
            'contents' => ['en' => $message],
        ]);
        if ($response->failed()) {
            $this->info('OneSignal API Error: ' . $response->body());
        }
    }
}
