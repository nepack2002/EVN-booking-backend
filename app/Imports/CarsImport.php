<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CarsImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $car = Car::where('bien_so_xe', $row['bien_so_xe'])
                ->first();
            $user = User::where('username', '=', $row['ten_dang_nhap_lai_xe'])->first();

            if ($car) {
                $car->update([
                    'ten_xe' => $row['ten_xe'],
                    'mau_xe' => $row['mau_xe'],
                    'user_id' => $user->id??0,
                    'so_khung' => $row['so_khung'],
                    'so_may' => $row['so_may'],
                    'so_cho' => $row['so_cho'],
                    'so_dau_xang_tieu_thu' => $row['so_dau_xang_tieu_thu'],
                    'ngay_bao_duong_gan_nhat' => $this->handleDateRow($row['ngay_bao_duong_gan_nhat']),
                    'han_dang_kiem_tiep_theo' => $this->handleDateRow($row['han_dang_kiem_tiep_theo']),
                    'ngay_sua_chua_lon_gan_nhat' => $this->handleDateRow($row['ngay_sua_chua_lon_gan_nhat']),
                ]);
            } else {
                Car::create([
                    'ten_xe' => $row['ten_xe'],
                    'mau_xe' => $row['mau_xe'],
                    'user_id' => $user->id??0,
                    'bien_so_xe' => $row['bien_so_xe'],
                    'so_khung' => $row['so_khung'],
                    'so_may' => $row['so_may'],
                    'so_cho' => $row['so_cho'],
                    'so_dau_xang_tieu_thu' => $row['so_dau_xang_tieu_thu'],
                    'ngay_bao_duong_gan_nhat' => $this->handleDateRow($row['ngay_bao_duong_gan_nhat']),
                    'han_dang_kiem_tiep_theo' => $this->handleDateRow($row['han_dang_kiem_tiep_theo']),
                    'ngay_sua_chua_lon_gan_nhat' => $this->handleDateRow($row['ngay_sua_chua_lon_gan_nhat']),
                    'location' => ''
                ]);
            }
        }
    }

    public function handleDateRow($data) {
        if (empty($data)) return null;
        if (gettype($data) === "integer") {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data)->format('Y-m-d');
        }
        $data = trim($data);
        $format = $this->detectDateFormat($data);
        return Carbon::createFromFormat($format,$data)->format('Y-m-d');
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
