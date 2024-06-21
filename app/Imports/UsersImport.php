<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $user = User::where('username', $row['ten_dang_nhap'])->first();
            $department = Department::where('name','=',$row['ten_phong_ban'])->first();
            if ($user) {
                $user->update([
                    'name' => $row['ho_ten'],
                    'role' => $row['vai_tro'],
                    'phone' => $row['so_dien_thoai'],
                    'department_id' => $department->id??'',
                    'password' => Hash::make($row['mat_khau']),
                ]);
            } else {
                User::create([
                    'name' => $row['ho_ten'],
                    'username' => $row['ten_dang_nhap'],
                    'role' => $row['vai_tro'],
                    'phone' => $row['so_dien_thoai'],
                    'department_id' => $department->id??'',
                    'password' => Hash::make($row['mat_khau']),
                ]);
            }
        }
    }
}
