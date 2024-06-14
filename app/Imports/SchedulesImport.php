<?php

namespace App\Imports;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
                'location_2' => 'required|string',
                'car_id' => 'required|exists:cars,id',
                'participants' => 'required|string',
                'program' => 'required|string',
            ]);
            if ($validator->fails()) {
                continue; // Bỏ qua dữ liệu không hợp lệ và tiếp tục với dữ liệu tiếp theo
            }
            Schedule::create([
                'department_id' => $row['department_id'],
                'datetime' => Carbon::createFromFormat('d/m/Y H:i',$row['datetime'])->format('Y-m-d H:i:s'),
                'location' => $row['location'],
                'location_2' => $row['location_2'],
                'car_id' => $row['car_id'],
                'participants' => $row['participants'],
                'program' => $row['program'],
            ]);
        }
    }
}
