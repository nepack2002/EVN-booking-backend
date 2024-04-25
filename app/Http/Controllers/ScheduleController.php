<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SchedulesImport;
class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::paginate(5);
        return response()->json($schedules);
    }
    public function show(Schedule $schedule)
    {
        return response()->json($schedule);
    }
    public function add(Request $request)
    {
        // Validate dữ liệu đầu vào từ request
        $validatedData = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'datetime' => 'required|date',
            'location' => 'required|string',
            'lat_location' => 'nullable|numeric',
            'long_location' => 'nullable|numeric',
            'car_id' => 'required|exists:cars,id',
            'participants' => 'required|string',
            'program' => 'required|string',
        ]);

        // Tạo mới một schedule từ dữ liệu được validate
        $schedule = Schedule::create($validatedData);

        // Trả về response thành công nếu tạo schedule thành công
        return response()->json(['message' => 'Schedule created successfully', 'schedule' => $schedule], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'datetime' => 'required|date',
            'location' => 'required|string',
            'lat_location' => 'nullable|numeric',
            'long_location' => 'nullable|numeric',
            'car_id' => 'required|exists:cars,id',
            'participants' => 'required|string',
            'program' => 'required|string',
        ]);

        $schedule->update($request->all());

        return response()->json($schedule, 200);
    }

    public function destroy($id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }
        $schedule->delete();
        return response()->json(['message' => 'Schedule deleted successfully'], 200);
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
}