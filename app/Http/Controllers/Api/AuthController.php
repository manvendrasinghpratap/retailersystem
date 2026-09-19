<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $credentials['username'];
        // Determine whether the user entered an email or username.
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $authCredentials = [$field => $login, 'password' => $credentials['password'],];
        // Attempt JWT authentication.
        if (!$token = Auth::guard('api')->attempt($authCredentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Username/Email or Password.',
            ], 401);
        }
        $user = Auth::guard('api')->user();
        /*
        |--------------------------------------------------------------------------
        | Account Status
        |--------------------------------------------------------------------------
        |
        | Keep the same business rules as web login.
        |
        */
        // Super Admin Bypass.
        if ($user->id != 1) {
            // Check main account status.
            if (!$user->account || $user->account->status != 1) {
                Auth::guard('api')->logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Your main account has been deactivated. Please contact support.',
                ], 403);
            }
            // Check subscription status.
            if (!$user->account->hasActiveSubscription()) {
                Auth::guard('api')->logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Your subscription has expired. Please renew your subscription.',
                ], 403);
            }
        }
        /*
        |--------------------------------------------------------------------------
        | User Status
        |--------------------------------------------------------------------------
        */
        if (!$user->is_active || !$user->status) {
            Auth::guard('api')->logout();
            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */
        $user->idle_timout = 10 * 60; //10 minutes
        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                'user' => $user,
                'idle_timout' => 10 * 60,
            ],
        ], 200);
    }


    public function apiRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required','string','lowercase','email','max:255','unique:' . User::class],
            'password' => ['required','confirmed', Rules\Password::defaults()],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Create User
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'name' => $validated['name'],
                'username' => strtolower($validated['email']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'account_id' => 1,
                'user_type_id' => 4,
                'designation_id' => 4,
                'is_active' => 1,
                'is_staff' => 1,
                'status' => 1,
                'created_by' => 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Fire Registered Event
            |--------------------------------------------------------------------------
            */

            event(new Registered($user));

            /*
            |--------------------------------------------------------------------------
            | Generate JWT
            |--------------------------------------------------------------------------
            */

            $token = Auth::guard('api')->login($user);

            /*
            |--------------------------------------------------------------------------
            | Return Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully.',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                    'user' => $user,
                ],
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to register user.',
            ], 500);
        }
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