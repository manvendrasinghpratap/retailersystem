<?php

namespace App\Http\Controllers\Admin;

use PDF;
use App\Models\User;
use App\Models\State;
use App\Models\Countries;
use App\Helpers\Settings;
use App\Models\StaffDetail;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Http\Redirect;
use App\Models\LocalGovernment;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Image;
class StaffController extends Controller
{
     protected $breadcrumbAddNew = ['title' => 'Staff Listing', 'route' => "staff", 'routeTitle' => "Staff Record ", 'add_new_route_title' => 'Add New Staff', 'add_new_route' => 'staff.add'];
    protected $breadcrumbListing = ['title' => 'Staff Listing', 'route' => "staff", 'routeTitle' => "Staff Records ", 'add_new_route_title' => 'Staff Listing', 'add_new_route' => 'staff'];
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {  
        $breadcrumb = $this->breadcrumbAddNew;
        $designation = Designation::where('id', '!=', 1)->where('id', '!=', 3)->pluck('name', 'id')->toArray();
        $updatedAt = date('Y-m-d');
        $updatedAt = '';
        $staffstatus            = \Config::get( 'constants.staffstatus' );
        $userList = User::select('*')->where('is_deleted', '0')->where('is_staff', 2)->where('designation_id', '>', 1);
        if (!empty(request()->get('staff_name'))) {
            $userList = $userList->where('name', 'LIKE', '%' . trim(request()->get('staff_name')) . '%');
        }
        if (!empty(request()->get('designation_id'))) {
            $userList = $userList->where('designation_id', request()->get('designation_id'));
        }
        if (request()->get('is_active') == '0') {
            $userList = $userList->where('is_active', request()->get('is_active'));
        }
        if (!empty(request()->get('is_active'))) {
            $userList = $userList->where('is_active', request()->get('is_active'));
        }
        if (!empty(request()->get('hire_date'))) {
            $updatedAt = request()->get('hire_date');
            $userList = $userList->whereDate('hire_date', request()->get('hire_date'));
        }
        $userList = $userList->orderBy('id', 'desc')
            ->paginate(\Config::get( 'constants.pagination' ));
        return view('backend.staff.index', compact("userList", "designation", "updatedAt",'staffstatus','breadcrumb'));
    }

