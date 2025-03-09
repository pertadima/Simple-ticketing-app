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

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'errors' => [
                    'error_code' => 401,
                    'title' => 'Unauthorized',
                    'message' => 'Invalid credentials'
                ]
            ], 401);
        }

        $user = Users::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'access_token' => 'Bearer ' . $token,
        ]);
    }
    
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'This email is already registered.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => [
                    'error_code' => 422,
                    'title' => 'Error',
                    'message' => 'Failed to register'
                ]
            ], 422);
        }

        $user = Users::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'remember_token' => Str::random(10)
        ]);

        return response()->json([
            'user' => new UsersResource($user),
            'access_token' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken,
            'message' => 'Registration successful'
        ], 201);
    }
}
