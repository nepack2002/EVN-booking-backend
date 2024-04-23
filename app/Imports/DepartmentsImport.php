<?php

namespace App\Imports;

use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Kiểm tra tính hợp lệ của dữ liệu
            $validator = Validator::make($row->toArray(), [
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:departments,id',
            ]);

            if ($validator->fails()) {
                // Xử lý lỗi
                // Ví dụ: Log lỗi hoặc thông báo lỗi cho người dùng
                continue; // Bỏ qua dữ liệu không hợp lệ và tiếp tục với dữ liệu tiếp theo
            }

            // Tạo bản ghi mới
            Department::create([
                'name' => $row['name'],
                'parent_id' => $row['parent_id'],
            ]);
        }
    }
}