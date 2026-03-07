<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CommonHelper
{
    /**
     * Get filtered staff list based on user role and designation.
     *
     * @return array
     */
    public static function getStaffList()
    {
        $staffList = User::where('is_deleted', 0)
            ->where('is_staff', 2)
            ->where('user_type', '>', 1);

        if (!in_array(Auth::user()->designation_id, Config::get('constants.accessonlysuper_front_admin'))) {
            $staffList = $staffList->where('id', Auth::user()->id);
        }

        return $staffList->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }
}
