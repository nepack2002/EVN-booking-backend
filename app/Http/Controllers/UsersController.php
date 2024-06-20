<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class UsersController extends Controller
{
    public function getUser(Request $request)
    {
        $query = $request->query('query');

        if (!empty($query)) {
            $users = User::with(['car', 'department'])
                ->where('name', 'like', '%' . $query . '%')
                ->paginate(5);
        } else {
            $users = User::with(['car', 'department'])->where('role' , 'user')->get();
        }

        return response()->json($users);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'role' => 'required|string',
            'password' => 'required|string|min:3',
            'department_id' => 'required|integer',
        ];

        if ($request->role === 'user') {
            $rules['phone'] = 'required|string|max:10|unique:users,phone';
        }

        // Thực hiện validation
        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->role,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
        ]);

        return response()->json($user);
    }

    public function get($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role' => 'required|string',
            'password' => 'sometimes|required',
            'department_id' => 'required'
        ];

        // Nếu role là user, thêm quy tắc bắt buộc cho trường phone
        if ($request->role === 'user') {
            $rules['phone'] = 'required|string|max:10';
        }

        // Thực hiện validation
        $request->validate($rules);


        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->phone = $request->phone ?? null;
        $user->department_id = $request->department_id;
        $user->save();

        return response()->json(['success' => true, 'user_id' => $user->id]);
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['success' => true]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
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

        Excel::import(new UsersImport, $request->file('file'));

        return response()->json(['success' => true]);
    }
}
