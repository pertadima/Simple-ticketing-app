<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UsersResource;
use Illuminate\Support\Str;
use App\Helpers\ApiErrorHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordOtpMail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $apiErrorHelper = new ApiErrorHelper();
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json($apiErrorHelper->formatError(
                title: 'Unauthenticated',
                status: 401,
                detail: 'Invalid email or password'
            ), 401);
        }

        $user = Users::where('email', $request->email)->firstOrFail();

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UsersResource($user),
                'message' => 'Login success',
                'access_token' => 'Bearer ' . $token,
            ]
        ]);
    }
    
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'data' => [
                'message' => 'Successfully logged out'
            ],
        ]);
    }

    public function register(Request $request)
    {
        $apiErrorHelper = new ApiErrorHelper();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'email.unique' => 'This email is already registered.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json($apiErrorHelper->formatError(
                title: 'Failed Validation',
                status: 422,
                detail: 'Cannot process the request due to validation errors',
                errors: array_merge(...array_values($validator->errors()->toArray()))
            ), 422);
        }

        $user = Users::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'remember_token' => Str::random(10)
        ]);

        $user->tokens()->delete();
        return response()->json([
            'data' => [
                'user' => new UsersResource($user),
                'access_token' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken,
                'message' => 'Registration successful'
            ]
        ], 201);
     }

    public function resetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $otp = rand(100000, 999999);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        Mail::to($request->email)->send(new ResetPasswordOtpMail($otp));

        return response()->json([
            'data' => [
                'message' => 'OTP sent to your email'
            ],
        ]);
    }

    public function validateOtp(Request $request)
    {
        $apiErrorHelper = new ApiErrorHelper();
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required'
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->first();

        if (!$record) {
            return response()->json($apiErrorHelper->formatError(
                title: 'Failed Validation',
                status: 422,
                detail: 'Invalid or expired OTP'
            ), 422);
        }

        return response()->json([
            'data' => [
                'message' => 'Otp is valid. Continue to change your password.',
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $apiErrorHelper = new ApiErrorHelper();
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'password' => 'required|string|min:8'
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->first();

        if (!$record) {
             return response()->json($apiErrorHelper->formatError(
                title: 'Failed Validation',
                status: 422,
                detail: 'Invalid or expired OTP'
            ), 422);
        }

        $user = Users::where('email', $request->email)->first();
        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Optionally, delete the reset record
        DB::table('password_resets')->where('email', $request->email)->delete();

       return response()->json([
            'data' => [
                'message' => 'Password changed successfully. Please log in with your new password.'
            ],
        ]);
    }
}
