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
        // Load các quan hệ 'department' và 'car'
        $schedule->load('department', 'car');

        // Trả về JSON với dữ liệu đã được load
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

            // Trả về danh sách các lịch trình mà các xe của người dùng tham gia
            return response()->json($schedules);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function updateRun(Request $request, $id)
    {
        // Lấy thông tin của lịch trình cần cập nhật
        $schedule = Schedule::findOrFail($id);

       
        $schedule->status = $request->input('status');
        $schedule->save();

        return response()->json(['message' => 'Car run value updated successfully']);

    }

    public function sendLocation(Request $request, $id)
    {
        $carId = Schedule::findOrFail($id)->pluck('car_id')->first();
        $car = Car::findOrFail($carId);
        $scheduleLocation = new ScheduleLocation();
        $car->lat_location = $request->input('lat_location');
        $car->long_location = $request->input('long_location');
        $scheduleLocation->schedule_id = $id;
        $scheduleLocation->lat = $request->input('lat_location');
        $scheduleLocation->long = $request->input('long_location');
        $scheduleLocation->location = $request->input('location');
        $car->save();
        $scheduleLocation->save();
    }
    public function getSchedulesGroupedByDate()
    {
        $now = Carbon::now();

        $schedules = Schedule::where('datetime', '>=', $now)
            ->orderBy('datetime')
            ->get()
            ->groupBy(function ($date) {
                // Ở đây ta sử dụng Carbon để định dạng datetime
                return Carbon::parse($date->datetime)->format('Y-m-d');
            });

        return response()->json($schedules);
    }
    public function getLocation(String $id){
        $locations = ScheduleLocation::where('schedule_id',$id)->get();
        return response()->json($locations);
    }
}