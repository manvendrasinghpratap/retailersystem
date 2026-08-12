<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

        // Super Admin Bypass
        if ($user->id != 1) {

            // Check account status
            if (!$user->account || $user->account->status != 1) {

                auth()->logout();
                return back()->with('error', 'Your main account has been deactivated. Please contact support.');
            }

            // Check subscription status
            if (!$user->account->hasActiveSubscription()) {

                auth()->logout();

                return back()->with('error', 'Your subscription has expired. Please renew your subscription.');
            }
        }

        $request->session()->regenerate();
        session(['is_admin' => (auth()->user()->designation && auth()->user()->designation->name === 'Admin') ? 1 : 0]);
        session(['is_cashier' => (auth()->user()->designation && auth()->user()->designation->name === 'Cashier') ? 1 : 0]);

        return redirect($this->redirectByRole($user));
    }



    public function modellogin(Request $request)
    {
        $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $login)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid username/email or password.',
            ], 422);
        }

        // Account status check
        if ($user->id != 1 && (!$user->account || $user->account->status != 1)) {
            return response()->json([
                'status' => false,
                'message' => 'Your subscription account has been deactivated. Please contact support.',
            ], 422);
        }

        // Subscription check
        // if (!$user->account->hasActiveSubscription()) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Your subscription has expired. Please renew your subscription.',
        //     ], 422);
        // }

        $credentials = [
            $field => $login,
            'password' => $request->password
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid username/email or password.',
            ], 422);
        }


        $request->session()->regenerate();
        session(['is_admin' => (auth()->user()->designation && auth()->user()->designation->name === 'Admin') ? 1 : 0]);
        session(['is_cashier' => (auth()->user()->designation && auth()->user()->designation->name === 'Cashier') ? 1 : 0]);

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'redirect' => $this->redirectByRole(Auth::user()),
        ]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectByRole($user)
    {
        switch ($user->user_type_id) {

            case 1:
                return route('administrator.dashboard');

            case 2:
                return route('admin.dashboard');

            case 3:
                return route('dashboard');

            default:
                return route('home');
        }
    }
}
