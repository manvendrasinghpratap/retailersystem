<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{

    protected $breadcrumbBilling;
    protected $breadcrumShow;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbBilling = [
            'title' => __('translation.sales'),
            'route1' => "billing.index",
            'route1Title' => __('translation.billing'),
            'route2Title' => __('translation.sales_list'),
            'route2' => 'admin.sales.index',
            'reset_route' => 'admin.sales.index',
            'reset_route_title' => __('translation.cancel'),
        ];

        $this->breadcrumShow = [
            'title' => __('translation.invoice'),
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
        $query = Sale::with('user', 'payments');

        // Filter by date
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Filter by invoice
        if ($request->invoice_no) {
            $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
        }

        $sales = $query->latest()->paginate(config('constants.pagination'));

        // Summary
        $totalSales = $sales->sum('total');

        return view('backend.sales.index', compact('sales', 'totalSales', 'breadcrumb'));
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
        // $id = base64_decode($id);
        $sale = Sale::find($id);
        $sale->load('items.product', 'user', 'payments');
        return view('backend.sales.receipt', compact("sale"));
    }
}