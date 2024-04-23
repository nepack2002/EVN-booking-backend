<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function login(Request $request){
    // Xác thực yêu cầu
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'messages' => 'User not found',
        ], 404);
    }

    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'messages' => 'Incorrect password',
        ], 404);
    }    Auth::login($user);
    // Tạo token
    $token = $user->createToken('auth_token')->plainTextToken;
    
    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
}

    public function register(Request $request){
        $message = [
            'email.email' => "Error Email", 
        ];
        $validator = Validator::make($request->all(), [
            'email' =>'required|email',
            'password' =>'required|min:5',
        ], $message);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors(),
                ]
            );
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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