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
use App\Models\Store;

class StaffController extends Controller
{
    /**
     * Breadcrumb navigation configurations for staff views.
     */
    protected $breadcrumbStaffListing;
    protected $breadcrumbAddStaff;

    /**
     * Service layer dependency for business logic decoupling.
     */
    protected $userService;

    /**
     * Initialize dependencies, auth middleware, permission definitions, and page breadcrumbs.
     * 
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        // Require authentication for all methods in this controller
        $this->middleware('auth');

        // Apply route-level authorization permissions using spatie/middleware
        $this->middleware('permission:staff.index')->only(['index']);
        $this->middleware('permission:staff.create')->only(['create', 'store']);
        $this->middleware('permission:staff.edit')->only(['editstaff', 'update', 'updatepassword']);
        $this->middleware('permission:staff.destroy')->only(['delete']);
        $this->middleware('permission:staff.export')->only(['exportPdf', 'exportCsv', 'downloadstaffpdf']);

        $this->userService = $userService;

        // Configure dynamic breadcrumb links for 'Add Staff' screen
        $this->breadcrumbAddStaff = [
            'title' => __('translation.staff'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.staff.index', 'title' => __('translation.staff')],
                ['route' => 'admin.staff.add', 'title' => __('translation.addstaff')],
            ],
            'route1' => 'admin.staff.index',
            'route1Title' => __('translation.staff') . ' ' . __('translation.listing'),
            'route2' => 'admin.staff.store',
            'route2Title' => __('translation.addstaff'),
            'reset_route' => 'admin.staff.index',
            'reset_route_title' => __('translation.cancel')
        ];

        // Configure dynamic breadcrumb links for 'Staff Listing' screen
        $this->breadcrumbStaffListing = [
            'title' => __('translation.staff'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.staff.index', 'title' => __('translation.staff')],
                ['route' => 'admin.staff.add', 'title' => __('translation.addstaff')],
            ],
            'route1' => 'admin.staff.add',
            'route1Title' => __('translation.addstaff'),
            'route2' => 'admin.staff.store',
            'route2Title' => __('translation.addstaff'),
            'reset_route' => 'admin.staff.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Helper Method: Reusable filtered query builder for staff records.
     * Enforces tenant isolation via `ofAccount()` and applies active request filters.
     * 
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFilteredStaffQuery(Request $request)
    {
        // Base query: fetch non-deleted staff members assigned to current tenant account
        $userList = User::where('is_deleted', '0')
            ->where('is_staff', 1)
            ->where('designation_id', '>', 1)
            ->ofAccount(); // Multi-tenant scope

        // Filter by Staff Name
        if ($request->filled('staff_name')) {
            $userList->where('name', 'LIKE', '%' . trim($request->staff_name) . '%');
        }

        // Filter by Designation ID
        if ($request->filled('designation_id')) {
            $userList->where('designation_id', $request->designation_id);
        }

        // Filter by Store Assignment
        if ($request->filled('store_id')) {
            $userList->where('store_id', $request->store_id);
        }

        // Filter by Active Status (supports boolean '0')
        if ($request->filled('is_active')) {
            $userList->where('is_active', $request->is_active);
        }

        // Filter by Hire Date
        // ✅ FIXED: Filter by hire_date on the user_details relationship table
        if ($request->filled('hired_date')) {
            $updatedAt = Settings::formatDate($request->hired_date, 'Y-m-d');
            $userList->whereHas('detail', function ($query) use ($updatedAt) {
                $query->whereDate('hire_date', $updatedAt);
            });
        }

        return $userList->orderBy('id', 'desc');
    }

    /**
     * Display paginated list of staff members.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbStaffListing;
        $designation = Designation::getSelectable();
        $stores = Store::getSelectableByUser();
        $staffstatus = \Config::get('constants.staffstatus');
        $updatedAt = '';

        // Retrieve filtered paginated results
        $userList = $this->getFilteredStaffQuery($request)
            ->paginate(account_setting('general.pagination'));

        return view('backend.staff.index', compact("userList", "designation", "updatedAt", 'staffstatus', 'breadcrumb', 'stores'));
    }

    /**
     * Generate and stream staff list as a PDF document.
     * Dedicated method enforcing `permission:staff.export`.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $breadcrumb = $this->breadcrumbStaffListing;
        $stores = Store::getSelectableByUser();
        $staffstatus = \Config::get('constants.staffstatus');

        // Fetch full dataset matching applied filters
        $userList = $this->getFilteredStaffQuery($request)->get();
        $pdfHeaderdata = \Config::get('constants.staffspdf');

        // Compile landscape layout PDF stream
        $pdf = PDF::loadView('backend.pdf.staff.staffListpdf', compact('userList', 'pdfHeaderdata', 'breadcrumb', 'staffstatus', 'stores'));
        $pdf = Settings::downloadLandscapePdf($pdf);
        $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Export staff records as a downloadable CSV file.
     * Dedicated method enforcing `permission:staff.export`.
     * 
     * @param Request $request
     * @return void
     */
    public function exportCsv(Request $request)
    {
        // Fetch full dataset matching applied filters
        $userList = $this->getFilteredStaffQuery($request)->get();
        $pdfHeaderdata = \Config::get('constants.staffspdf');
        $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';

        $data = [];
        $ii = 0;

        // Construct CSV Column Header Row
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

        // Format row values safely
        if ($userList->isNotEmpty()) {
            foreach ($userList as $i => $user) {
                $status = \Config::get('constants.staffstatus')[$user->is_active] ?? '-';
                $data[$ii++] = [
                    $i + 1,
                    $user->name ?? '-',
                    $user->email ?? '-',
                    $user->username ?? '-',
                    $user->designation->name ?? '-',
                    !empty($user->detail->hire_date) ? "\t" . $user->detail->hire_date : '-',
                    $status,
                    !empty($user->created_at) ? "\t" . $user->created_at : '-',
                ];
            }
        } else {
            $data[$ii++] = [__('translation.no_staff_found')];
        }

        // Trigger CSV forced browser download
        Settings::downloadcsvfile($data, $fileName);
    }

