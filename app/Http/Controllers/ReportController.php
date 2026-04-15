<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Helpers\Settings;
use App\Models\SalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
class ReportController extends Controller
{

    protected $breadcrumbDailySales;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbDailySales = [
            'title' => __('translation.daily_sales_report'),
            'route1' => "reports.daily.sales",
            'route1Title' => __('translation.daily_sales_report'),
            'route2Title' => __('translation.daily_sales_report'),
            'route2' => 'reports.daily.sales',
            'reset_route' => 'reports.daily.sales',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.customers'),
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

    public function dailySales(Request $request)
    {
        $breadcrumb = $this->breadcrumbDailySales;
        $accountId = auth()->user()->account_id;
        $staffId = $request->staff_id;

        $fromDate = Settings::checkAndformatDate($request->get('from_date'), 'Y-m-d');
        $toDate   = Settings::checkAndformatDate($request->get('to_date'), 'Y-m-d');

        // ✅ Smart Date Filtering
        if (!empty($fromDate) && !empty($toDate)) {
        // Between from & to
        $start = Carbon::parse($fromDate)->startOfDay();
        $end   = Carbon::parse($toDate)->endOfDay();

        } elseif (!empty($fromDate)) {
        // From date → today
        $start = Carbon::parse($fromDate)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        } elseif (!empty($toDate)) {
        // Beginning → to date
        $start = Carbon::parse($toDate)->startOfDay(); // OR earliest date if needed
        $end   = Carbon::parse($toDate)->endOfDay();

        } else {
        // Default → today
        $start = Carbon::today()->startOfDay();
        $end   = Carbon::today()->endOfDay();
        }
        // =========================
        // Base Query
        // =========================
        $query = Sale::where('account_id', $accountId)
            ->visibleToUser()
            ->whereBetween('created_at', [$start, $end]);
        if ($request->has('invoice_no') && !empty($request->invoice_no)) {
            $query->where('invoice_no', $request->invoice_no);
        }

        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        // Clone for totals
        $totalQuery = clone $query;

        // =========================
        // Paginated Sales (Eager Loaded)
        // =========================
        $sales = $query->with([
            'customer:id,name',
            'user:id,name',
            'payments:id,sale_id,method,amount'
        ])
        ->latest();

        // =========================
        // Overall Totals
        // =========================
        $totalSales = $totalQuery->sum('total');
        $totalOrders = $totalQuery->count();

        // =========================
        // Payment Totals (JOIN + SAME FILTER)
        // =========================
        $paymentTotals = SalePayment::join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->where('sales.account_id', $accountId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->when($staffId, fn($q) => $q->where('sales.user_id', $staffId))
            ->selectRaw('sale_payments.method, SUM(sale_payments.amount) as total')
            ->groupBy('sale_payments.method')
            ->pluck('total', 'method');

        $cashTotal = $paymentTotals['cash'] ?? 0;
        $cardTotal = $paymentTotals['card'] ?? 0;
        $transferTotal = $paymentTotals['transfer'] ?? 0;

        // =========================
        // Staff Dropdown
        // =========================
        $staffs = User::where('account_id', $accountId)
            ->visibleToUser()
            ->pluck('name', 'id');
        if ($request->has('pdf')) {
            $pdfHeaderdata = \Config::get('constants.dailySalespdf');
            $pdfSales = $sales->get();
            $pdf = PDF::loadView('backend.pdf.reports.dailySalespdf', compact('pdfSales', 'pdfHeaderdata', 'totalSales', 'totalOrders', 'staffs', 'staffId', 'breadcrumb', 'cashTotal', 'cardTotal', 'transferTotal' ));
            $pdf = Settings::downloadlandscapepdf($pdf);
            $fileName = $pdfHeaderdata['filename'] .'-'. date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        if ($request->has('csv')) {
            $pdfHeaderdata = \Config::get('constants.dailySalespdf');
            $salesList = $sales->get(); // full data (no pagination)

            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';

            $data = [];
            $ii = 0;

            // ✅ Header Row
            $data[$ii++] = [
                '#',
                __('translation.invoice_no'),
                __('translation.customer_name'),
                __('translation.payment_type'),
                __('translation.currency').'  '.__('translation.cash'),
                __('translation.currency').'  '.__('translation.card'),
                __('translation.currency').'  '.__('translation.transfer'),
                __('translation.currency').'  '.__('translation.total_amount'),
                __('translation.transaction_date'),
            ];

            if (!empty($salesList) && count($salesList) > 0) {

                foreach ($salesList as $i => $sale) {

                    // ✅ Payment Summary (optimized)
                    $summary = $sale->payments
                        ->groupBy('method')
                        ->map(fn($items) => $items->sum('amount'));

                    $data[$ii++] = [
                        $i + 1,
                        $sale->invoice_no ?? '-',
                        $sale->customer->name ?? '-',
                        $sale->payment_method == null ? 'Partial Payment' : 'Full Payment',

                        $summary['cash'] ?? 0,
                        $summary['card'] ?? 0,
                        $summary['transfer'] ?? 0,

                        $sale->total ?? 0,

                        // Prevent Excel auto-format
                        !empty($sale->created_at) ? "\t" . $sale->created_at : '-',
                    ];
                }

                // ✅ Add Totals Row
                $data[$ii++] = [
                    '',
                    '',
                    '',
                    'TOTAL',
                    __('translation.currency').'  '.$cashTotal,
                    __('translation.currency').'  '.$cardTotal,
                    __('translation.currency').'  '.$transferTotal,
                    __('translation.currency').'  '.$totalSales,
                    ''
                ];

            } else {
                $data[$ii++] = [__('translation.no_data_found')];
            }

            return Settings::downloadcsvfile($data, $fileName);
        }
        $sales = $sales->paginate(config('pagination'));
        return view('backend.admin.reports.daily_sales', compact(
            'sales',
            'totalSales',
            'totalOrders',
            'staffs',
            'staffId',
            'breadcrumb',
            'cashTotal',
            'cardTotal',
            'transferTotal'
        ));
    }

    public function dailySalesPdf(Request $request){
        $request->merge(['pdf' => 1]);
        return $this->dailySales($request);
    }
    public function dailySalesCsv(Request $request){
        $request->merge(['csv' => 1]);
        return $this->dailySales($request);
    }
}
