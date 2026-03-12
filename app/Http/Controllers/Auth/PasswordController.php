<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Breadcrumb configuration for the Change Password page
     */
    protected $breadcrumbChangePassword;

    /**
     * Constructor
     * 
     * Applies authentication middleware and prepares breadcrumb data
     */
    public function __construct()
    {
        // Ensure only authenticated users can access this controller
        $this->middleware('auth');

        // Breadcrumb structure used in the change password view
        $this->breadcrumbChangePassword = [
            'title' => __('translation.change_password'),
            'route1' => (auth()->check()) ? auth()->user()->user_type_id === 1 ? 'administrator.dashboard' : 'admin.dashboard' : 'dashboard',
            'route1Title' => 'Dashboard',
            'route2' => 'update-password',
            'route2Title' => 'Dashboard',
            'reset_route' => 'administrator.dashboard',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Update the authenticated user's password
     *
     * Steps:
     * 1. Validate request fields
     * 2. Check if the current password is correct
     * 3. Prevent using the same password again
     * 4. Hash and update the new password
     * 5. Return success/error messages
     */
    public function update(Request $request): RedirectResponse
    {
        // Validate incoming request data
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Old password is required.',
            'password.required' => 'New password is required.',
            'password.confirmed' => __('translation.new_password_confirm'),
        ]);

        /**
         * Check if the provided current password matches
         * the authenticated user's stored password
         */
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return back()->with('error', 'Your old password is incorrect.');
        }

        /**
         * Prevent users from reusing the same password
         */
        if (Hash::check($request->password, $request->user()->password)) {
            return back()->with('warning', 'New password cannot be the same as the old password.');
        }

        /**
         * Update the user's password after hashing it
         */
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        /**
         * Redirect back with success message
         */
        return redirect()->route('administrator.dashboard')->with('success', 'Password updated successfully.');
    }

    /**
     * Show the Change Password page
     *
     * Loads the password change view with breadcrumb data
     */
    public function editPassword()
    {
        $breadcrumb = $this->breadcrumbChangePassword;

        return view('backend.password', compact('breadcrumb'));
    }
}