<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\Notification;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduleNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thong bao lich sap dien ra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $formattedTime = date('Y-m-d H:i:s', $now->timestamp);

        $expiredSchedules = Schedule::where('datetime', '>=', $now)
            ->where('status', '0')
            ->get();

        foreach ($expiredSchedules as $schedule) {
            $scheduleTime = Carbon::parse($schedule->datetime);
            $diffInHours = $scheduleTime->diffInHours($formattedTime);
            if ($diffInHours <= 1) {
                $userId = Car::where('car_id', $schedule->car_id)->value('user_id');
                $message = "Còn 1 giờ nữa là tới lịch {$schedule->program} vào lúc {$scheduleTime->format('H:i:s d/m/Y')}. Xe di chuyển từ {$schedule->location} tới {$schedule->location_2}";
                $notification = new Notification();
                $notification->user_id = $userId;
                $notification->message = $message;
                $notification->save();

                $notificationGen = new NotificationGenerate();
                $notificationGen->sendNotificationByUserId($userId, $message);
            }
        }
    }
}
