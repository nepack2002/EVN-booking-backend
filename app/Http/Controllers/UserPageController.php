<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\ScheduleLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserPageController extends Controller
{
    public function getDetail(Schedule $schedule)
    {
        $schedule->load('department', 'car');
        return response()->json($schedule);
    }

    public function getCarOfUser(string $id)
    {
        $cars = Car::with('history')->where('user_id', $id)->get();
        $domain = config('app.url');
        foreach ($cars as $car) {
            foreach ($car->history as $item) {
                $item->ngay_bao_duong_gan_nhat = $item->ngay_bao_duong_gan_nhat ? Carbon::createFromFormat('Y-m-d', $item->ngay_bao_duong_gan_nhat)->format('d/m/Y') : null;
                $item->han_dang_kiem_tiep_theo = $item->han_dang_kiem_tiep_theo ? Carbon::createFromFormat('Y-m-d', $item->han_dang_kiem_tiep_theo)->format('d/m/Y') : null;
                $item->tai_lieu = $domain . asset($item->tai_lieu);
            }
        }
        if ($cars) {
            return response()->json($cars);
        } else {
            return response()->json(['message' => 'Không tìm thấy xe cho người dùng này'], 404);
        }
    }

    public function getNotification(string $id)
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
        $userId = Auth::id();
        $schedule = Schedule::findOrFail($id);

        if ($request->input('status') == '1') {
            $cars = Car::where('user_id', $userId)->where('id', '!=', $schedule->car_id)->pluck('id');
            $checkIsThereAnyRunningSchedule = Schedule::whereIn('car_id', $cars)->whereIn('status', ['1','3'])->get();
            if (!$checkIsThereAnyRunningSchedule->isEmpty()) {
                return response()->json(['message' => 'Đang trong một chuyến đi khác']);
            }
        }

        $schedule->status = $request->input('status');
        $schedule->save();

        //ket thuc chuyen di thi tinh toan duong dau xang tieu thu
        if ($request->input('status') == '2') {
            $scheduleLocations = ScheduleLocation::where('schedule_id', $id)->orderBy('created_at', 'ASC')->get();
            $startLocationsString = $schedule->lat_location . ',' . $schedule->long_location;

            $locations = [];
            foreach ($scheduleLocations as $scheduleLocation) {
                $locations[] = $scheduleLocation->lat . ',' . $scheduleLocation->long;
            }
            $locationsString = implode('|', $locations);

            $apiKey = env('GOONG_API_KEY');
            $response = Http::get("https://rsapi.goong.io/DistanceMatrix?origins=$startLocationsString&destinations=$locationsString&vehicle=car&api_key=$apiKey");
            if ($response->successful()) {
                $car = Car::findOrFail($schedule->car_id);

                $rows = $response->json('rows');
                $row = $rows[0];
                $elements = $row["elements"];
                foreach ($elements as $k => $element) {
                    $scheduleLocations[$k]->so_dau_xang_tieu_thu = ($element["distance"]["value"]/1000) * ($car->so_dau_xang_tieu_thu/100);
                    $scheduleLocations[$k]->save();
                }
            }
        }
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

        $schedule = Schedule::findOrFail($id);
        $schedule->total_running_time = request()->input('total_running_time');
        $schedule->save();

        return response()->json(['message' => 'Car location updated successfully']);
    }

    public function sendTime(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->total_running_time = request()->input('total_running_time');
        $schedule->save();

        return response()->json(['message' => 'Car running time updated successfully']);
    }

    public function x(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->location_2 = $request->input('location_2');
        $schedule->lat_location_2 = $request->input('lat_location_2');
        $schedule->long_location_2 = $request->input('long_location_2');
        $schedule->save();
    }

    public function getSchedulesGroupedByDate($userId)
    {
        $now = Carbon::today();
        $cars = Car::where('user_id', $userId)->pluck('id');
        $schedules = Schedule::with('car')
            ->whereIn('car_id', $cars)
            ->where(function ($query) use ($now) {
                $query->where('datetime', '>=', $now)
                    ->orWhere('status', '=', '1');
            })
            ->orderBy('datetime')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->datetime)->format('Y-m-d');
            });

        return response()->json($schedules);
    }

    public function getCurrentRunningSchedule($userId)
    {
        $cars = Car::where('user_id', $userId)->pluck('id');
        $schedules = Schedule::with('car')
            ->whereIn('car_id', $cars)
            ->where('status', '=', '1')
            ->orWhere('status', '=', '3')
            ->first();

        return response()->json($schedules);
    }

    public function getLocation(string $id)
    {
        $locations = ScheduleLocation::where('schedule_id', $id)->get();
        return response()->json($locations);
    }
}
