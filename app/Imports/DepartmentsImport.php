<?php

namespace App\Imports;

use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class DepartmentsImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $validator = Validator::make($row->toArray(), [
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:departments,id',
            ]);

            if ($validator->fails()) {
                continue;
            }
            Department::create([
                'name' => $row['name'],
                'parent_id' => $row['parent_id'],
            ]);
        }
    }
}