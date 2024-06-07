<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function login(Request $request){
    // Xác thực yêu cầu
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);
    $user = User::where('username', $request->username)->first();

    if (!$user) {
        return response()->json([
            'messages' => 'User not found',
        ], 404);
    }

    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'messages' => 'Incorrect password',
        ], 404);
    }   
    // Tạo token
    $token = $user->createToken('auth_token')->plainTextToken;
    
    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
}

    public function register(Request $request){
        
        $validator = Validator::make($request->all(), [
            'username' =>'required',
            'password' =>'required|min:5',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                ]
            );
        }
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? null,
        ]);
        return response()->json(
            [
               'message' => "Register Success",
            ]
        );
    }
    public function user(Request $request){
        return $request->user();
    }
    public function logout()
{
    auth()->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out successfully']);
}
}