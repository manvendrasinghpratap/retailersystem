<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditDuration;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditDurationController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.credit_durations'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.credit-line.index',
                    'title' => __('translation.credit_durations')
                ],
                [
                    'route' => 'admin.credit-line.create',
                    'title' => __('translation.add_credit_duration')
                ]
            ],
            'route1' => 'admin.credit-line.create',
            'route1Title' => __('translation.add_credit_duration'),
            'route2Title' => __('translation.add_credit_duration'),
            'route2' => 'admin.credit-line.index',
            'reset_route' => 'admin.credit-line.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.credit_durations'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.credit-line.index',
                    'title' => __('translation.credit_durations')
                ],
                [
                    'route' => 'admin.credit-line.create',
                    'title' => __('translation.add_credit_duration')
                ]
            ],
            'route1' => 'admin.credit-line.index',
            'route1Title' => __('translation.credit_durations'),
            'route2Title' => __('translation.add_credit_duration'),
            'route2' => 'admin.credit-line.create',
            'route3Title' => __('translation.update_credit_duration'),
            'route3' => 'admin.credit-line.edit',
            'reset_route' => 'admin.credit-line.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Listing
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $creditDurations = CreditDuration::ofAccount();
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */
        if ($request->filled('name')) {
            $creditDurations->where(
                'name',
                'LIKE',
                '%' . trim($request->name) . '%'
            );
        }
        if ($request->filled('duration_days')) {
            $creditDurations->where('duration_days', $request->duration_days);
        }

        if ($request->status !== null && $request->status !== '') {
            $creditDurations->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | PDF Export
        |--------------------------------------------------------------------------
        */
        if ($request->has('pdf')) {
            $creditDurations = $creditDurations->latest()->get();
            $pdfHeaderdata = config('constants.creditDurationpdf');
            $pdf = PDF::loadView(
                'backend.pdf.creditduration.creditDurationListpdf',
                compact(
                    'creditDurations',
                    'pdfHeaderdata',
                    'breadcrumb'
                )
            );
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        /*
        |--------------------------------------------------------------------------
        | CSV Export
        |--------------------------------------------------------------------------
        */
        if ($request->has('csv')) {
            $creditDurations = $creditDurations->latest()->get();
            $csvHeaderdata = config('constants.creditDurationpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $i = 0;
            $data[$i++] = [
                '#',
                __('translation.name'),
                __('translation.duration_days'),
                __('translation.interest'),
                __('translation.status'),
                __('translation.created_at')
            ];
            foreach ($creditDurations as $key => $duration) {
                $data[$i++] = [
                    $key + 1,
                    $duration->name,
                    $duration->duration_days,
                    $duration->interest . '%',
                    $duration->status == 1
                    ? __('translation.active')
                    : __('translation.inactive'),
                    "\t" . Settings::getFormattedDatetime($duration->created_at)
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $creditDurations = $creditDurations->latest()->paginate(config('constants.pagination'));

        return view('backend.admin.creditduration.index', compact('creditDurations', 'breadcrumb'));
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $request->merge([
            'pdf' => 1
        ]);
        return $this->index($request);
    }

    /**
     * Export CSV
     */
    public function exportCsv(Request $request)
    {
        $request->merge([
            'csv' => 1
        ]);

        return $this->index($request);
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('backend.admin.creditduration.form', ['breadcrumb' => $this->breadcrumbListing]);
    }

    /**
     * Store Credit Duration
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50|unique:credit_durations,name,NULL,id,account_id,' . auth()->user()->account_id,
            'duration_days' => 'required|integer|min:1',
            'interest' => 'required|numeric|min:0|max:100',
        ]);

        try {
            CreditDuration::create([
                'account_id' => auth()->user()->account_id,
                'name' => $request->name,
                'duration_days' => $request->duration_days,
                'interest' => $request->interest,
                'status' => $request->status ?? 1,
                'created_by' => auth()->id(),
            ]);
            return Settings::roleRedirect('credit-line.index', __('translation.credit_duration_added_successfully'));

        } catch (\Exception $e) {
            return Settings::roleRedirect('credit-line.index', __('translation.something_went_wrong'), 'error');
        }
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbListing, ['route3', 'route3Title'], ['admin.credit-line.update', __('translation.update_credit_duration')]);
        $id = Settings::getDecodeCode($id);
        $creditDuration = CreditDuration::ofAccount()->findOrFail($id);
        return view('backend.admin.creditduration.form', compact('breadcrumb', 'creditDuration'));
    }

    /**
     * Update
     */
    public function update(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->credit_duration_id);
            $creditDuration = CreditDuration::ofAccount()->findOrFail($id);
            $request->validate([
                'name' => 'required|max:50|unique:credit_durations,name,' . $creditDuration->id . ',id,account_id,' . auth()->user()->account_id,
                'duration_days' => 'required|integer|min:1',
                'interest' => 'required|numeric|min:0|max:100',
            ]);
            $creditDuration->update([
                'name' => $request->name,
                'duration_days' => $request->duration_days,
                'interest' => $request->interest,
                'status' => $request->status ?? 1,
            ]);
            return Settings::roleRedirect('credit-line.index', __('translation.credit_duration_updated_successfully'));
        } catch (\Exception $e) {
            return Settings::roleRedirect('credit-line.index', __('translation.something_went_wrong'), 'error');
        }
    }
    /**
     * Status Update
     */
    public function statusUpdate(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);
            $updated = CreditDuration::ofAccount()->where('id', $id)->update(['status' => $request->status]);
            return response()->json([
                'success' => $updated ? true : false,
                'message' => $updated ? __('translation.status_updated') : __('translation.update_failed')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.something_went_wrong')
            ], 500);
        }
    }

    /**
     * Delete
     */
    public function destroy(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);
            $deleted = CreditDuration::ofAccount()->where('id', $id)->delete();
            return response()->json([
                'success' => $deleted ? true : false,
                'message' => $deleted ? __('translation.deleted_successfully') : __('translation.delete_failed')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.something_went_wrong')
            ], 500);
        }
    }
}