    /**
     * Toggle staff user active status via AJAX.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusUpdate(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = $request->input('status');

            // Secure status update with account tenant boundary check
            User::where("id", $id)
                ->where('account_id', Auth::user()->account_id)
                ->update(["is_active" => $status]);

            return response()->json(['success' => true, 'message' => __('translation.updated_successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('translation.something_went_wrong')], 500);
        }
    }

    /**
     * Soft-delete staff member via AJAX endpoint.
     * Enforces `permission:staff.destroy`.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');

            // Set soft-deletion flag ensuring record belongs to logged-in user's account
            $updated = User::where('id', $id)
                ->where('account_id', Auth::user()->account_id)
                ->update(['is_deleted' => 1]);

            if ($updated) {
                return response()->json(['success' => true, 'message' => __('translation.deleted_successfully')]);
            }
            return response()->json(['success' => false, 'message' => __('translation.delete_failed')], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('translation.something_went_wrong')], 500);
        }
    }

    /**
     * Render the form to create a new staff member.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $stores = Store::getSelectableByUser();
        $submitText = 'Save';
        $breadcrumb = $this->breadcrumbAddStaff;
        $isimagechanged = 1;
        $suffix = \Config::get('constants.suffix');
        $emergecyRelationship = \Config::get('constants.emergecyRelationship');
        $staffstatus = \Config::get('constants.staffstatus');
        $designation = Designation::getSelectable();
        $cashierDesignation = Designation::getDesignationIdOfCashier();
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();

        return view('backend.staff.form', compact(["cashierDesignation", "designation", 'suffix', 'state', 'localGovernment', 'emergecyRelationship', 'staffstatus', 'isimagechanged', 'countries', 'breadcrumb', 'submitText', 'stores']));
    }

    /**
     * Store new staff member record using FormRequest validation.
     * 
     * @param StoreStaffRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreStaffRequest $request)
    {
        try {
            $filename = 'default.png';

            // Process uploaded avatar image if provided
            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage($request, 'avatar', 'staff');
            }

            // Create main User and secondary UserDetail via Service Layer
            $user = $this->userService->createStaff($request, $filename);
            UserDetail::updateOrCreateDetail($user->id, $request->all());

            return Settings::roleRedirect('staff', 'Staff Added Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('staff', 'Something went wrong!', 'error');
        }
    }

    /**
     * Download alternate PDF layout for internal staff lists.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadstaffpdf(Request $request)
    {
        $updatedAt = '';
        $userList = User::where('is_deleted', '0')
            ->where('is_staff', 2)
            ->where('designation_id', '>', 1)
            ->ofAccount(); // Account isolation applied

        if ($request->filled('staff_name')) {
            $userList->where('name', 'LIKE', '%' . trim($request->staff_name) . '%');
        }
        if ($request->filled('designation_id')) {
            $userList->where('designation_id', $request->designation_id);
        }
        if ($request->filled('is_active')) {
            $userList->where('is_active', $request->is_active);
        }
        if ($request->filled('hire_date')) {
            $updatedAt = $request->hire_date;
            $userList->whereDate('hire_date', $request->hire_date);
        }

        $userList = $userList->orderBy('id', 'desc')->get();
        $pdf = PDF::loadView('backend.staff.downloadstaffpdf', compact("userList", "updatedAt"))->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->stream('Staff-list.pdf');
    }

    /**
     * Render edit form for an existing staff record.
     * 
     * @param Request $request
     * @param string $id Encoded record identifier
     * @return \Illuminate\View\View
     */
    public function editstaff(Request $request, $id)
    {
        $stores = Store::getSelectableByUser();
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbAddStaff, ['route2', 'route2Title'], ['admin.staff.update', 'Update Staff']);

