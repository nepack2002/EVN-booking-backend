<?php

namespace App\Imports;

use App\Models\Schedule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class SchedulesImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $validator = Validator::make($row->toArray(), [
                'department_id' => 'required|exists:departments,id',
                'datetime' => 'required|date',
                'location' => 'required|string',
                'lat_location' => 'nullable|numeric',
                'long_location' => 'nullable|numeric',
                'location_2' => 'required|string',
                'lat_location_2' => 'nullable|numeric',
                'long_location_2' => 'nullable|numeric',
                'car_id' => 'required|exists:cars,id',
                'participants' => 'required|string',
                'program' => 'required|string',
            ]);
            if ($validator->fails()) {
                continue; // Bỏ qua dữ liệu không hợp lệ và tiếp tục với dữ liệu tiếp theo
            }
            Schedule::create([
                'department_id' => $row['department_id'],
                'datetime' => $row['datetime'],
                'location' => $row['location'],
                'lat_location' => $row['lat_location'],
                'long_location' => $row['long_location'],
                'car_id' => $row['car_id'],
                'participants' => $row['participants'],
                'program' => $row['program'],
            ]);
        }
    }
}