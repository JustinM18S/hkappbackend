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
use App\Models\StudentAssignment;
use App\Models\StudentTask;


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

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:student,faculty', // Only 'student' or 'faculty'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'email_verified_at' => now(), // Automatically verify the user
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'user_type' => 'required|in:student,faculty,admin',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            'user_type' => $validated['user_type'],
        ]);

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function getStudents()
    {
        $students = User::where('user_type', 'student')
                        ->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Student list fetched successfully',
            'students' => $students,
        ], 200);
    }

    public function getFaculties()
    {
        $faculties = User::where('user_type', 'faculty')
                        ->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Faculty list fetched successfully',
            'faculties' => $faculties,
        ], 200);
    }

    public function getStudentHours($student_id)
    {
        $student = User::findOrFail($student_id);

        $assignment = StudentAssignment::where('student_id', $student_id)->first();

        if (!$assignment) {
            return response()->json(['message' => 'No assignment found for this student.'], 404);
        }

        $hkHours = [
            'HK75' => 120,
            'HK50' => 90,
            'HK25' => 45,
        ];

        $totalHours = $hkHours[$assignment->hk_type] ?? 0;

        $completedTasks = StudentTask::where('student_id', $student_id)
                                    ->where('status', 'completed')
                                    ->get();

        $completedHours = $completedTasks->sum(function ($task) {
            return $this->calculateTaskDuration($task->duty_start, $task->duty_end);
        });

        $remainingHours = max(0, $totalHours - $completedHours);

        return response()->json([
            'student_name' => $student->name,
            'total_hours' => $totalHours,
            'completed_hours' => $completedHours,
            'remaining_hours' => $remainingHours,
            'completed_tasks' => $completedTasks,
        ], 200);
    }

    private function calculateTaskDuration($start, $end)
    {
        $startTime = \Carbon\Carbon::parse($start);
        $endTime = \Carbon\Carbon::parse($end);

        $totalMinutes = $endTime->diffInMinutes($startTime);

        $hours = intdiv($totalMinutes, 60); 
        $minutes = $totalMinutes % 60; 

        return $hours + ($minutes / 60);
    }

}
