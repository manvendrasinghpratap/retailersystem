<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid username or password.',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        if (!$user->is_active || !$user->status) {
            Auth::guard('api')->logout();

            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        return response()->json([
            'status' => true,
            'user' => Auth::guard('api')->user(),
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        return response()->json([
            'status' => true,
            'access_token' => Auth::guard('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}