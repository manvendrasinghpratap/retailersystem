<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use App\Models\Customer;
use App\Mail\CustomerInvoiceMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
class SaleController extends Controller
{

    protected $breadcrumbBilling;
    protected $breadcrumShow;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbBilling = [
            'title' => __('translation.sales'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'billing.index',
                    'title' => __('translation.billing')
                ],
                [
                    'route' => 'admin.sales.index',
                    'title' => __('translation.sales_list')
                ]
            ],
            'route1' => "billing.index",
            'route1Title' => __('translation.billing'),
            'route2Title' => __('translation.sales_list'),
            'route2' => 'admin.sales.index',
            'reset_route' => 'admin.sales.index',
            'reset_route_title' => __('translation.cancel'),
        ];

        $this->breadcrumShow = [
            'title' => __('translation.invoice'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'billing.index',
                    'title' => __('translation.billing')
                ],
                [
                    'route' => 'admin.sales.index',
                    'title' => __('translation.sales_list')
                ],
                [
                    'route' => 'admin.sales.show',
                    'title' => __('translation.invoice')
                ],
                [
                    'route' => 'admin.sales.payment',
                    'title' => __('translation.payment_details')
                ]
            ],
            'route1' => "admin.sales.index",
            'route1Title' => __('translation.sales_list'),
            'route2Title' => __('translation.invoice'),
            'route2' => 'admin.sales.show',
            'route3Title' => __('translation.payment_details'),
            'route3' => 'admin.sales.payment',
            'reset_route' => 'admin.sales.index',
            'reset_route_title' => __('translation.cancel'),
            'route4' => 'billing.index',
            'route4Title' => __('translation.billing'),

        ];
    }


    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbBilling;
        $query = Sale::with('user', 'payments')->visibleToUser();

        // Filter by date
        $query = Settings::applyDateRange($query, $request, 'created_at', true);
        // Filter by invoice
        if ($request->invoice_no) {
            $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
        }
        $sales = $query->latest();

        // Summary
        $totalSales = $sales->sum('total');
        if ($request->pdf) {
            $sales = $sales->get();
            $pdfHeaderdata = \Config::get('constants.downloadsalespdf');
            $pdf = Pdf::loadView('backend.pdf.sales.salesListpdf', compact('sales', 'pdfHeaderdata'));
            $pdf = Settings::downloadLandscapepdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $sales = $sales->get();
            $csvHeaderdata = \Config::get('constants.downloadsalespdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.customer_name'),
                __('translation.customer_phone'),
                __('translation.customer_email'),
                __('translation.invoice_no'),
                __('translation.cashier'),
                __('translation.payment_type'),
                __('translation.payment_method'),
                __('translation.b_ngn') . ' ' . __('translation.total_amount'),
                __('translation.transaction_date'),
            ];

            foreach ($sales as $sale) {
                $data[++$ii] = [
                    $ii,
                    $sale->customer->name ?? '-',
                    $sale->customer->phone ?? '-',
                    $sale->customer->email ?? '-',
                    $sale->invoice_no,
                    $sale->user->name ?? '-',
                    ($sale->payment_method == null) ? 'Partial Payment' : 'Full Payment',
                    $sale->payment_methods,
                    __('translation.b_ngn') . ' ' . number_format($sale->total, 2),
                    Settings::getFormattedDatetime($sale->created_at),
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $sales = $sales->paginate(config('constants.pagination'));
        return view('backend.sales.index', compact('sales', 'breadcrumb'));
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

    public function show(Sale $sale)
    {
        $breadcrumb = $this->breadcrumShow;
        $sale->load('items.product', 'user', 'payments');

        return view('backend.sales.show', compact('sale', 'breadcrumb'));
    }

    public function payment(Sale $sale)
    {
        $breadcrumb = $this->breadcrumShow;
        $sale->load('items.product', 'user', 'payments');

        return view('backend.sales.payment', compact('sale', 'breadcrumb'));
    }

    public function printinvoice($id)
    {
        $id = Settings::getDecodeCodeWithHashids($id);
        try {
            if (empty($id)) {
                return redirect()->route('admin.sales.index')->with('error', 'Invalid sale ID');
            }
            $id = $id[0];
            $sale = Sale::find($id);
            $sale->load('items.product', 'user', 'payments');
            return view('backend.sales.receipt', compact("sale"));
        } catch (\Exception $e) {
            return redirect()->route('admin.sales.index')->with('error', 'Invalid sale ID');
        }

    }



    public function sendInvoiceEmail(Request $request)
    {

        $request->validate([
            'sale_id' => 'required|integer|exists:sales,id',
        ]);

        try {

            $sale = Sale::with('items.product')->findOrFail($request->sale_id);
            $customer = Customer::find($sale->customer_id);

            // ✅ Check customer + email
            if (!$customer || empty($customer->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not available'
                ], 400);
            }

            // ✅ Send email
            Mail::to($customer->email)->send(new CustomerInvoiceMail($sale, $customer));
            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadInvoice($id)
    {
        $id = Settings::getDecodeCodeWithHashids($id);
        try {
            if (empty($id)) {
                return redirect()->route('admin.sales.index')->with('error', 'Invalid sale ID');
            }
            $id = $id[0];
            $pdfHeaderdata = \Config::get('constants.downloadinvoicpdf');
            $sale = Sale::with(['items.product', 'customer'])->findOrFail($id);
            $customer = Customer::find($sale->customer_id);
            $pdf = Pdf::loadView('backend.pdf.invoice', compact('sale', 'customer'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . $sale->invoice_no . '.pdf';
            return $pdf->stream($fileName);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}