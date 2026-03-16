<?php

namespace App\Http\Controllers\Admin;

use PDF;
use App\Models\User;
use App\Models\State;
use App\Models\Countries;
use App\Helpers\Settings;
use App\Models\UserDetail;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Http\Redirect;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use Illuminate\Validation\Rule;
use App\Models\LocalGovernment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Image;
use Lang;
use App\Services\UserService;

use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    protected $breadcrumbStaffListing;
    protected $breadcrumbAddStaff;

    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->middleware('auth');
        $this->userService = $userService;

        $this->breadcrumbAddStaff = ['title' => __('translation.staff'), 'route1' => 'admin.staff', 'route1Title' => __('translation.staff') . ' ' . __('translation.listing'), 'route2' => 'admin.staff.store', 'route2Title' => __('translation.addstaff'), 'reset_route' => 'admin.staff', 'reset_route_title' => __('translation.cancel')];

        $this->breadcrumbStaffListing = ['title' => __('translation.staff') . ' ' . __('translation.listing'), 'route1' => 'admin.staff', 'route1Title' => __('translation.staff') . ' ' . __('translation.listing'), 'route2' => 'admin.staff.store', 'route2Title' => __('translation.addstaff'), 'reset_route' => 'admin.staff', 'reset_route_title' => __('translation.cancel')];
    }


    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbStaffListing;
        $designation = Designation::where('status',1)->pluck('name', 'id')->toArray();
        $updatedAt = date('Y-m-d');
        $updatedAt = '';
        $staffstatus = \Config::get('constants.staffstatus');
        $userList = User::select('*')->where('is_deleted', '0')->where('is_staff', 1)->where('designation_id', '>', 1)->where('account_id', Auth::user()->account_id);
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
        if (!empty(request()->get('hired_date'))) {
            $updatedAt = Settings::formatDate(request()->get('hired_date'), 'Y-m-d');
            $userList = $userList->whereDate('hire_date', $updatedAt);
        }
        $userList = $userList->orderBy('id', 'desc');
        // if ($request->has('pdf')) {
        //     $userList = $userList->get();
        //     $pdfHeaderdata = \Config::get('constants.staffspdf');
        //     $pdf = PDF::loadView('backend.pdf.staffspdf', compact('userList', 'pdfHeaderdata'))->setPaper('a4')->setOptions(['defaultFont' => 'sans-serif']);
        //     $pdf = Settings::downloadlandscapepdf($pdf);
        //     $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
        //     return $pdf->stream($fileName);
        // }
        // print_r($userList->count());
        // die;
        if ($request->has('csv')) {
            $userList = $userList->get();
            $pdfHeaderdata = \Config::get('constants.staffspdf');
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = 0;

            // Header row
            $data[$ii++] = [
                '#',
                __('translation.staff'),
                __('translation.email'),
                __('translation.username'),
                __('translation.designation'),
                __('translation.hired_date'),
                __('translation.status'),
                __('translation.createdat'),
            ];

            if (!empty($userList) && count($userList) > 0) {
                foreach ($userList as $i => $user) {
                    $status = '';
                    if (array_key_exists($user->is_active, \Config::get('constants.staffstatus'))) {
                        $status = \Config::get('constants.staffstatus')[$user->is_active];
                    }

                    $data[$ii++] = [
                        $i + 1,
                        $user->name ?? '-',
                        $user->email ?? '-',
                        $user->username ?? '-',
                        $user->designation->name ?? '-',
                        // ✅ Ensure hire date and created date formatted safely
                        !empty($user->hire_date) ? "\t" . $user->hire_date : '-',
                        $status,
                        !empty($user->created_at) ? "\t" . $user->created_at : '-',
                    ];
                }
            }
            else {
                // No records fallback
                $data[$ii++] = [__('translation.no_staff_found')];
            }
            Settings::downloadcsvfile($data, $fileName);
        }

        $userList = $userList->paginate(\Config::get('constants.pagination'));
        return view('backend.staff.index', compact("userList", "designation", "updatedAt", 'staffstatus', 'breadcrumb'));
    }

    public function staffspdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->index($request);
    }
    public function staffscsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->index($request);
    }

    public function statusUpdate(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = $request->input('status');
            $Menu = User::where("id", $id)->update(["is_active" => $status]);
            Session::flash('success', 'Data update successfully.');
        }
        catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }


    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            $is_deleted = 1;
            $Menu = User::where("id", $id)->update(["is_deleted" => $is_deleted]);
        }
        catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function create(Request $request)
    {
        $submitText = 'Save';
        $route = 'addroute';
        $breadcrumb = $this->breadcrumbAddStaff;
        $isimagechanged = 1;
        $suffix = \Config::get('constants.suffix');
        $emergecyRelationship = \Config::get('constants.emergecyRelationship');
        $staffstatus = \Config::get('constants.staffstatus');
        $state = State::select('name')->pluck('name', 'name')->toArray();
        $countries = Countries::select('country_name')->pluck('country_name', 'country_name')->toArray();

        $localGovernment = LocalGovernment::select('name')->pluck('name', 'name')->toArray();
        $designation = Designation::where('id', '!=', 1)->where('id', '!=', 3)->pluck('name', 'id')->toArray();
        return view('backend.staff.form', compact(["designation", "route", 'suffix', 'state', 'localGovernment', 'emergecyRelationship', 'staffstatus', 'isimagechanged', 'countries', 'breadcrumb', 'submitText']));
    }

    public function store(StoreStaffRequest $request)
    {

        try {

            $filename = 'default.png';

            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage($request, 'avatar', 'staff');
            }

            $user = $this->userService->createStaff($request, $filename);
            $userDetail = UserDetail::updateOrCreateDetail($user->id, $request->all());

            return Settings::roleRedirect('staff', 'Staff Added Successfully.');
        }
        catch (\Exception $e) {
            return Settings::roleRedirect('staff', 'Something went wrong!', 'error');
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
        $route = 'updateroute';
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbAddStaff,['title','route2','route2Title'],[__('translation.update_staff'),'admin.staff.update','Update Staff']);
        $id = Settings::getDecodeCode($id);
        $isimagechanged = 0;
        $designation = Designation::where('id', '!=', 1)->where('id', '!=', 3)->pluck('name', 'id')->toArray();
        $userDetails = User::where('id', $id)->where('account_id', Auth::user()->account_id)->first();
        $suffix = \Config::get('constants.suffix');
        $emergecyRelationship = \Config::get('constants.emergecyRelationship');
        $staffstatus = \Config::get('constants.staffstatus');
        $state = State::select('name')->pluck('name', 'name')->toArray();
        $localGovernment = LocalGovernment::select('name')->pluck('name', 'name')->toArray();
        $countries = Countries::select('country_name')->pluck('country_name', 'country_name')->toArray();
        return view('backend.staff.form', compact(["designation", "userDetails", "route", 'suffix', 'emergecyRelationship', 'staffstatus', 'state', 'localGovernment', 'isimagechanged', 'countries', 'breadcrumb']));
    }

    public function update(UpdateStaffRequest $request)
    {  
      
        // try {
            $id = Settings::getDecodeCode($request->user_id);
            $user = User::find($id);
            $filename = 'default.png';

            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage($request, 'avatar', 'staff');
            }
            // Update user
            $user = $this->userService->updateStaffBasic($user, $request);
            $userDetail = UserDetail::updateOrCreateDetail($id, $request->all());
            return Settings::roleRedirect('staff', 'Staff Updated Successfully. ');
        // }
        // catch (\Exception $e) {
        //     return Settings::roleRedirect('staff', 'Something went wrong! Please try again.', 'error');
        // }
    }
    public function updatepassword(Request $request)
    {
        $user = User::find($request->staff_id);
        $user->password = Hash::make($request->input('password'));
        $user->update();
        return;
    }
    public function stafflistAjax()
    {
        $userList = User::select('id', 'name')
            ->where('is_deleted', 0)
            ->where('is_staff', 2)
            ->where('designation_id', '>', 1)
            ->where('account_id', Auth::user()->account_id)
            ->where('is_active', 1)
            ->where('is_parent', 0)
            ->orderBy('id', 'desc')
            ->pluck('name', 'id'); // key => value
        return response()->json($userList);
    }
}
