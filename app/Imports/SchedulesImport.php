<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\Department;
use App\Models\Schedule;
use Carbon\Carbon;
use Exception;
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
                'datetime' => $this->handleDateRow($row['thoi_gian_bat_dau']),
                'location' => $row['diem_xuat_phat'],
                'location_2' => $row['diem_ket_thuc'],
                'car_id' => $car->id,
                'participants' => $row['nguoi_tham_gia'],
                'program' => $row['ten_lich_trinh'],
            ]);
        }
    }


    public function handleDateRow($data) {
        if (empty($data)) return null;
        if (gettype($data) === "integer") {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data)->format('Y-m-d H:i:s');
        }
        $data = trim($data);
        $format = $this->detectDateFormat($data);
        return Carbon::createFromFormat($format,$data)->format('Y-m-d H:i:s');
    }

    function sanitizeDate($date) {
        return trim(preg_replace('/[^\d\/\-\. ]+/', '', $date)); // Allow only numbers, slashes, hyphens, dots, and spaces
    }
    /**
     * @throws Exception
     */
    public function detectDateFormat($date) {
        $date = $this->sanitizeDate($date);
        $formats = [
            'd/m/Y' => '/^\d{1,2}\/\d{1,2}\/\d{4}$/',       // 20/11/2023
            'd/m/y' => '/^\d{1,2}\/\d{1,2}\/\d{2}$/',       // 20/11/23
            'd-m-Y' => '/^\d{1,2}-\d{1,2}-\d{4}$/',         // 20-11-2023
            'd-m-y' => '/^\d{1,2}-\d{1,2}-\d{2}$/',         // 20-11-23
            'd.M.Y' => '/^\d{1,2}\.\d{1,2}\.\d{4}$/',       // 20.11.2023
            'd.M.y' => '/^\d{1,2}\.\d{1,2}\.\d{2}$/',       // 20.11.23
            'Y-m-d' => '/^\d{4}-\d{2}-\d{2}$/',             // 2023-11-20
            'm/d/Y' => '/^\d{1,2}\/\d{1,2}\/\d{4}$/',       // 11/20/2023
            'm/d/y' => '/^\d{1,2}\/\d{1,2}\/\d{2}$/',       // 11/20/23
            'm-d-Y' => '/^\d{1,2}-\d{1,2}-\d{4}$/',         // 11-20-2023
            'm-d-y' => '/^\d{1,2}-\d{1,2}-\d{2}$/',         // 11-20-23
            'd-M-Y' => '/^\d{1,2}-[a-zA-Z]{3}-\d{4}$/',     // 20-Nov-2023
            'd-M-y' => '/^\d{1,2}-[a-zA-Z]{3}-\d{2}$/',     // 20-Nov-23
            'd M Y' => '/^\d{1,2}\s[a-zA-Z]{3,}\s\d{4}$/',  // 20 Nov 2023
            'd M y' => '/^\d{1,2}\s[a-zA-Z]{3,}\s\d{2}$/',  // 20 Nov 23
            // Add more formats as needed
        ];

        foreach ($formats as $format => $regex) {
            if (preg_match($regex, $date)) {
                return $format;
            }
        }

        throw new Exception("Unknown date format: $date");
    }
}
