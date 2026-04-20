<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use App\Models\State;
use App\Models\Countries;
use App\Helpers\Settings;
use App\Models\UserDetail;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\LocalGovernment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffController extends Controller
{
    protected $breadcrumbStaffListing;
    protected $breadcrumbAddStaff;

    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
        $this->breadcrumbAddStaff = [
            'title' => __('translation.staff'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.staff.index',
                    'title' => __('translation.staff')
                ],
                [
                    'route' => 'admin.staff.add',
                    'title' => __('translation.addstaff')
                ],
            ],
            'route1' => 'admin.staff.index',
            'route1Title' => __('translation.staff') . ' ' . __('translation.listing'),
            'route2' => 'admin.staff.store',
            'route2Title' => __('translation.addstaff'),
            'reset_route' => 'admin.staff.index',
            'reset_route_title' => __('translation.reset')
        ];
        $this->breadcrumbStaffListing = [
            'title' => __('translation.staff'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.staff.index',
                    'title' => __('translation.staff')
                ],
                [
                    'route' => 'admin.staff.add',
                    'title' => __('translation.addstaff')
                ],
            ],
            'route1' => 'admin.staff.add',
            'route1Title' => __('translation.addstaff'),
            'route2' => 'admin.staff.store',
            'route2Title' => __('translation.addstaff'),
            'reset_route' => 'admin.staff.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }


    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbStaffListing;
        $designation = Designation::getSelectable();
        $updatedAt = date('Y-m-d');
        $updatedAt = '';
        $staffstatus = \Config::get('constants.staffstatus');
        $userList = User::select('*')->where('is_deleted', '0')->where('is_staff', 1)->where('designation_id', '>', 1)->where('account_id', Auth::user()->account_id);
        if (!empty(request('staff_name'))) {
            $userList = $userList->where('name', 'LIKE', '%' . trim(request('staff_name')) . '%');
        }
        if (!empty(request('designation_id'))) {
            $userList = $userList->where('designation_id', request('designation_id'));
        }
        if (request('is_active') == '0') {
            $userList = $userList->where('is_active', request('is_active'));
        }
        if (!empty(request('is_active'))) {
            $userList = $userList->where('is_active', request('is_active'));
        }
        if (!empty(request('hired_date'))) {
            $updatedAt = Settings::formatDate(request('hired_date'), 'Y-m-d');
            $userList = $userList->whereDate('hire_date', $updatedAt);
        }
        $userList = $userList->orderBy('id', 'desc');
        if ($request->has('pdf')) {
            $userList = $userList->get();
            $pdfHeaderdata = \Config::get('constants.staffspdf');
            $pdf = PDF::loadView('backend.pdf.staff.staffListpdf', compact('userList', 'pdfHeaderdata', 'breadcrumb', 'staffstatus'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $userList = $userList->get();
            $pdfHeaderdata = \Config::get('constants.staffspdf');
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = 0;

            // Header row
            $data[$ii++] = [
                '#',
                __('translation.staff_name'),
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
            } else {
                // No records fallback
                $data[$ii++] = [__('translation.no_staff_found')];
            }
            Settings::downloadcsvfile($data, $fileName);
        }

        $userList = $userList->paginate(\Config::get('constants.pagination'));
        return view('backend.staff.index', compact("userList", "designation", "updatedAt", 'staffstatus', 'breadcrumb'));
    }

    public function exportPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->index($request);
    }
    public function exportCsv(Request $request)
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function create(Request $request)
    {
        $submitText = 'Save';
        $breadcrumb = $this->breadcrumbAddStaff;
        $isimagechanged = 1;
        $suffix = \Config::get('constants.suffix');
        $emergecyRelationship = \Config::get('constants.emergecyRelationship');
        $staffstatus = \Config::get('constants.staffstatus');
        $designation = Designation::getSelectable();
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();
        return view('backend.staff.form', compact(["designation", 'suffix', 'state', 'localGovernment', 'emergecyRelationship', 'staffstatus', 'isimagechanged', 'countries', 'breadcrumb', 'submitText']));
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
        } catch (\Exception $e) {
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
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbAddStaff, ['route2', 'route2Title'], ['admin.staff.update', 'Update Staff']);
        $id = Settings::getDecodeCode($id);
        $isimagechanged = 0;
        $suffix = \Config::get('constants.suffix');
        $emergecyRelationship = \Config::get('constants.emergecyRelationship');
        $staffstatus = \Config::get('constants.staffstatus');

        $designation = Designation::getSelectable();
        $userDetails = User::getByAccount($id, Auth::user()->account_id);
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();
        return view('backend.staff.form', compact(["designation", "userDetails", 'suffix', 'emergecyRelationship', 'staffstatus', 'state', 'localGovernment', 'isimagechanged', 'countries', 'breadcrumb']));
    }

    public function update(UpdateStaffRequest $request)
    {
        try {
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
        } catch (\Exception $e) {
            return Settings::roleRedirect('staff', 'Something went wrong! Please try again.', 'error');
        }
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
