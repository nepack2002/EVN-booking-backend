<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Car;
use App\Models\ScheduleLocation;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserPageController extends Controller
{
    public function getDetail(Schedule $schedule)
    {
        $schedule->load('department', 'car');
        return response()->json($schedule);
    }
    public function getCarOfUser(String $id)
    {
        $car = Car::where('user_id', $id)->first();
        if ($car) {
            return response()->json($car);
        } else {
            return response()->json(['message' => 'Không tìm thấy xe cho người dùng này'], 404);
        }
    }
    public function getNotification(String $id)
    {
        $notification = Notification::where('user_id', $id)->get();
        return response()->json($notification);
    }
    public function getNotificationUnRead(int $id)
    {
        $notificationCount = Notification::where('user_id', $id)->where('read', 0)->count();
        return response()->json($notificationCount);
    }
    public function markAsRead(Notification $notification)
    {
        $notification->update(['read' => 1]);

        return response()->json(['message' => 'Notification marked as read']);
    }
    public function getUserSchedules($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->isUser()) {
            // Lấy danh sách các xe của người dùng
            $cars = Car::where('user_id', $userId)->pluck('id');

            // Lấy danh sách các lịch trình mà các xe của người dùng tham gia
            $schedules = Schedule::with('car')->whereIn('car_id', $cars)->get();

            return response()->json($schedules);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function updateRun(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->status = $request->input('status');
        $schedule->save();

        return response()->json(['message' => 'Car run value updated successfully']);
    }

    public function sendLocation(Request $request, $id)
    {
        $lat = $request->input('lat_location');
        $long = $request->input('long_location');
        $location = $request->input('location');

        $carId = Schedule::findOrFail($id)->pluck('car_id')->first();
        $car = Car::findOrFail($carId);

        $car->lat_location = $lat;
        $car->long_location = $long;
        $car->location = $location;
        $car->save();

        $scheduleLocation = new ScheduleLocation();
        $scheduleLocation->schedule_id = $id;
        $scheduleLocation->lat = $lat;
        $scheduleLocation->long = $long;
        $scheduleLocation->location = $location;


        $scheduleLocation->save();

      

        return response()->json(['message' => 'Car location updated successfully']);
    }
    public function x(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->location_2 = $request->input('location_2');
        $schedule->lat_location_2 = $request->input('lat_location_2');
        $schedule->long_location_2 = $request->input('long_location_2');
        $schedule->save();
    }
    public function getSchedulesGroupedByDate()
    {
        $now = Carbon::now();

        $schedules = Schedule::where('datetime', '>=', $now)
            ->orderBy('datetime')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->datetime)->format('Y-m-d');
            });

        return response()->json($schedules);
    }
    public function getLocation(String $id)
    {
        $locations = ScheduleLocation::where('schedule_id', $id)->get();
        return response()->json($locations);
    }
}