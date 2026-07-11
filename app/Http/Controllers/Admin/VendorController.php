<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Models\VendorLedger;
use App\Models\Type;
use App\Helpers\Settings;
use App\Models\State;
use App\Models\LocalGovernment;
use App\Models\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.vendors'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.vendors.index',
                    'title' => __('translation.vendors')
                ],
                [
                    'route' => 'admin.vendors.create',
                    'title' => __('translation.add_new_vendor')
                ]
            ],
            'route1' => 'admin.vendors.create',
            'route1Title' => __('translation.add_vendor'),
            'route2' => 'admin.vendors.index',
            'route2Title' => __('translation.vendor_list'),
            'reset_route' => 'admin.vendors.index',
            'reset_route_title' => __('translation.cancel'),
            'route3Title' => __('translation.update_vendor'),
        ];

        $this->breadcrumbListing = $this->breadcrumbAddNew;
    }

    /*
    |--------------------------------------------------------------------------
    | Vendor List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbListing;

        $vendors = Vendor::ofAccount();

        if ($request->name) {
            $vendors->where('name', 'like', '%' . trim($request->name) . '%');
        }

        if ($request->phone) {
            $vendors->where('phone', 'like', '%' . trim($request->phone) . '%');
        }

        if ($request->status !== '' && $request->status !== null) {
            $vendors->where('status', $request->status);
        }

        $vendors = $vendors->latest()->paginate(config('constants.pagination'));

        return view('backend.admin.vendor.index', compact(
            'vendors',
            'breadcrumb'
        ));
    }

    public function exportPdf(Request $request)
    {
        $request->merge([
            'pdf' => 1,
            'account_id' => auth()->user()->account_id,
        ]);
        return $this->index($request);
    }

    public function exportExcel(Request $request)
    {
        $request->merge([
            'excel' => 1,
            'account_id' => auth()->user()->account_id,
        ]);
        return $this->index($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Vendor Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();
        return view('backend.admin.vendor.form', ['breadcrumb' => $this->breadcrumbAddNew, 'state' => $state, 'localGovernment' => $localGovernment, 'countries' => $countries]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Vendor
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:150',
            'phone' => 'required|max:30',
            'email' => 'nullable|email|max:150',
        ]);

        $openingBalance = $request->opening_balance ?? 0;

        DB::transaction(function () use ($request, $openingBalance) {

            $vendor = Vendor::create([
                'account_id' => auth()->user()->account_id,
                'vendor_code' => 'VEN' . rand(10000, 99999),
                'name' => $request->name,
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'lga_id' => $request->lga_id,
                'state_id' => $request->state_id,
                'country_id' => $request->country_id,
                'opening_balance' => $openingBalance,
                'current_balance' => $openingBalance,
                'status' => $request->status ?? 1,
                'created_by' => auth()->id(),
            ]);

            // Opening balance ledger
            if ($openingBalance > 0) {
                VendorLedger::create([
                    'account_id' => auth()->user()->account_id,
                    'vendor_id' => $vendor->id,
                    'type' => 4,  //// coming from types table 4 means vendor opening balance
                    'debit' => $openingBalance,
                    'credit' => 0,
                    'balance' => $openingBalance,
                    'remarks' => 'Opening Balance'
                ]);
            }
        });

        return Settings::roleRedirect(
            'vendors.index',
            'Vendor Added Successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Vendor
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();
        $vendor = Vendor::ofAccount()->findOrFail($id);
        return view('backend.admin.vendor.form', [
            'vendor' => $vendor,
            'breadcrumb' => $this->breadcrumbListing,
            'state' => $state,
            'localGovernment' => $localGovernment,
            'countries' => $countries
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Vendor
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $id = Settings::getDecodeCode($request->vendor_id);
        $vendor = Vendor::ofAccount()->findOrFail($id);
        $request->validate([
            'name' => 'required|max:150',
        ]);

        $vendor->update([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'lga_id' => $request->lga_id,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
            'status' => $request->status ?? 1,
            'updated_by' => auth()->id(),
        ]);

        return Settings::roleRedirect(
            'vendors.index',
            'Vendor Updated Successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Vendor Payment Form
    |--------------------------------------------------------------------------
    */

    public function paymentForm($id)
    {
        $breadcrumb['title'] = __('translation.vendor_payment');
        $id = Settings::getDecodeCode($id);

        $vendor = Vendor::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.vendor.payment', compact('vendor', 'breadcrumb'));
    }

    /*
    |--------------------------------------------------------------------------
    | Save Vendor Payment
    |--------------------------------------------------------------------------
    */

    public function paymentStore(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'payment_date' => 'required',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required'
        ]);

        $id = Settings::getDecodeCode($request->vendor_id);

        $vendor = Vendor::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        DB::transaction(function () use ($request, $vendor) {

            $payment = VendorPayment::create([
                'account_id' => auth()->user()->account_id,
                'vendor_id' => $vendor->id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_no' => $request->reference_no,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);

            // Reduce current balance
            $vendor->decrement('current_balance', $request->amount);

            $vendor->refresh();

            VendorLedger::create([
                'account_id' => auth()->user()->account_id,
                'vendor_id' => $vendor->id,
                'type' => 1,  /// Payment 1, coming drom types table
                'reference_id' => $payment->id,
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => $vendor->current_balance,
                'remarks' => 'Vendor Payment'
            ]);
        });

        return Settings::roleRedirect(
            'vendors.index',
            'Vendor Payment Added Successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Vendor Ledger
    |--------------------------------------------------------------------------
    */

    public function ledger($id)
    {
        $breadcrumb = $this->breadcrumbListing;
        $breadcrumb['title'] = __('translation.vendor_ledger');
        $breadcrumb['breadcrumb'][] = [
            'route' => 'admin.vendors.ledger',
            'params' => ['id' => $id],
            'title' => __('translation.ledger')
        ];

        $id = Settings::getDecodeCode($id);

        $vendor = Vendor::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $types = Type::active()->pluck('name', 'id')->toArray();
        $ledgers = VendorLedger::where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(config('constants.pagination'));
        return view('backend.admin.vendor.ledger', compact(
            'vendor',
            'ledgers',
            'breadcrumb',
            'types'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Soft Delete
    |--------------------------------------------------------------------------
    */

    public function softdelete(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);

        $deleted = Vendor::where('account_id', auth()->user()->account_id)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'success' => $deleted ? true : false
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Update
    |--------------------------------------------------------------------------
    */

    public function statusUpdate(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);

        $updated = Vendor::where('account_id', auth()->user()->account_id)
            ->where('id', $id)
            ->update([
                'status' => $request->status
            ]);

        return response()->json([
            'success' => $updated ? true : false
        ]);
    }
}