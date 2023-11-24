<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWT;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function me()
    {
        if (Auth::user())
            return response()->json(['status' => 'success']);
        else
            return response()->json(['status' => 'not exist']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wrong password',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'body' => [
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]
        ]);

    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            // 'role' => 'required|string|exists:roles,role'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $role_id = Role::where('role', 'pupil')->first()->id;

        $user = User::create([
            'name' => $request->login,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role_id
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'body' => [
                'message' => 'User created successfully',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
