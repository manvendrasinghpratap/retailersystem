<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DesignationController extends Controller
{
    protected array $breadcrumbAddNew;
    protected array $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:designation.view')->only(['index', 'viewAjax', 'viewAjaxPdf', 'getStock']);
        // $this->middleware('permission:designation.create')->only(['create', 'store']);
        // $this->middleware('permission:designation.cancel')->only(['cancel']);
        // $this->middleware('permission:designation.export')->only(['exportPdf', 'exportCsv']);

        $this->breadcrumbAddNew = [
            'title' => __('translation.designations'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.designations.index', 'title' => __('translation.designations')],
                ['route' => 'admin.designations.create', 'title' => __('translation.add_new_designation')]
            ],
            'route1' => 'admin.designations.create',
            'route1Title' => __('translation.add_new_designation'),
            'route2Title' => __('translation.add_new_designation'),
            'route2' => 'admin.designations.index',
            'reset_route' => 'admin.designations.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.designations'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.designations.index', 'title' => __('translation.designations')],
                ['route' => 'admin.designations.create', 'title' => __('translation.add_new_designation')]
            ],
            'route1' => 'admin.designations.index',
            'route1Title' => __('translation.designations'),
            'route2Title' => __('translation.add_new_designation'),
            'route2' => 'admin.designations.create',
            'route3Title' => __('translation.update_designation'),
            'route3' => 'admin.designations.edit',
            'reset_route' => 'admin.designations.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;

        // Base scope query
        $query = Designation::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0);

        // Search Filter
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . trim($request->name) . '%');
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // PDF Export
        if ($request->has('pdf')) {
            $designations = $query->orderBy('name')->get();
            $pdfHeaderdata = \Config::get('constants.designationListpdf');

            $pdf = PDF::loadView(
                'backend.pdf.designations.designationListpdf',
                compact('designations', 'pdfHeaderdata', 'breadcrumb')
            );

            return Settings::downloadpdf($pdf)->stream($pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf');
        }

        // CSV Export
        if ($request->has('csv')) {
            $designations = $query->orderBy('name')->get();
            $csvHeaderdata = \Config::get('constants.designationListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';

            $data = [];
            $data[0] = [
                '#',
                __('translation.designation'),
                __('translation.status'),
                __('translation.created_at'),
            ];

            foreach ($designations as $index => $designation) {
                $data[] = [
                    $index + 1,
                    $designation->name,
                    $designation->status ? __('translation.active') : __('translation.inactive'),
                    $designation->created_at ? "\t" . Settings::getFormattedDatetime($designation->created_at) : '-',
                ];
            }

            return Settings::downloadcsvfile($data, $fileName);
        }

        $designations = $query->orderBy('name')
            ->paginate(account_setting('general.pagination'));

        return view('backend.admin.designation.index', compact('designations', 'breadcrumb'));
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

    public function create()
    {
        return view('backend.admin.designation.form', [
            'breadcrumb' => $this->breadcrumbListing
        ]);
    }

    public function store(Request $request)
    {
        // Keep validation outside try-catch to allow standard Laravel redirect back with errors
        $request->validate([
            'name' => 'required|string|max:100|unique:designations,name,NULL,id,account_id,' . auth()->user()->account_id . ',is_deleted,0',
            'status' => 'required|in:0,1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                Designation::create([
                    'account_id' => auth()->user()->account_id,
                    'name' => ucwords(trim($request->name)),
                    'status' => $request->status,
                ]);
            });

            return Settings::roleRedirect(
                'designations.index',
                __('translation.designation_added_successfully')
            );
        } catch (\Exception $e) {
            return Settings::roleRedirect(
                'designations.index',
                __('translation.something_went_wrong'),
                'error'
            );
        }
    }

    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute(
            $this->breadcrumbListing,
            ['route3', 'route3Title'],
            ['admin.designations.update', __('translation.update_designation')]
        );

        $decodedId = Settings::getDecodeCode($id);

        $designation = Designation::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0)
            ->findOrFail($decodedId);

        return view('backend.admin.designation.form', compact('breadcrumb', 'designation'));
    }

    public function update(Request $request)
    {
        $id = Settings::getDecodeCode($request->designation_id);

        $designation = Designation::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0)
            ->findOrFail($id);

        // Unique check modified to ignore soft-deleted items
        $request->validate([
            'name' => 'required|string|max:100|unique:designations,name,' . $designation->id . ',id,account_id,' . auth()->user()->account_id . ',is_deleted,0',
            'status' => 'required|in:0,1',
        ]);

        try {
            $designation->update([
                'name' => ucwords(trim($request->name)),
                'status' => $request->status,
            ]);

            return Settings::roleRedirect(
                'designations.index',
                __('translation.designation_updated_successfully')
            );
        } catch (\Exception $e) {
            return Settings::roleRedirect(
                'designations.index',
                __('translation.something_went_wrong'),
                'error'
            );
        }
    }

    public function softdelete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);

            $deleted = Designation::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['is_deleted' => 1]);

            return response()->json([
                'success' => (bool) $deleted,
                'message' => $deleted
                    ? __('translation.deleted_successfully')
                    : __('translation.delete_failed')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.something_went_wrong')
            ], 500);
        }
    }
}