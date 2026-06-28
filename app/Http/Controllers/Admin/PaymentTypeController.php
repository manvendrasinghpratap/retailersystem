<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;
use Illuminate\Support\Str;

class PaymentTypeController extends Controller
{
    protected $breadcrumbListing = [];
    protected $breadcrumbAdd = [];
    protected $breadcrumbEdit = [];

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.payment_types'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.payment-types.index',
                    'title' => __('translation.payment_types')
                ],
                [
                    'route' => 'admin.payment-types.create',
                    'title' => __('translation.add_payment_type')
                ]
            ],
            'route1' => 'admin.payment-types.create',
            'route1Title' => __('translation.add_payment_type'),
            'route2Title' => __('translation.add_payment_type'),
            'route2' => 'admin.payment-types.index',
            'reset_route' => 'admin.payment-types.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.payment_types'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.payment-types.index',
                    'title' => __('translation.payment_types')
                ],
                [
                    'route' => 'admin.payment-types.create',
                    'title' => __('translation.add_payment_type')
                ]
            ],
            'route1' => 'admin.payment-types.index',
            'route1Title' => __('translation.payment_types'),
            'route2Title' => __('translation.add_payment_type'),
            'route2' => 'admin.payment-types.index',
            'route3Title' => __('translation.update_payment_type'),
            'route3' => 'admin.payment-types.edit',
            'reset_route' => 'admin.payment-types.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Listing
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbListing;
        $paymentTypes = PaymentType::ofAccount();
        if ($request->filled('name')) {
            $paymentTypes->where('name', 'LIKE', '%' . trim($request->name) . '%');
        }
        if ($request->filled('status')) {
            $paymentTypes->where('status', $request->status);
        }
        if ($request->pdf) {
            $paymentTypes = $paymentTypes->ordered()->get();
            $pdfHeaderdata = config('constants.paymentTypepdf');
            $pdf = PDF::loadView(
                'backend.pdf.paymenttype.paymentTypeListpdf',
                compact(
                    'paymentTypes',
                    'pdfHeaderdata',
                    'breadcrumb'
                )
            );
            $pdf = Settings::downloadpdf($pdf);
            return $pdf->stream(
                $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf'
            );
        }
        if ($request->csv) {
            $paymentTypes = $paymentTypes->ordered()->get();
            $pdfHeaderdata = config('constants.paymentTypepdf');
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $i = 0;
            $data[$i++] = [
                '#',
                __('translation.name'),
                __('translation.status'),
                __('translation.createdat'),
            ];
            if ($paymentTypes->count()) {
                foreach ($paymentTypes as $key => $paymentType) {
                    $data[$i++] = [
                        $key + 1,
                        $paymentType->name,
                        $paymentType->status_text,
                        "\t" . $paymentType->created_at,
                    ];
                }
            } else {
                $data[$i++] = [
                    __('translation.no_data_found')
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $paymentTypes = $paymentTypes->ordered()->paginate(config('pagination'));

        return view(
            'backend.admin.paymenttype.index',
            compact(
                'paymentTypes',
                'breadcrumb'
            )
        );
    }

    /**
     * PDF Export
     */
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
     * Show Create Form
     */
    public function create()
    {
        $breadcrumb = $this->breadcrumbListing;

        return view(
            'backend.admin.paymenttype.form',
            compact('breadcrumb')
        );
    }

    /**
     * Store Payment Type
     */
    public function store(Request $request)
    {
        $shortName = strtolower(substr(preg_replace('/[^a-z]/', '', Str::ascii($request->name)), 0, 5));
        $request->merge(['short_name' => $shortName]);
        $request->validate([
            'short_name' => [
                'required',
                'max:50',
                'alpha_dash',
                Rule::unique('payment_types')->where(function ($query) {
                    return $query->where(
                        'account_id',
                        auth()->user()->account_id
                    );
                }),
            ],

            'name' => [
                'required',
                'max:100',
                Rule::unique('payment_types')->where(function ($query) {
                    return $query->where(
                        'account_id',
                        auth()->user()->account_id
                    );
                }),
            ],
            'status' => 'required|boolean',
        ]);

        PaymentType::create([
            'short_name' => strtolower(trim($request->short_name)),
            'name' => trim($request->name),
            'status' => $request->status,
        ]);

        return redirect()->route('admin.payment-types.index')->with('success', __('translation.record_added_successfully'));
    }

    /**
     * Edit Form
     */
    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);

        $paymentType = PaymentType::ofAccount()
            ->findOrFail($id);

        $breadcrumb = $this->breadcrumbListing;

        return view(
            'backend.admin.paymenttype.form',
            compact(
                'paymentType',
                'breadcrumb'
            )
        );
    }

    /**
     * Update Payment Type
     */
    public function update(Request $request, $id)
    {
        $id = Settings::getDecodeCode($id);
        $paymentType = PaymentType::ofAccount()->findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'max:100',
                Rule::unique('payment_types')
                    ->ignore($paymentType->id)
                    ->where(function ($query) {
                        return $query->where(
                            'account_id',
                            auth()->user()->account_id
                        );
                    }),
            ],
            'status' => 'required|boolean',
        ]);
        $paymentType->update([
            'name' => trim($request->name),
            'status' => $request->status,
        ]);
        return redirect()->route('admin.payment-types.index')->with('success', __('translation.record_updated_successfully'));
    }

    /**
     * Delete Payment Type
     */
    public function softdelete($id)
    {
        try {
            $id = Settings::getDecodeCode($id);
            $paymentType = PaymentType::ofAccount()->findOrFail($id);
            $paymentType->delete();
            return response()->json([
                'status' => true,
                'message' => __('translation.record_deleted_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Status
     */
    public function statusUpdate(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required',
                'status' => 'required|boolean',
            ]);

            $id = Settings::getDecodeCode($request->id);

            $paymentType = PaymentType::ofAccount()
                ->findOrFail($id);

            $paymentType->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => __('translation.status_updated_successfully'),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }
}