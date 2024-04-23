<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    //lấy full
    public function index(Request $request){
        $query = $request->query('query');
        if (!empty($query)) {
            $departments = Department::with('children')->where('name', 'like', '%' . $query . '%')->paginate(5);
        }else{
            $departments = Department::with('children')->paginate(5);
        }
            return response()->json($departments);
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
    public function show(Department $department){
        return response()->json($department);
    } 
    //cập nhật
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:departments,id'
        ]);

                $department->update($request->all());
        
        return response()->json("Thành công");
    }
    //xóa
     public function destroy(Department $department)
    {
        $department->delete();

        return response()->json("Thành công");
    }
    
}