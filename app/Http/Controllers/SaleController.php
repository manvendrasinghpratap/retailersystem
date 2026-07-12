<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use App\Models\Customer;
use App\Mail\CustomerInvoiceMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Store;
use App\Models\PaymentType;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentMethod;
class SaleController extends Controller
{

    protected $breadcrumbBilling;
    protected $breadcrumShow;

    /**
     * @method __construct
     * @description Constructor
     * @access public
     */
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


    /**
     * @method index
     * @description Index
     * @param $request
     * @return mixed
     * @access public
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbBilling;
        $paymentTypes = PaymentType::getSelectable();
        $query = Sale::with('user', 'payments')->visibleToUser()->ofAccount();

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
        return view('backend.sales.index', compact('sales', 'breadcrumb', 'paymentTypes'));
    }

    /**
     * @method paymentDetails
     * @description Payment Details
     * @param $saleId
     * @return mixed
     * @access public
     */
    public function paymentDetails($saleId)
    {
        $sale = Sale::with([
            'customer',
            'payments' => function ($query) {
                $query->with('paymentReceivedBy')
                    ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($saleId);
        $sale->formatted_due_date = $sale->due_date
            ? \App\Helpers\Settings::getFormattedDate($sale->due_date)
            : '-';
        $sale->formatted_created_at = $sale->created_at
            ? \App\Helpers\Settings::getFormattedDate($sale->created_at)
            : '-';
        return response()->json([
            'sale' => $sale,
            'payment_methods' => PaymentMethod::getSelectable(),
        ]);
    }

    /**
     * @method saveCreditPayment
     * @description Save Credit Payment
     * @param $request
     * @return mixed
     * @access public
     */
    public function saveCreditPayment(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
        ]);
        $sale = Sale::findOrFail($request->sale_id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        if (!$paymentMethod) {
            return response()->json([
                'status' => 'error',
                'message' => __('translation.invalid_payment_method')
            ]);
        }
        if ($request->amount > $sale->balance_amount) {
            return response()->json([
                'status' => 'error',
                'message' => __('translation.payment_amount_cannot_exceed_pending_balance')
            ]);
        }
        $existingPayment = SalePayment::where('sale_id', $sale->id)
            ->where('method', $paymentMethod->short_name)
            ->where('amount', $request->amount)
            ->where('payment_received_by', auth()->id())
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($existingPayment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Duplicate payment request detected.'
            ]);
        }


        DB::transaction(function () use ($sale, $request, $paymentMethod) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'method' => $paymentMethod->short_name,
                'amount' => $request->amount,
                'payment_received_by' => auth()->id(),
            ]);
            $sale->paid_amount += $request->amount;
            $sale->balance_amount = $sale->payable_amount - $sale->paid_amount;
            if ($sale->balance_amount <= 0) {
                $sale->balance_amount = 0;
                $sale->payment_status = 'paid';
            } elseif ($sale->paid_amount > 0) {
                $sale->payment_status = 'partial';
            }
            $sale->save();
        });
        return response()->json([
            'status' => 'success',
            'message' => __('translation.payment_received_successfully')
        ]);
    }

    /**
     * Export PDF
     *
     * @param Request $request
     */
    /**
     * @method exportPdf
     * @description Export Pdf
     * @param $request
     * @return mixed
     * @access public
     */
    public function exportPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->index($request);
    }

    /**
     * @method exportCsv
     * @description Export Csv
     * @param $request
     * @return mixed
     * @access public
     */
    public function exportCsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->index($request);
    }

    /**
     * @method show
     * @description Show
     * @param $id
     * @return mixed
     * @access public
     */

    public function show($sale)
    {
        $paymentTypes = PaymentType::getSelectable();
        $paymentMethods = PaymentMethod::getSelectable();
        $breadcrumb = $this->breadcrumShow;
        $saleDecodeId = Settings::getDecodeCodeWithHashids($sale);
        $saleId = $saleDecodeId[0];
        $sale = Sale::with(['customer', 'user', 'creditDuration', 'items.product', 'payments.paymentMethod', 'payments.paymentReceivedBy'])->findOrFail($saleId);
        return view('backend.sales.show', compact('sale', 'breadcrumb', 'paymentTypes', 'paymentMethods'));
    }

    /**
     * @method payment
     * @description Payment
     * @param $id
     * @return mixed
     * @access public
     */

    public function payment(Sale $sale)
    {
        $breadcrumb = $this->breadcrumShow;
        $sale->load('items.product', 'user', 'payments');

        return view('backend.sales.payment', compact('sale', 'breadcrumb'));
    }

    /**
     * @method printinvoice
     * @description Print Invoice
     * @param $id
     * @return mixed
     * @access public
     */

    public function printinvoice($id)
    {
        $id = Settings::getDecodeCodeWithHashids($id);
        try {
            $storeDetails = Store::where('account_id', auth()->user()->account_id)->first();
            if (empty($id)) {
                return redirect()->route('admin.sales.index')->with('error', 'Invalid sale ID');
            }
            $id = $id[0];
            $sale = Sale::find($id);
            $sale->load('items.product', 'user', 'payments');
            return view('backend.sales.receipt', compact("sale", 'storeDetails'));
        } catch (\Exception $e) {
            return redirect()->route('admin.sales.index')->with('error', 'Invalid sale ID');
        }

    }

    /**
     * @method sendInvoiceEmail
     * @description Send Invoice Email
     * @param $id
     * @return mixed
     * @access public
     */

    public function sendInvoiceEmail(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:sales,id',
        ]);
        try {
            $sale = Sale::with('items.product')->findOrFail($request->sale_id);
            $customer = Customer::find($sale->customer_id);
            if (!$customer || empty($customer->email)) {
                return response()->json(['success' => false, 'message' => 'Customer email not available'], 400);
            }
            Mail::to($customer->email)->send(new CustomerInvoiceMail($sale, $customer));
            return response()->json(['success' => true, 'message' => 'Invoice email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * @method downloadInvoice
     * @description Download Invoice
     * @param $id
     * @return mixed
     * @access public
     */

    public function downloadInvoice($id)
    {
        $id = Settings::getDecodeCodeWithHashids($id);
        try {
            if (empty($id)) {
                return redirect()->route('admin.sales.index')->with('error', 'Invalid Sale ID');
            }
            $id = $id[0];
            $pdfHeaderdata = config('constants.downloadinvoicpdf');
            $sale = Sale::with(['customer', 'items.product', 'payments', 'user', 'warehouse', 'creditDuration', 'store'])->findOrFail($id);
            //$this->pr($sale->store);
            $pdf = Pdf::loadView('backend.pdf.invoice', compact('sale'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . $sale->invoice_no . '.pdf';
            return $pdf->stream($fileName);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(),], 500);
        }
    }

    /**
     * @method downloadPurchaseInvoice
     * @description Download Purchase Invoice
     * @param $id
     * @return mixed
     * @access public
     */

    public function downloadPurchaseInvoice($id)
    {
        $id = Settings::getDecodeCodeWithHashids($id);
        try {
            if (empty($id)) {
                return redirect()->route('admin.sales.index')->with('error', 'Invalid Sale ID');
            }
            $id = $id[0];
            $storeDetails = Store::where('account_id', auth()->user()->account_id)->first();
            $pdfHeaderdata = config('constants.downloadinvoicpdf');
            $purchase = Sale::with(['customer', 'items.product', 'payments', 'user', 'warehouse', 'creditDuration'])->findOrFail($id);
            $pdf = Pdf::loadView('backend.pdf.purchase_invoice', compact('purchase', 'storeDetails'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . ($purchase->invoice_no ?? $purchase->id) . '.pdf';
            return $pdf->stream($fileName);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}