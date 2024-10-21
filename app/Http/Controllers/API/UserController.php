<?php


namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\SendVerificationCode;

class UserController extends Controller
{
   
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->email_verified_at) {
  
            $verificationCode = Str::random(4);
            $user->verification_code = $verificationCode;
            $user->save();

         
            $user->notify(new SendVerificationCode($verificationCode));

            return response()->json([
                'message' => 'Verification code sent to your email.',
            ], 200);
        }


        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ], 200);
    }


    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)
                    ->where('verification_code', $request->code)
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid code or email'], 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully. Please log in.',
        ], 200);
    }

    public function user(Request $request)
    {
        return response()->json([
            'message' => 'User Successfully Fetched',
            'data' => $request->user()
        ], 200);
    }

  
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User Successfully Logged Out'
        ], 200);
    }

 
    public function logoutFromAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'User Logged Out from All Devices'
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $token = Str::random(64);

        // Delete any existing token for the email before inserting a new one
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        Mail::send('emails.reset-password', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Password Reset Link');
        });

        return response()->json([
            'message' => 'Password reset link sent to your email.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $passwordReset = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        $user = User::where('email', $passwordReset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $passwordReset->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }
}
