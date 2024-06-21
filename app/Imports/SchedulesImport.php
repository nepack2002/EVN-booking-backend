<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\Department;
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
                'phong_ban' => 'required|exists:departments,name',
                'thoi_gian_bat_dau' => 'required|datetime',
                'diem_xuat_phat' => 'required|string',
                'diem_ket_thuc' => 'required|string',
                'bien_so_xe_duoc_phan_cong' => 'required|exists:cars,bien_so_xe',
                'nguoi_tham_gia' => 'required|string',
                'ten_lich_trinh' => 'required|string',
            ]);
            if ($validator->fails()) {
                continue; // Bỏ qua dữ liệu không hợp lệ và tiếp tục với dữ liệu tiếp theo
            }
            $department = Department::where('name','=',$row['phong_ban'])->first();
            $car = Car::where('bien_so_xe', $row['bien_so_xe'])->first();
            Schedule::create([
                'department_id' => $department->id,
                'datetime' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['thoi_gian_bat_dau'])->format('Y-m-d H:i:s'),
                'location' => $row['diem_xuat_phat'],
                'location_2' => $row['diem_ket_thuc'],
                'car_id' => $car->id,
                'participants' => $row['nguoi_tham_gia'],
                'program' => $row['ten_lich_trinh'],
            ]);
        }
    }
}
