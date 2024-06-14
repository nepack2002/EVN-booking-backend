<?php

namespace App\Imports;

use App\Models\Car;
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
                ->where('so_khung', $row['so_khung'])
                ->first();

            // Check if user already has a different car
            $existingCar = Car::where('user_id', $row['user_id'])->where('id', '!=', $car ? $car->id : null)->first();
            if ($existingCar) {
                continue;
            }

            if ($car) {
                $car->update([
                    'ten_xe' => $row['ten_xe'],
                    'mau_xe' => $row['mau_xe'],
                    'user_id' => $row['user_id'],
                    'so_cho' => $row['so_cho'],
                    'dac_diem_mac_dinh' => $row['dac_diem_mac_dinh'],
                    'so_dau_xang_tieu_thu' => $row['so_dau_xang_tieu_thu'],
                    'ngay_bao_duong_gan_nhat' => $row['ngay_bao_duong_gan_nhat'],
                    'han_dang_kiem_tiep_theo' => $row['han_dang_kiem_tiep_theo'],
                    'anh_xe' => $row['anh_xe'],
                ]);
            } else {
                Car::create([
                    'ten_xe' => $row['ten_xe'],
                    'mau_xe' => $row['mau_xe'],
                    'user_id' => $row['user_id'],
                    'bien_so_xe' => $row['bien_so_xe'],
                    'so_khung' => $row['so_khung'],
                    'so_cho' => $row['so_cho'],
                    'dac_diem_mac_dinh' => $row['dac_diem_mac_dinh'],
                    'so_dau_xang_tieu_thu' => $row['so_dau_xang_tieu_thu'],
                    'ngay_bao_duong_gan_nhat' => $row['ngay_bao_duong_gan_nhat'],
                    'han_dang_kiem_tiep_theo' => $row['han_dang_kiem_tiep_theo'],
                    'anh_xe' => $row['anh_xe'],
                ]);
            }
        }
    }
}
