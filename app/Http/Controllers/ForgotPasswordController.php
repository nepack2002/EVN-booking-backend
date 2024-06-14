<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        // $token = Str::random(64);
        // DB::table('password_reset_tokens')->insert([
        //     'email' => $request->email,
        //     'token' => $token,
        //     'created_at' => now()
        // ]);
        $newPassword = Str::random(5);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
        }
        Mail::send('forgot', ['newPassword' => $newPassword], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });
        return response()->json([
            'message' => 'A reset link has been sent to your email address.'
        ], 200);
    }

    public function get()
    {
        return view('cars.index');
    }

}
