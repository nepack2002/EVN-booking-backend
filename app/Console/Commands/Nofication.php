<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Schedule;
use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Nofication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nofication:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */


    public function handle()
    {
        $schedules = Schedule::all();
        $cars = Car::all();
        $user_ids = [];
        $currentTime = now();
        // Định dạng thời gian theo định dạng cụ thể
        $formattedTime = date('Y-m-d H:i:s', $currentTime->timestamp);

        foreach ($cars as $car){
            $user_ids[$car->id] = $car->user_id; // Mảng user_id theo thứ tự xe
            $carTime = Carbon::parse($car->han_dang_kiem_tiep_theo);
            if ($carTime->isPast()) {
                
            }else{
                $diffInHours = $carTime->diffInHours($formattedTime);
                if ($diffInHours == 24){
                    $notification = new Notification();
                    $notification->user_id = $user_ids[$car->id];
                    $notification->message = "Bạn còn 1 ngày cho hạn đăng kiểm sắp tới";
                    $notification->save();
                }else{
                    $this->info("Thời gian lịch hẹn: " . $carTime);
                    $this->info("Thời gian còn lại: " . $diffInHours);
                }
            }
        }

        foreach ($schedules as $schedule) {
            $scheduleTime = Carbon::parse($schedule->datetime);
            $user_id = Car::where('id', $schedule->car_id)->value('user_id');
            $user_ids[$schedule->id] = $user_id; // Mảng user_id theo thứ tự schedule

            if ($scheduleTime->isPast()) {
                // Sự kiện đã quá hạn
            } else {
                
                // $this->info('Thời gian hiện tại: ' . $now);
                $diffInHours = $scheduleTime->diffInHours($formattedTime);

                // Kiểm tra nếu còn 1 ngày hoặc 1 tiếng, thêm thông báo tương ứng
                if ($diffInHours == 24) {
                    $notification = new Notification();
                    $notification->user_id = $user_ids[$schedule->id];
                    $notification->message = "Bạn còn 1 ngày cho sự kiện sắp tới";
                    $notification->save();
                } elseif ($diffInHours == 1) {
                    $notification = new Notification();
                    $notification->user_id = $user_ids[$schedule->id];
                    $notification->message = "Bạn còn 1 giờ cho sự kiện sắp tới";
                    $notification->save();
                } else {
                    $this->info("Thời gian lịch hẹn: " . $scheduleTime);
                    $this->info("Thời gian còn lại: " . $diffInHours);
                }
            }
        }
    }
}