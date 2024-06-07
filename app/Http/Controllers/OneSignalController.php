<?php

namespace App\Http\Controllers;

use App\Models\DeviceId;
use Illuminate\Http\Request;

class OneSignalController extends Controller
{
    public function sendOneSignal(Request $request)
    {
        $request->validate([
            'onesignal_id' => 'required',
            'user_id' => 'required',
        ]);

        $existingDevice = DeviceId::where('onesignal_id', $request->onesignal_id)->first();

        if ($existingDevice) {
            $existingDevice->user_id = $request->user_id;
            $existingDevice->save();

            return response()->json(['message' => 'Device ID updated successfully', 'device_id' => $existingDevice], 200);
        }

        // If the onesignal_id does not exist, create a new DeviceId record
        $deviceId = DeviceId::create([
            'onesignal_id' => $request->onesignal_id,
            'user_id' => $request->user_id,
        ]);

        // Return a JSON response
        return response()->json(['message' => 'Device ID created successfully', 'device_id' => $deviceId], 201);
    }
}