<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Config;
class UserService
{
    public function createStaff($request, $filename)
    {
        return User::create([
            'account_id' => Auth::user()->account_id,
            'name' => $request->suffix.' '.ucwords($request->first_name.' '.$request->last_name),
            'user_type_id' => \Config::get('constants.staff'),
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'avatar' => $filename,
            'is_staff' => \Config::get('constants.is_staff'),
            'is_active' => $request->staffstatus,
            'status' => $request->staffstatus,
            'designation_id' => $request->designation_id,
            'created_by' => Auth::id(),
        ]);
    }

    public function createAdmin($request)
    {
        return User::create([
            'name' => $request->suffix . ' ' . ucwords($request->first_name) . ' ' . ucwords($request->last_name),
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'user_type_id' => config('constants.admin'),
            'is_active' => $request->is_active,
            'created_by' => Auth::id(),
        ]);
    }

    public function updateStaffBasic($user, $request)
    {
        $user->name = $request->staff_suffix . ' ' . ucwords($request->first_name . ' ' . $request->last_name);
        $user->email = $request->email;
        $user->designation_id = $request->designation_id;
        $user->is_active = $request->staffstatus;

        return $user->update();
    }

}