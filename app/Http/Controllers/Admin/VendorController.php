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
use PDF;

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
    @author Manvendra Pratap Singh
    @description This function is used to display the list of vendors
    @function index
    */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbListing;
        $vendors = Vendor::ofAccount();
        if ($request->vendor_name) {
            $vendors->where('name', 'like', '%' . trim($request->vendor_name) . '%');
        }

        if ($request->company_name) {
            $vendors->where('company_name', 'like', '%' . trim($request->company_name) . '%');
        }
        if ($request->phone) {
            $vendors->where('phone', 'like', '%' . trim($request->phone) . '%');
        }
        if ($request->status !== '' && $request->status !== null) {
            $vendors->where('status', $request->status);
        }
        $vendors = $vendors->latest();

        if ($request->has('pdf')) {
            $vendors = $vendors->get();
            $pdfHeaderdata = \Config::get('constants.vendorListpdf');
            $pdf = PDF::loadView('backend.pdf.vendors.vendorListpdf', compact('vendors', 'pdfHeaderdata', 'breadcrumb'));
            $pdf = Settings::downloadLandscapepdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);

        } elseif ($request->has('csv')) {
            $vendors = $vendors->get();
            $csvHeaderdata = \Config::get('constants.vendorListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.vendor_code'),
                __('translation.company_name'),
                __('translation.vendor_name'),
                __('translation.phone'),
                __('translation.email'),
                __('translation.currency') . ' ' . __('translation.opening_balance'),
                __('translation.currency') . ' ' . __('translation.current_balance'),
                __('translation.status'),
                __('translation.createdat'),
            ];

            foreach ($vendors as $vendor) {
                $data[++$ii] = [
                    $ii,
                    $vendor->vendor_code,
                    $vendor->company_name,
                    $vendor->name,
                    ' ' . $vendor->phone,
                    ' ' . $vendor->email,
                    __('translation.currency') . ' ' . Settings::getcustomnumberformat($vendor->opening_balance),
                    __('translation.currency') . ' ' . Settings::getcustomnumberformat($vendor->current_balance),
                    ($vendor->status == 1) ? __('translation.active') : __('translation.inactive'),
                    Settings::getFormattedDatetime($vendor->created_at),
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $vendors = $vendors->paginate(account_setting('general.pagination'));
        return view('backend.admin.vendor.index', compact('vendors', 'breadcrumb'));
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to export the list of vendors to PDF
    @function exportPdf
    */
    public function exportPdf(Request $request)
    {
        $request->merge([
            'pdf' => 1,
            'account_id' => auth()->user()->account_id,
        ]);
        return $this->index($request);
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to export the list of vendors to Excel
    @function exportExcel
    */
    public function exportExcel(Request $request)
    {
        $request->merge([
            'csv' => 1,
            'account_id' => auth()->user()->account_id,
        ]);
        return $this->index($request);
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to display the form for creating a new vendor
    @function create
    */
    public function create()
    {
        $state = State::getList();
        $localGovernment = LocalGovernment::getList();
        $countries = Countries::getList();
        return view('backend.admin.vendor.form', ['breadcrumb' => $this->breadcrumbAddNew, 'state' => $state, 'localGovernment' => $localGovernment, 'countries' => $countries]);
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to store a new vendor
    @function store
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
                'website' => $request->website,
                'phone' => $request->phone,
                'whatsapp_number' => $request->whatsapp_number,
                'email' => $request->email,
                'address' => $request->address,
                'lga_id' => $request->lga_id,
                'state_id' => $request->state_id,
                'country_id' => $request->country_id,
                'opening_balance' => $openingBalance,
                'current_balance' => $openingBalance,
                'status' => $request->status ?? 1,
                'comment' => $request->comment,
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
            'Supplier Added Successfully.'
        );
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to edit a vendor
    @function edit
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
    @author Manvendra Pratap Singh
    @description This function is used to update a vendor
    @function update
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
            'website' => $request->website,
            'phone' => $request->phone,
            'whatsapp_number' => $request->whatsapp_number,
            'email' => $request->email,
            'address' => $request->address,
            'lga_id' => $request->lga_id,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
            'status' => $request->status ?? 1,
            'comment' => $request->comment,
            'updated_by' => auth()->id(),
        ]);

        return Settings::roleRedirect(
            'vendors.index',
            'Supplier Details Updated Successfully.'
        );
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to display the form for vendor payment
    @function paymentForm
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
    @author Manvendra Pratap Singh
    @description This function is used to save vendor payment
    @function paymentStore
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
    @author Manvendra Pratap Singh
    @description This function is used to display the vendor ledger
    @function ledger
    */
    public function ledger($id, $type = null)
    {
        $breadcrumb = $this->breadcrumbListing;
        $breadcrumb['title'] = __('translation.vendor_ledger');
        $breadcrumb['breadcrumb'][] = [
            'route' => 'admin.vendors.ledger',
            'params' => ['id' => $id],
            'title' => __('translation.ledger')
        ];

        $id = Settings::getDecodeCode($id);
        try {
            $vendor = Vendor::where('account_id', auth()->user()->account_id)->findOrFail($id);
            $types = Type::active()->pluck('name', 'id')->toArray();
            $ledgers = VendorLedger::where('vendor_id', $vendor->id)->latest();
            if ($type == 'pdf') {
                $ledgers = $ledgers->get();
                $pdfHeaderdata = \Config::get('constants.vendorLedgerListpdf');
                $pdf = PDF::loadView('backend.pdf.vendors.vendorLedgerListpdf', compact('ledgers', 'pdfHeaderdata', 'breadcrumb', 'types', 'vendor'));
                $pdf = Settings::downloadpdf($pdf);
                $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
                return $pdf->stream($fileName);
            }
            if ($type == 'csv') {
                $ledgers = $ledgers->get();
                $csvHeaderdata = \Config::get('constants.vendorLedgerListpdf');
                $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
                $data = [];
                $ii = $i = $y = 0;

                $data[$ii] = [
                    __('translation.company_name'),
                    __('translation.name'),
                    __('translation.phone'),
                    __('translation.currency') . ' ' . __('translation.current_balance'),
                ];
                $data[++$ii] = [
                    $vendor->company_name,
                    $vendor->name,
                    ' ' . $vendor->phone,
                    __('translation.currency') . ' ' . Settings::getcustomnumberformat($vendor->current_balance),
                ];
                $data[++$ii] = [''];
                $data[++$ii] = [''];
                ++$ii;
                $data[++$ii] = [
                    '#',
                    __('translation.date'),
                    __('translation.type'),
                    __('translation.currency') . ' ' . __('translation.debit'),
                    __('translation.currency') . ' ' . __('translation.credit'),
                    __('translation.currency') . ' ' . __('translation.balance'),
                    __('translation.remarks'),
                ];

                foreach ($ledgers as $ledger) {
                    $data[++$ii] = [
                        ++$y,
                        Settings::getFormattedDatetime($ledger->created_at),
                        $types[$ledger->type],
                        __('translation.currency') . ' ' . Settings::getcustomnumberformat($ledger->debit),
                        __('translation.currency') . ' ' . Settings::getcustomnumberformat($ledger->credit),
                        __('translation.currency') . ' ' . Settings::getcustomnumberformat($ledger->balance),
                        $ledger->remarks,
                    ];
                }
                return Settings::downloadcsvfile($data, $fileName);
            }

            $ledgers = $ledgers->paginate(account_setting('general.pagination'));
            return view('backend.admin.vendor.ledger', compact('vendor', 'ledgers', 'breadcrumb', 'types'));
        } catch (e) {
            return redirect()->route('admin.vendors.index');
        }
    }

    public function ledgerExportPdf($id)
    {
        return $this->ledger($id, 'pdf');
    }
    public function ledgerExportCsv($id)
    {
        return $this->ledger($id, 'csv');
    }

    /*
    @author Manvendra Pratap Singh
    @description This function is used to soft delete a vendor
    @function softdelete
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
    @author Manvendra Pratap Singh
    @description This function is used to update the status of a vendor
    @function statusUpdate
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