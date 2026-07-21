<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeoutController extends Controller
{
    /**
     * Refresh session when user clicks
     * "Stay Logged In"
     */
    public function keepAlive(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Touch the session to refresh the lifetime
        $request->session()->put('last_activity', now()->timestamp);

        // Save session immediately
        $request->session()->save();

        return response()->json([
            'success' => true,
            'message' => 'Session refreshed.',
            'expires_in' => config('session.lifetime') * 60,
            'server_time' => now()->timestamp,
        ]);
    }

    /**
     * Logout current session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'redirect' => route('login')
        ]);
    }
}