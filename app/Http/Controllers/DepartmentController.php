<?php

namespace App\Http\Controllers;

use App\Imports\DepartmentsImport;
use App\Models\Department;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentController extends Controller
{
    //lấy full
    public function index(Request $request)
    {
        $query = $request->query('query');
        if (!empty($query)) {
            $departments = Department::with('children')->where('name', 'like', '%' . $query . '%')->paginate(5);
        } else {
            $departments = Department::with('children')->paginate(20);
        }
        return response()->json($departments);
    }

    public function index2()
    {
        $departments = Department::all()->toArray();

        $hierarchy = $this->buildHierarchy($departments);

        return response()->json($hierarchy);
    }

    private function buildHierarchy(array $departments, $parentId = null, $level = 0)
    {
        $result = [];
        foreach ($departments as $department) {
            if ($department['parent_id'] == $parentId) {
                $department['level'] = $level;
                $department['full_name'] = str_repeat('-', $level) . ' ' . $department['name'];
                $result[] = $department;
                $children = $this->buildHierarchy($departments, $department['id'], $level + 1);
                $result = array_merge($result, $children);
            }
        }
        return $result;
    }

    //thêm
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:departments,id'
        ]);

        Department::create($request->all());

        return response()->json("Thành công");
    }

    //hiện chi tiết
    public function show(Department $department)
    {
        return response()->json($department);
    }

    //cập nhật
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'sometimes|nullable|exists:departments,id'
        ]);


        $department->update($request->all());

        return response()->json("Thành công");
    }

    //xóa
    public function destroy($id)
    {
        try {
            // Đặt giá trị department_id trong bảng schedules thành NULL
            Schedule::where('department_id', $id)->delete();

            // Xóa phòng ban
            Department::destroy($id);

            return response()->json(['message' => 'Department deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        Excel::import(new DepartmentsImport, $request->file('file'));

        return response()->json(['success' => true]);
    }
}
