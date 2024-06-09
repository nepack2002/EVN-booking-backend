<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Carbon\Carbon;

class Date extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'date:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiem tra các lịch di chuyển quên chưa đóng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $expiredSchedules = Schedule::where('datetime', '<', $now)
            ->where('status', '!=', '2')
            ->get();

        foreach ($expiredSchedules as $schedule) {
            $schedule->status = '2';
            $schedule->save();
        }

        $this->info('Expired schedules have been updated.');
    }
}
