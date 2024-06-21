<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
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
                    'user_id' => $user->id??'',
                    'so_khung' => $row['so_khung'],
                    'so_may' => $row['so_may'],
                    'so_cho' => $row['so_cho'],
                    'so_dau_xang_tieu_thu' => $row['so_dau_xang_tieu_thu'],
                    'ngay_bao_duong_gan_nhat' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_bao_duong_gan_nhat'])->format('Y-m-d'),
                    'han_dang_kiem_tiep_theo' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['han_dang_kiem_tiep_theo'])->format('Y-m-d'),
                    'ngay_sua_chua_lon_gan_nhat' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_sua_chua_lon_gan_nhat'])->format('Y-m-d'),
                ]);
            } else {
                Car::create([
                    'ten_xe' => $row['ten_xe'],
                    'mau_xe' => $row['mau_xe'],
                    'user_id' => $user->id??'',
                    'bien_so_xe' => $row['bien_so_xe'],
                    'so_khung' => $row['so_khung'],
                    'so_may' => $row['so_may'],
                    'so_cho' => $row['so_cho'],
                    'so_dau_xang_tieu_thu' => $row['so_dau_xang_tieu_thu'],
                    'ngay_bao_duong_gan_nhat' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_bao_duong_gan_nhat'])->format('Y-m-d'),
                    'han_dang_kiem_tiep_theo' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['han_dang_kiem_tiep_theo'])->format('Y-m-d'),
                    'ngay_sua_chua_lon_gan_nhat' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_sua_chua_lon_gan_nhat'])->format('Y-m-d'),
                    'location' => ''
                ]);
            }
        }
    }
}
