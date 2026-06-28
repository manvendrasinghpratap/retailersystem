<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Helpers\Settings;
use App\Models\SalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentType;
use App\Models\PaymentMethod;
use Auth;
class ReportController extends Controller
{

    protected $breadcrumbDailySales;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbDailySales = [
            'title' => __('translation.daily_sales_report'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'reports.daily.sales',
                    'title' => __('translation.daily_sales_report')
                ]
            ],
            'route1' => "reports.daily.sales",
            'route1Title' => __('translation.daily_sales_report'),
            'route2Title' => __('translation.daily_sales_report'),
            'route2' => 'reports.daily.sales',
            'reset_route' => 'reports.daily.sales',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.customers'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.customers.index',
                    'title' => __('translation.customers')
                ]
            ],
            'route1' => "admin.customers.index",
            'route1Title' => __('translation.customers'),
            'route2Title' => __('translation.add_new_customer'),
            'route2' => 'admin.customers.create',
            'route3Title' => __('translation.add_new_customer'),
            'route3' => 'admin.customers.edit',
            'reset_route' => 'admin.customers.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Summary of dailySales
     * @param Request $request
     */
    public function dailySales(Request $request)
    {
        $paymentMethods = PaymentMethod::getSelectableWithData()->toArray();
        $paymentTypes = PaymentType::getSelectable();
        $breadcrumb = $this->breadcrumbDailySales;
        $accountId = auth()->user()->account_id;
        $staffId = $request->staff_id;

        $fromDate = Settings::checkAndformatDate($request->from_date, 'Y-m-d');
        $toDate = Settings::checkAndformatDate($request->to_date, 'Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */
        if ($fromDate && $toDate) {

            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::parse($toDate)->endOfDay();

        } elseif ($fromDate) {

            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::now()->endOfDay();

        } elseif ($toDate) {

            $start = Carbon::parse($toDate)->startOfDay();
            $end = Carbon::parse($toDate)->endOfDay();

        } else {

            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */
        $query = Sale::where('account_id', $accountId)
            ->visibleToUser()
            ->whereBetween('created_at', [$start, $end]);

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', $request->invoice_no);
        }

        if (!empty($staffId)) {
            $query->where('user_id', $staffId);
        }

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */
        $totalSales = (clone $query)->sum('total');
        $totalOrders = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | Payment Totals
        |--------------------------------------------------------------------------
        | Use filtered sale IDs so every filter is respected.
        |--------------------------------------------------------------------------
        */

        $saleIds = (clone $query)->pluck('id');

        $paymentTotals = SalePayment::whereIn('sale_id', $saleIds)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Sales List
        |--------------------------------------------------------------------------
        */

        $sales = $query->with([
            'customer:id,name',
            'user:id,name',
            'payments:id,sale_id,method,amount'
        ])
            ->latest()
            ->paginate(config('pagination'))
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Staff List
        |--------------------------------------------------------------------------
        */

        $staffs = User::where('account_id', $accountId)
            ->visibleToUser()
            ->pluck('name', 'id');

        if ($request->has('pdf')) {
            $pdfHeaderdata = \Config::get('constants.dailySalespdf');
            $pdfSales = (clone $query)->with([
                'customer:id,name',
                'user:id,name',
                'payments:id,sale_id,method,amount'
            ])
                ->latest()
                ->get();
            $pdf = PDF::loadView('backend.pdf.reports.dailySalespdf', compact('pdfSales', 'pdfHeaderdata', 'totalSales', 'totalOrders', 'staffs', 'staffId', 'breadcrumb', 'paymentMethods', 'paymentTotals', 'paymentTypes'));
            $pdf = Settings::downloadlandscapepdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        if ($request->has('csv')) {

            $pdfHeaderdata = \Config::get('constants.dailySalespdf');
            $salesList = (clone $query)->with([
                'customer:id,name',
                'user:id,name',
                'payments:id,sale_id,method,amount'
            ])
                ->latest()
                ->get();

            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';

            $data = [];
            $ii = 0;

            /*
            |--------------------------------------------------------------------------
            | Header Row
            |--------------------------------------------------------------------------
            */

            $header = [
                '#',
                __('translation.invoice_no'),
                __('translation.customer_name'),
            ];

            if (Auth::user()->hasDesignation()) {
                $header[] = __('translation.staff_name');
            }

            $header[] = __('translation.payment_type');

            foreach ($paymentMethods as $method) {
                $header[] = __('translation.currency') . ' ' . $method['name'];
            }

            $header[] = __('translation.currency') . ' ' . __('translation.total_amount');
            $header[] = __('translation.transaction_date');

            $data[$ii++] = $header;

            /*
            |--------------------------------------------------------------------------
            | Data Rows
            |--------------------------------------------------------------------------
            */

            if ($salesList->count()) {

                foreach ($salesList as $i => $sale) {

                    $summary = $sale->payments
                        ->groupBy('method')
                        ->map(fn($items) => $items->sum('amount'));

                    $row = [
                        $i + 1,
                        $sale->invoice_no ?? '-',
                        $sale->customer->name ?? '-',
                    ];

                    if (Auth::user()->hasDesignation()) {
                        $row[] = $sale->user->name ?? '-';
                    }

                    $row[] = $sale->payment_method
                        ? __('translation.full_payment')
                        : __('translation.partial_payment');

                    foreach ($paymentMethods as $method) {
                        $row[] = number_format($summary[$method['short_name']] ?? 0, 2, '.', '');
                    }

                    $row[] = number_format($sale->total ?? 0, 2, '.', '');
                    $row[] = !empty($sale->created_at)
                        ? "\t" . \App\Helpers\Settings::getFormattedDatetime($sale->created_at)
                        : '-';

                    $data[$ii++] = $row;
                }

                /*
                |--------------------------------------------------------------------------
                | Totals Row
                |--------------------------------------------------------------------------
                */

                $totalRow = [
                    '',
                    '',
                    '',
                ];

                if (Auth::user()->hasDesignation()) {
                    $totalRow[] = '';
                }

                $totalRow[] = strtoupper(__('translation.total'));

                foreach ($paymentMethods as $method) {
                    $totalRow[] = __('translation.currency') . ' ' .
                        number_format($paymentTotals[$method['short_name']] ?? 0, 2);
                }

                $totalRow[] = __('translation.currency') . ' ' . number_format($totalSales, 2);
                $totalRow[] = '';

                $data[$ii++] = $totalRow;

            } else {

                $data[$ii++] = [
                    __('translation.no_data_found')
                ];
            }

            return Settings::downloadcsvfile($data, $fileName);
        }
        return view('backend.admin.reports.daily_sales', compact(
            'sales',
            'totalSales',
            'totalOrders',
            'staffs',
            'staffId',
            'breadcrumb',
            'paymentMethods',
            'paymentTotals',
            'paymentTypes'
        ));
    }

    public function dailySalesPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->dailySales($request);
    }
    public function dailySalesCsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->dailySales($request);
    }
}
