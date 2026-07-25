<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DesignationController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbAddNew = [
            'title' => __('translation.designations'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.designations.index',
                    'title' => __('translation.designations')
                ],
                [
                    'route' => 'admin.designations.create',
                    'title' => __('translation.add_new_designation')
                ]
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
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.designations.index',
                    'title' => __('translation.designations')
                ],
                [
                    'route' => 'admin.designations.create',
                    'title' => __('translation.add_new_designation')
                ]
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
        $designations = Designation::OfAccount()->OfActive();
        // Search Filter
        if ($request->filled('name')) {
            $designations->where('name', 'LIKE', '%' . trim($request->name) . '%');
        }

        // Status Filter
        if ($request->status !== null && $request->status !== '') {
            $designations->where('status', $request->status);
        }

        // PDF Export
        if ($request->has('pdf')) {

            $designations = $designations->orderBy('name')->get();

            $pdfHeaderdata = \Config::get('constants.designationListpdf');

            $pdf = PDF::loadView(
                'backend.pdf.designations.designationListpdf',
                compact('designations', 'pdfHeaderdata', 'breadcrumb')
            );

            $pdf = Settings::downloadpdf($pdf);

            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';

            return $pdf->stream($fileName);
        }

        // CSV Export
        if ($request->has('csv')) {

            $designations = $designations->orderBy('name')->get();

            $csvHeaderdata = \Config::get('constants.designationListpdf');

            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';

            $data = [];

            $ii = 0;

            // Header
            $data[$ii] = [
                '#',
                __('translation.designation'),
                __('translation.status'),
                __('translation.created_at'),
            ];

            foreach ($designations as $designation) {

                $data[++$ii] = [
                    $ii,
                    $designation->name,
                    $designation->status ? __('translation.active') : __('translation.inactive'),
                    !empty($designation->created_at)
                    ? "\t" . Settings::getFormattedDatetime($designation->created_at)
                    : '-',
                ];
            }

            return Settings::downloadcsvfile($data, $fileName);
        }

        $designations = $designations
            ->orderBy('name')
            ->paginate(account_setting('general.pagination'));

        return view('backend.admin.designation.index', compact(
            'designations',
            'breadcrumb'
        ));
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

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        return view('backend.admin.designation.form', [
            'breadcrumb' => $this->breadcrumbListing
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100|unique:designations,name,NULL,id,account_id,' . auth()->user()->account_id,
            'status' => 'required|in:0,1',
        ]);

        try {

            Designation::create([
                'account_id' => auth()->user()->account_id,
                'name' => ucwords(trim($request->name)),
                'status' => $request->status,
            ]);

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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute(
            $this->breadcrumbListing,
            ['route3', 'route3Title'],
            ['admin.designations.update', __('translation.update_designation')]
        );

        $id = Settings::getDecodeCode($id);

        $designation = Designation::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0)
            ->findOrFail($id);

        return view('backend.admin.designation.form', [
            'breadcrumb' => $breadcrumb,
            'designation' => $designation
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->designation_id);

            $designation = Designation::where('account_id', auth()->user()->account_id)
                ->where('is_deleted', 0)
                ->findOrFail($id);

            $request->validate([
                'name' => 'required|max:100|unique:designations,name,' . $designation->id . ',id,account_id,' . auth()->user()->account_id,
                'status' => 'required|in:0,1',
            ]);

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
                ->update([
                    'is_deleted' => 1
                ]);

            return response()->json([
                'success' => $deleted ? true : false,
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
