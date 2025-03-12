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
                errors: $validator->errors()->toArray()
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
}