    public function statusUpdate(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = $request->input('status');
            $Menu = User::where("id", $id)->update(["is_active" => $status]);
            Session::flash('success', 'Data update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }


    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            $is_deleted = $request->input('is_deleted');
            $Menu = User::where("id", $id)->update(["is_deleted" => $is_deleted]);
            Session::flash('success', 'Data update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function create(Request $request)
    {
        $breadcrumb = $this->breadcrumbListing;
        $route = 'add';
        $isimagechanged = 1;
        $suffix                 = \Config::get( 'constants.suffix' );
        $emergecyRelationship   = \Config::get( 'constants.emergecyRelationship' );
        $staffstatus            = \Config::get( 'constants.staffstatus' );
        $state                  = State::select('name')->pluck('name','name')->toArray();
        $country                = Countries::select('country_name')->pluck('country_name','country_name')->toArray();
        
        $localGovernment  = LocalGovernment::select('name')->pluck('name','name')->toArray();
        $designation = Designation::where('id', '!=', 1)->where('id', '!=', 3)->get();
        return view('backend.staff.form', compact(["designation", "route",'suffix','state','localGovernment','emergecyRelationship','staffstatus','isimagechanged','country','breadcrumb']));
    }

    public function store(Request $request)
    {
        
        $validator = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'street_address' => 'required',
            'local_gov_id' => 'required',
            'country_of_origin' => 'required',
            'state_of_origin' => 'required',
            'date_of_birth' => 'required',
            'nin' => 'required',
            'emergency_contact_name' => 'required',
            'emergency_phone' => 'required',
            'emergency_relationship' => 'required',
            'staff_suffix' => 'required',
            'guarantor_name' => 'required',
            'guarantor_address' => 'required',
            'guarantor_phone' => 'required',
            'designation_id' => 'required',
            'hire_date' => 'required',
            'email' => 'required|unique:users|max:255',
            'username' => 'required|unique:users|max:100',
            'mobile_no' => 'required|numeric|min:10',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png', // max size in kilobytes (e.g., 2048 KB = 2 MB)
        ]);
       try {
            $filename = 'default.png';
            if ($request->hasFile('image')) {
                $uploadedfilename = Settings::uploadimagenew($request,'image','staff');
                $filename = !empty($uploadedfilename)?$uploadedfilename:$filename;
            }
            $userType = ($request->input('designation_id') == 2) ? $request->input('designation_id') : 3;
            if($request->input('designation_id') == 12){
                $userType = 2;
            }
            $user = User::create([
                'name' => ucwords($request->input('first_name').' '. $request->input('last_name')),
                'first_name' => ucwords($request->input('first_name')),
                'last_name' => ucwords($request->input('last_name')),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'mobile_no' => $request->input('mobile_no'),
                'hire_date' => Settings::formatDate($request->input('hire_date'),'Y-m-d'),
                'designation_id' => $request->input('designation_id'),
                'user_type' => $userType,
                'is_active' => $request->input('staffstatus'),
                'is_staff' => 2,
                'avatar' => $filename,
                'created_by' => Auth::user()->id,
                'password' => Hash::make($request->input('password')),
            ]);
            $lastInsertedId = $user->id;
            $staffDetail = new StaffDetail();
            $staffDetail->street_address = $request->input('street_address');
            $staffDetail->local_gov_id = $request->input('local_gov_id');
            $staffDetail->country_of_origin = $request->input('country_of_origin');
            $staffDetail->state_of_origin = $request->input('state_of_origin');
            $staffDetail->date_of_birth = Settings::formatDate($request->input('date_of_birth'),'Y-m-d');
            $staffDetail->nin = $request->input('nin');
            $staffDetail->emergency_contact_name = $request->input('emergency_contact_name');
            $staffDetail->emergency_phone = $request->input('emergency_phone');
            $staffDetail->emergency_relationship = $request->input('emergency_relationship');
            $staffDetail->staff_suffix = $request->input('staff_suffix');
            $staffDetail->guarantor_name = $request->input('guarantor_name');
            $staffDetail->guarantor_address = $request->input('guarantor_address');
            $staffDetail->guarantor_phone = $request->input('guarantor_phone');
            $staffDetail->note = $request->input('note');
            $staffDetail->staff_id = $lastInsertedId;
            $staffDetail->save();
           return redirect()->route('staff')->with('success', 'Staff added successfully!');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function downloadstaffpdf(Request $request)
    {
        $updatedAt = '';
        $userList = User::select('*')->where('is_deleted', '0')->where('is_staff', 2)->where('designation_id', '>', 1);
        if (!empty(request()->get('staff_name'))) {
            $userList = $userList->where('name', 'LIKE', '%' . trim(request()->get('staff_name')) . '%');
        }
        if (!empty(request()->get('designation_id'))) {
            $userList = $userList->where('designation_id', request()->get('designation_id'));
        }
        if (request()->get('is_active') == '0') {
            $userList = $userList->where('is_active', request()->get('is_active'));
        }
        if (!empty(request()->get('is_active'))) {
            $userList = $userList->where('is_active', request()->get('is_active'));
        }
        if (!empty(request()->get('hire_date'))) {
            $updatedAt = request()->get('hire_date');
            $userList = $userList->whereDate('hire_date', request()->get('hire_date'));
        }
        $userList = $userList->orderBy('id', 'desc')->get();
        $pdf = PDF::loadView('backend.staff.downloadstaffpdf', compact("userList", "updatedAt"))->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->stream('Staff-list.pdf');
    }

    public function editstaff(Request $request, $id)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $route = 'edit';
        $isimagechanged = 0;
        $id = base64_decode($id);
        $designation = Designation::where('id', '!=', 1)->where('id', '!=', 3)->get();
        $userDetails = User::find($id);
        $staffDetails = StaffDetail::where('staff_id',$id)->first();
        $suffix                 = \Config::get( 'constants.suffix' );
        $emergecyRelationship   = \Config::get( 'constants.emergecyRelationship' );
        $staffstatus            = \Config::get( 'constants.staffstatus' );
        $state                  = State::select('name')->pluck('name','name')->toArray();
        $localGovernment        = LocalGovernment::select('name')->pluck('name','name')->toArray();
        $country                = Countries::select('country_name')->pluck('country_name','country_name')->toArray();
        return view('backend.staff.form', compact(["designation", "userDetails", "route",'suffix','emergecyRelationship','staffstatus','state','localGovernment','staffDetails','isimagechanged','country','breadcrumb']));
    }

    public function update(Request $request)
    {
        $id = $request->staff_id;
        $newpassword = '';    
        $user = User::find($request->staff_id);       
        $validator = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'designation_id' => 'required',
            'hire_date' => 'required',
            'email' => 'required|email|unique:users,email,' . $id . 'id',
            'mobile_no' => 'required|numeric|min:10',
            'local_gov_id' => 'required',
            'country_of_origin' => 'required',
            'state_of_origin' => 'required',
            'date_of_birth' => 'required',
            'nin' => 'required',
            'emergency_contact_name' => 'required',
            'emergency_phone' => 'required',
            'emergency_relationship' => 'required',
            'staff_suffix' => 'required',
            'guarantor_name' => 'required',
            'guarantor_address' => 'required',
            'guarantor_phone' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // max size in kilobytes (e.g., 2048 KB = 2 MB)
        ]);
        try {
            $filename = $user->avatar;
            if ($request->hasFile('image')) {
                $uploadedfilename = Settings::uploadimage($request,'image','staff');
                $filename = !empty($uploadedfilename)?$uploadedfilename:$filename;
            }
            $userType = ($request->input('designation_id') == 2) ? $request->input('designation_id') : 3;
            $user->name = ucwords($request->input('first_name').' '. $request->input('last_name'));
            $user->first_name = ucwords($request->input('first_name'));
            $user->last_name = ucwords($request->input('last_name'));
            $user->avatar = $filename;            
            $user->email = $request->input('email');
            $user->mobile_no = $request->input('mobile_no');
            $user->hire_date = date('Y-m-d', strtotime($request->input('hire_date')));
            $user->designation_id = $request->input('designation_id');
            $user->user_type = $userType;
            $user->is_active = $request->input('staffstatus');
            if(!empty($newpassword)){$user->password = $newpassword; $user->is_password_changed = 1;}
            $user->update();
            StaffDetail::where('staff_id',$id)->delete();
            $staffDetail = new StaffDetail();
            $staffDetail->street_address = $request->input('street_address');
            $staffDetail->local_gov_id = $request->input('local_gov_id');
            $staffDetail->country_of_origin = $request->input('country_of_origin');
            $staffDetail->state_of_origin = $request->input('state_of_origin');
            $staffDetail->date_of_birth = Settings::formatDate($request->input('date_of_birth'),'Y-m-d');
            $staffDetail->nin = $request->input('nin');
            $staffDetail->emergency_contact_name = $request->input('emergency_contact_name');
            $staffDetail->emergency_phone = $request->input('emergency_phone');
            $staffDetail->emergency_relationship = $request->input('emergency_relationship');
            $staffDetail->staff_suffix = $request->input('staff_suffix');
            $staffDetail->guarantor_name = $request->input('guarantor_name');
            $staffDetail->guarantor_address = $request->input('guarantor_address');
            $staffDetail->guarantor_phone = $request->input('guarantor_phone');
            $staffDetail->note = $request->input('note');
            $staffDetail->facebook = $request->input('facebook');
            $staffDetail->twitter = $request->input('twitter');
            $staffDetail->linkedin = $request->input('linkedin');
            $staffDetail->instagram = $request->input('instagram');
            $staffDetail->pinterest = $request->input('pinterest');
            
            $staffDetail->staff_id = $id;
            $staffDetail->save();
            return redirect()->route('staff')->with('success', 'Staff Updated Successfully.!');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function updatepassword(Request $request){
        $user           = User::find($request->staff_id);
        $user->password = Hash::make($request->input('password'));
        $user->update();
        return;
    }
}
