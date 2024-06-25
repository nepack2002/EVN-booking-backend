<?php

namespace App\Http\Controllers;

use App\Console\Commands\NotificationGenerate;
use App\Imports\SchedulesImport;
use App\Models\Car;
use App\Models\Notification;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('query');
        if (!empty($query)) {
            $schedules = Schedule::where('program', 'like', '%' . $query . '%')
                ->paginate(5);
        } else {
            $schedules = Schedule::paginate(5);
        }
        foreach ($schedules as $schedule) {
            $schedule->datetime = Carbon::parse($schedule->datetime)->format('d/m/Y H:i');
        }
        return response()->json($schedules);
    }

    public function show(Schedule $schedule)
    {
        $schedule->datetime = Carbon::parse($schedule->datetime)->format('d/m/Y H:i');
        return response()->json($schedule);
    }

    public function add(Request $request)
    {
        // Validate dữ liệu đầu vào từ request
        $validatedData = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'datetime' => 'required|date_format:d/m/Y H:i',
            'location' => 'required|string',
            'lat_location' => 'nullable|numeric',
            'long_location' => 'nullable|numeric',
            'location_2' => 'string|nullable',
            'lat_location_2' => 'nullable|numeric',
            'long_location_2' => 'nullable|numeric',
            'car_id' => 'required|exists:cars,id',
            'participants' => 'required|string',
            'program' => 'required|string',
            'tai_lieu' => 'required|file|mimes:pdf',
        ]);
        $dateTimeBackup =$validatedData['datetime'];
        $validatedData['datetime'] = Carbon::createFromFormat('d/m/Y H:i', $validatedData['datetime'])->format('Y-m-d H:i:s');

        $file_name = $request->file('tai_lieu')->getClientOriginalName();
        $request->file('anh_xe')->move(public_path('documents'), $file_name);
        $file_path = 'documents/' . $file_name;
        $validatedData['tai_lieu'] = $file_path;
        $validatedData['ten_tai_lieu'] = $file_name;

        // Tạo mới một schedule từ dữ liệu được validate
        $schedule = Schedule::create($validatedData);

        $car = Car::findOrFail($validatedData['car_id']);
        if (!empty($car->user_id)) {
            $message = "Bạn vừa được phân công lịch {$validatedData['program']}. Lịch trình bắt đầu lúc {$dateTimeBackup}. Địa điểm di chuyển từ {$validatedData['location']} tới {$validatedData['location_2']}";
            $notification = new Notification();
            $notification->user_id = $car->user_id;
            $notification->message = $message;
            $notification->save();

            $notificationGen = new NotificationGenerate();
            $notificationGen->sendNotificationByUserId($car->user_id, $message);
        }
        // Trả về response thành công nếu tạo schedule thành công
        return response()->json(['message' => 'Tạo lịch trình thành công', 'schedule' => $schedule], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Lịch trình không tồn tại'], 404);
        }

        $validatedData = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'datetime' => 'required|date_format:d/m/Y H:i',
            'location' => 'required|string',
            'lat_location' => 'nullable|numeric',
            'long_location' => 'nullable|numeric',
            'location_2' => 'string|nullable',
            'lat_location_2' => 'nullable|numeric',
            'long_location_2' => 'nullable|numeric',
            'car_id' => 'required|exists:cars,id',
            'participants' => 'required|string',
            'program' => 'required|string',
            'tai_lieu' => 'required|file|mimes:pdf',
        ]);
        $validatedData['datetime'] = Carbon::createFromFormat('d/m/Y H:i', $validatedData['datetime'])->format('Y-m-d H:i:s');

        $file_name = $request->file('tai_lieu')->getClientOriginalName();
        $request->file('anh_xe')->move(public_path('documents'), $file_name);
        $file_path = 'documents/' . $file_name;
        $validatedData['tai_lieu'] = $file_path;
        $validatedData['ten_tai_lieu'] = $file_name;

        $schedule->update($validatedData);

        return response()->json($schedule, 200);
    }

    public function destroy($id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Lịch trình không tồn tại'], 404);
        }
        $schedule->delete();
        return response()->json(['message' => 'Xoá lịch trình thành công'], 200);
    }

    public function import(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Import schedules from the Excel file
        try {
            Excel::import(new SchedulesImport, $request->file('file'));
        } catch (\Exception $e) {
            // Handle any exceptions that occur during import
            return response()->json(['error' => 'Error occurred during import'], 500);
        }
        // Return success response
        return response()->json(['success' => true]);
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Convert độ sang radian
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Tính toán khoảng cách giữa hai điểm sử dụng Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = pow(sin($dlat / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dlon / 2), 2);
        $c = 2 * asin(sqrt($a));

        // Bán kính trái đất (đơn vị: km)
        $earthRadius = 6371;

        // Khoảng cách giữa hai điểm (đơn vị: km)
        $distance = $earthRadius * $c;

        return $distance;
    }

    public function storeCoordinates(Request $request)
    {
        // Lấy dữ liệu `lat` và `long` từ request
        $lat = $request->lat ?? null;
        $long = $request->long ?? null;
        $soCho = $request->so_cho ?? 0;

        // Tính toán khoảng cách với từng car
        $cars = Car::where('so_cho', '>=', $soCho)->orderBy('so_cho', 'ASC')->get();
        $distances = [];
        $carsFormat = [];

        foreach ($cars as $car) {
            $carsFormat[$car->id] = $car;
            $carLat = $car->lat_location;
            $carLong = $car->long_location;

            // Kiểm tra xem lat_location và long_location của car có đều là null không
            if ($carLat !== null && $carLong !== null && $lat !== null && $long !== null) {
                // Tính toán khoảng cách bằng công thức Haversine
                $distance = $this->haversineDistance($lat, $long, $carLat, $carLong);
            } else {
                $distance = 0; // Nếu có ít nhất một trong các giá trị là null, đặt khoảng cách bằng 0
            }

            // Lưu khoảng cách vào mảng
            $distances[$car->id] = [
                "distance" => $distance,
                "so_cho" => $car->so_cho,
            ];
        }

        uasort($distances, function ($a, $b) {
            // First, compare so_cho
            if ($a['so_cho'] == $b['so_cho']) {
                // If so_cho is the same, compare distance
                return $a['distance'] <=> $b['distance'];
            }
            // Otherwise, compare so_cho
            return $a['so_cho'] <=> $b['so_cho'];
        });

        $carsWithDistance = [];
        foreach ($distances as $carId => $distance) {
            // Lấy tên của xe
            $carName = $carsFormat[$carId]->ten_xe;

            // Tạo mảng chứa thông tin về xe và khoảng cách
            $carWithDistance = [
                'car_id' => $carId,
                'name' => $carName,
                'distance' => $distance["distance"],
                'so_cho' => $distance["so_cho"],
            ];
            $carsWithDistance[] = $carWithDistance;
        }

        return $carsWithDistance;
    }
}
