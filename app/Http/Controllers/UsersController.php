<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsersController extends Controller
{
    public function getUser(Request $request)
    {
        $query = $request->query('query');

        if (!empty($query)) {
            $users = User::with(['car', 'department'])
                ->where('name', 'like', '%' . $query . '%')
                ->paginate(10);
        } else {
            $users = User::with(['car', 'department'])->paginate(10);
        }

        return response()->json($users);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required',
            'password' => 'required',
            'department_id' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'password' => 'sometimes|required',
            'department_id' => 'required'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
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