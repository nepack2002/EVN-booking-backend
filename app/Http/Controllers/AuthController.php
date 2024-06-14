<?php

namespace App\Http\Controllers;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
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
        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], config('sanctum.expiration'));
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], config('sanctum.expiration'));

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'refresh_token' => $refreshToken->plainTextToken,
        ]);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:5',
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

    public function user(Request $request)
    {
        return $request->user();
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function refresh(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], config('sanctum.expiration'));

        return ['access_token' => $accessToken->plainTextToken];
    }
}
