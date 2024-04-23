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
    /**
     * Get list of users with their cars.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
{
    // Lấy từ khóa tìm kiếm từ tham số truy vấn 'query'
    $query = $request->query('query');

    // Nếu người dùng đã cung cấp từ khóa tìm kiếm
    if (!empty($query)) {
        // Tìm kiếm người dùng bằng cách kiểm tra tên hoặc email chứa từ khóa tìm kiếm
        $users = User::where('name', 'like', '%' . $query . '%')
            // ->orWhere('email', 'like', '%' . $query . '%')
            ->paginate(10); // Phân trang kết quả tìm kiếm
    } else {
        // Nếu không có từ khóa tìm kiếm, trả về tất cả người dùng
        $users = User::paginate(10); // Phân trang danh sách người dùng
    }

    // Trả về danh sách người dùng dưới dạng JSON
    return response()->json($users);
}
    /**
     * Create a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    
    /**
     * Get a single user details
     *
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    /**
     * Update a user's information
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'password' => 'sometimes|required',
        ]);

        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->save();

        return response()->json(['success' => true, 'user_id' => $user->id]);
    }

    /**
     * Delete a user
     *
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Import users from an Excel file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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