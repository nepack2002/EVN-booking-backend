<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        Mail::send('forgot', [ 'newPassword' => $newPassword], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });
        // DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();
        // Trả về phản hồi thành công hoặc thông báo thành công
        return response()->json([
            'message' => 'A reset link has been sent to your email address.'
        ], 200);
    }
    public function viewResetPassword()
    {
        return view('welcome');
    }
    
    public function resetPassword( $token)
    {
//     $request->validate([
//         'password' => 'required|confirmed',
//     ]);

        dd($token);
//         // Đặt lại mật khẩu mới cho người dùng

//         // Xóa token sau khi đã đặt lại mật khẩu
//         DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

//         // Trả về phản hồi thành công
//         return response()->json([
//             'message' => 'Password has been reset successfully.'
//         ], 200);
 }
}