        // Decode URL parameter to get integer Primary Key
        $id = Settings::getDecodeCode($id);
        $isimagechanged = 0;
        $suffix = \Config::get('constants.suffix');
        $emergecyRelationship = \Config::get('constants.emergecyRelationship');
        $staffstatus = \Config::get('constants.staffstatus');

        $designation = Designation::getSelectable();
        // Retrieve target user enforcing current tenant ownership
        $userDetails = User::getByAccount($id, Auth::user()->account_id);
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();

        return view('backend.staff.form', compact(["designation", "userDetails", 'suffix', 'emergecyRelationship', 'staffstatus', 'state', 'localGovernment', 'isimagechanged', 'countries', 'breadcrumb', 'stores']));
    }

    /**
     * Update existing staff profile details using validated input.
     * 
     * @param UpdateStaffRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateStaffRequest $request)
    {
        try {
            $id = Settings::getDecodeCode($request->user_id);

            // Fetch target staff record safely within tenant scope
            $user = User::where('id', $id)->ofAccount()->firstOrFail();
            $filename = 'default.png';

            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage($request, 'avatar', 'staff');
            }

            // Update models using Service layer logic
            $user = $this->userService->updateStaffBasic($user, $request);
            UserDetail::updateOrCreateDetail($id, $request->all());

            return Settings::roleRedirect('staff', 'Staff Updated Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('staff', 'Something went wrong! Please try again.', 'error');
        }
    }

    /**
     * Reset staff password via modal AJAX request.
     * Protected by `permission:staff.edit`.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatepassword(Request $request)
    {
        // Backend input validation
        $request->validate([
            'staff_id' => 'required',
            'password' => 'required|min:6'
        ]);

        try {
            // Retrieve staff user belonging strictly to current logged-in account
            $user = User::where('id', $request->staff_id)
                ->ofAccount()
                ->firstOrFail();

            // Hash password and save changes
            $user->password = Hash::make($request->input('password'));
            $user->save();

            return response()->json(['success' => true, 'message' => __('translation.password_updated_successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('translation.something_went_wrong')], 500);
        }
    }

    /**
     * Fetch simple key-value list of active staff for AJAX dropdowns.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function stafflistAjax()
    {
        $userList = User::select('id', 'name')
            ->where('is_deleted', 0)
            ->where('is_staff', 2)
            ->where('designation_id', '>', 1)
            ->ofAccount() // Enforces tenant isolation
            ->where('is_active', 1)
            ->where('is_parent', 0)
            ->orderBy('id', 'desc')
            ->pluck('name', 'id');

        return response()->json($userList);
    }
}