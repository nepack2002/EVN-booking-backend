<?php

namespace App\Imports;

use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DepartmentsImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $validator = Validator::make($row->toArray(), [
                'ten_phong_ban' => 'required|string|max:255',
                'phong_ban_cap_tren' => 'nullable|exists:departments,name',
            ]);

            if ($validator->fails()) {
                continue;
            }
            if (!empty($row['phong_ban_cap_tren'])) {
                $department = Department::where('name','=',$row['phong_ban_cap_tren'])->first();
                Department::create([
                    'name' => $row['ten_phong_ban'],
                    'parent_id' => $department->id,
                ]);
            } else {
                Department::create([
                    'name' => $row['ten_phong_ban'],
                ]);
            }
        }
    }
}
