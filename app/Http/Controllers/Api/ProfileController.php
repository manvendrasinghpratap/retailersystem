<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\LocalGovernment;
use App\Models\Countries;
use App\Models\State;
use App\Models\UserDetail;
use App\Helpers\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->load([
                'detail',
                'designation',
                'store',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Profile retrieved successfully.',
                'data' => [
                    'user' => $user,
                    'user_detail' => $user->detail,
                    'countries' => Countries::pluck('country_name', 'country_name'),
                    'states' => State::pluck('name', 'name'),
                    'local_governments' => LocalGovernment::pluck('name', 'name'),
                    'suffix' => config('constants.suffix'),
                ],
            ], 200);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve profile.',
            ], 500);
        }
    }

    /**
     * Update authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'suffix' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $request->user()->id,
            ],

            'phone' => ['nullable', 'string', 'max:30'],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // Add any other UserDetail fields used by your
            // ProfileUpdateRequest here.
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'local_government' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $user = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Avatar
            |--------------------------------------------------------------------------
            */

            $filename = $user->avatar;

            if ($request->hasFile('avatar')) {
                $uploaded = Settings::uploadimage(
                    $request,
                    'avatar',
                    'staff',
                    $user->avatar
                );

                if (!empty($uploaded)) {
                    $filename = $uploaded;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update User Detail
            |--------------------------------------------------------------------------
            */

            UserDetail::updateOrCreateDetail(
                $user->id,
                $request->all()
            );

            /*
            |--------------------------------------------------------------------------
            | Update User
            |--------------------------------------------------------------------------
            */

            $user->name =
                trim(
                    ($request->suffix ? $request->suffix . ' ' : '') .
                    ucwords($request->first_name) . ' ' .
                    ucwords($request->last_name)
                );

            $user->email = strtolower($request->email);

            if (!empty($filename)) {
                $user->avatar = $filename;
            }

            $user->save();

            /*
            |--------------------------------------------------------------------------
            | Reload relationships
            |--------------------------------------------------------------------------
            */

            $user->load([
                'detail',
                'designation',
                'store',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User details updated successfully.',
                'data' => [
                    'user' => $user,
                    'user_detail' => $user->detail,
                ],
            ], 200);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while updating your profile.',
            ], 500);
        }
    }

    /**
     * Update authenticated user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        try {
            $user = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Verify Current Password
            |--------------------------------------------------------------------------
            */

            if (!Hash::check(
                $validated['current_password'],
                $user->password
            )) {
                throw ValidationException::withMessages([
                    'current_password' => [
                        'The current password is incorrect.'
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update Password
            |--------------------------------------------------------------------------
            */

            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully.',
            ], 200);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to update password.',
            ], 500);
        }
    }

    /**
     * Delete authenticated user's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        try {
            $user = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Verify Password
            |--------------------------------------------------------------------------
            */

            if (!Hash::check(
                $validated['password'],
                $user->password
            )) {
                throw ValidationException::withMessages([
                    'password' => [
                        'The password is incorrect.'
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Logout JWT
            |--------------------------------------------------------------------------
            */

            Auth::guard('api')->logout();

            /*
            |--------------------------------------------------------------------------
            | Delete Account
            |--------------------------------------------------------------------------
            */

            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Account deleted successfully.',
            ], 200);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to delete account.',
            ], 500);
        }
    }
}