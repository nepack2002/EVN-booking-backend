<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $user = User::where('username', $row['username'])->first();
            if ($user) {
                $user->update([
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'phone' => $row['phone'],
                    'department_id' => $row['department_id'],
                    'password' => Hash::make($row['password']),
                ]);
            } else {
                User::create([
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                    'phone' => $row['phone'],
                    'department_id' => $row['department_id'],
                    'password' => Hash::make($row['password']), // Hash mật khẩu trước khi lưu
                ]);
            }
        }
    }
}