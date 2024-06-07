<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeviceId;
use Illuminate\Support\Facades\Http;

class SendOneSignalNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to devices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $devices = DeviceId::all();
        foreach ($devices as $device) {
            $this->info($device->onesignal_id);
            $this->sendNotification($device->onesignal_id, 'Bạn có thông báo');
        }
        $this->info('Notifications sent successfully.');
    }
    private function sendNotification($onesignal_id, $message)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => env('ONESIGNAL_APP_ID'),
            'include_player_ids' => [$onesignal_id],
            'contents' => ['en' => $message],
        ]);
        if ($response->failed()) {
            $this->info('OneSignal API Error: ' . $response->body());
        }
    }
}