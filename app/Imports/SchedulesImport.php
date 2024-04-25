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
            // Validate dữ liệu từ các hàng
            $validator = Validator::make($row->toArray(), [
                'department_id' => 'required|exists:departments,id',
                'datetime' => 'required|date',
                'location' => 'required|string',
                'lat_location' => 'nullable|numeric',
                'long_location' => 'nullable|numeric',
                'car_id' => 'required|exists:cars,id',
                'participants' => 'required|string',
                'program' => 'required|string',
            ]);

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