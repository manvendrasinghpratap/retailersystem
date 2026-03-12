<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use App\Models\LocalGovernment;
use App\Models\Countries;
use App\Models\State;   
use Illuminate\Support\Facades\Config;
use App\Helpers\Settings;
use App\Models\UserDetail;

class ProfileController extends Controller
{
    protected $breadcrumbProfileListing;
    protected $breadcrumbProfile;
    public function __construct()
    {
        // Ensure only authenticated users can access this controller
        $this->middleware('auth');
         // Breadcrumb structure used in the profile view
        $this->breadcrumbProfile = [
            'title' => __('translation.profile'),
            'route1' => 'dashboard',
            'route1Title' => 'Dashboard',
            'route2' => 'profile',
            'route2Title' => 'Profile',
            'reset_route' => 'administrator.dashboard',
            'reset_route_title' => __('translation.cancel')
        ];
    }
    /**
     * Display the user's profile form.
     */
   public function editprofile( Request $request )
    {
        if(Auth::user()->user_type_id == 1)
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbProfile,['route1','route2','reset_route'],['administrator.dashboard','update.profile','administrator.dashboard']);
        $user = User::find(Auth::user()->id);
        $suffix                     = \Config::get( 'constants.suffix' );
        $localGovernment            = LocalGovernment::pluck('name','name')->toArray();
        $countries                  = Countries::pluck('country_name','country_name')->toArray();
        $state                      = State::pluck('name','name')->toArray();
        return view('backend.profile', compact('user','breadcrumb','localGovernment','countries','state','suffix'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


public function updateProfile(ProfileUpdateRequest $request)
{
    try {

        $user = Auth::user();
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Upload Avatar
        |--------------------------------------------------------------------------
        */

        $filename = $user->avatar;

        if ($request->hasFile('avatar')) {
            $uploaded = Settings::uploadimage($request, 'avatar', 'staff', $user->avatar);
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
            $request->only([
                'first_name',
                'last_name',
                'street_address',
                'office_phone',
                'cell_phone',
                'whatsapp_number',
                'local_government',
                'country',
                'state',
                'date_of_birth',
                'nin',
                'suffix'
            ])
        );

        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        $fullName = $request->suffix . ' ' .
                    ucwords($request->first_name) . ' ' .
                    ucwords($request->last_name);

        $user->update([
            'name'   => $fullName,
            'avatar' => $filename
        ]);

        return Settings::roleRedirect('profile', 'User Details Updated Successfully.');

    } catch (\Exception $e) {

        return Settings::roleRedirect('profile', 'Something went wrong!', 'error');
    }
}

public function updateProfileold(ProfileUpdateRequest $request)
    {
        
        try {
            $user = Auth::user();
            $request->validated();
            $filename = '';
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $uploadedfilename = Settings::uploadimage($request, 'avatar', 'staff', $user->avatar);
                $filename = !empty($uploadedfilename) ? $uploadedfilename : '';
            }
            
            // Save user details
            $userDetail = UserDetail::firstOrNew(['user_id' => $user->id]);
            
            $userDetail->first_name         = $request->first_name;            
            $userDetail->last_name          = $request->last_name;
            $userDetail->street_address     = $request->street_address;
            $userDetail->office_phone       = $request->office_phone;
            $userDetail->cell_phone          = $request->cell_phone;
            $userDetail->whatsapp_number    = $request->whatsapp_number;
            $userDetail->local_government   = $request->local_government;
            $userDetail->country_of_origin  = $request->country;
            $userDetail->state_of_origin    = $request->state;
            $userDetail->date_of_birth      = Settings::formatDate($request->date_of_birth, config('constants.datepicker'));
            $userDetail->nin                = $request->nin;
            $userDetail->staff_suffix       = $request->suffix;
            $userDetail->save();
            
            // Update user
            $user->name = $request->suffix . ' ' . ucwords($request->first_name) . ' ' . ucwords($request->last_name);
            if (!empty($filename)) {
                $user->avatar = $filename;
            }
            $user->save();
            // Redirect based on user type
            return Settings::roleRedirect('profile','User Details Updated Successfully.');

        } catch (\Exception $e) {
           return Settings::roleRedirect('profile','Something went wrong!','error');

        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
