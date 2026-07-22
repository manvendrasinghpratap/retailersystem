<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbAddNew = [
            'title' => __('translation.customers'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.customers.index',
                    'title' => __('translation.customers')
                ],
                [
                    'route' => 'admin.customers.create',
                    'title' => __('translation.add_new_customer')
                ]
            ],
            'route1' => "admin.customers.create",
            'route1Title' => __('translation.add_new_customer'),
            'route2Title' => __('translation.add_new_customer'),
            'route2' => 'admin.customers.index',
            'reset_route' => 'admin.customers.index',
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
                ],
                [
                    'route' => 'admin.customers.create',
                    'title' => __('translation.add_new_customer')
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
     * @desc function to fetch customer listing
     * @param Request $request
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;

        $customers = Customer::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0);

        // Search filter
        if (request('name')) {
            $customers->where('name', 'LIKE', '%' . trim(request('name')) . '%');
        }

        if (request('phones')) {
            $customers->where('phone', 'LIKE', '%' . trim(request('phones')) . '%');
        }

        if (request('status') !== null) {
            $customers->where('status', request('status'));
        }

        $customers = $customers->orderBy('id', 'desc');
        if ($request->has('pdf')) {
            $customers = $customers->get();
            $pdfHeaderdata = \Config::get('constants.customerListpdf');
            $pdf = PDF::loadView('backend.pdf.customers.customerListpdf', compact('customers', 'pdfHeaderdata', 'breadcrumb'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $customers = $customers->get();
            $csvHeaderdata = \Config::get('constants.customerListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.customer_name'),
                __('translation.phone'),
                __('translation.email'),
                __('translation.wallet_balance'),
                __('translation.status'),
                __('translation.createdat'),
            ];

            foreach ($customers as $customer) {
                $data[++$ii] = [
                    $ii,
                    $customer->name,
                    $customer->phone,
                    $customer->email,
                    $customer->wallet_balance,
                    ($customer->status == 1) ? __('translation.active') : __('translation.inactive'),
                    !empty($customer->created_at) ? "\t" . Settings::getFormattedDatetime($customer->created_at) : '-',
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $customers = $customers->paginate(account_setting('general.pagination'));
        $status = config('constants.accountstatus');
        return view('backend.admin.customers.index', compact('customers', 'breadcrumb', 'status'));
    }


    /**
     * @desc function to export customer list
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function exportPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->index($request);
    }

    /**
     * @desc function to export customer list
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function exportCsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->index($request);
    }

    /**
     * @desc function to create customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function create()
    {
        return view('backend.admin.customers.form', [
            'breadcrumb' => $this->breadcrumbListing
        ]);
    }

    /**
     * @desc function to store customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:customers,phone',
            'email' => 'nullable|email',
            'wallet_balance' => 'nullable|numeric',
            'status' => 'nullable|boolean',
        ]);

        try {

            Customer::create([
                'account_id' => auth()->user()->account_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'wallet_balance' => $request->wallet_balance ?? 0,
                'status' => $request->status ?? 1,
            ]);

            return Settings::roleRedirect('customers.index', 'Customer Added Successfully.');

        } catch (\Exception $e) {

            return Settings::roleRedirect('customers.index', 'Something went wrong!', 'error');
        }
    }

    /**
     * @desc function to edit customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute(
            $this->breadcrumbListing,
            ['route3', 'route3Title'],
            ['customers.update', __('translation.update_customer')]
        );

        $id = Settings::getDecodeCode($id);

        $customer = Customer::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0)
            ->findOrFail($id);

        return view('backend.admin.customers.form', [
            'breadcrumb' => $breadcrumb,
            'customer' => $customer
        ]);
    }

    /**
     * @desc function to update customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function update(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->customer_id);

            $customer = Customer::where('account_id', auth()->user()->account_id)
                ->findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|unique:customers,phone,' . $customer->id,
                'email' => 'nullable|email',
                'wallet_balance' => 'nullable|numeric',
                'status' => 'nullable|boolean',
            ]);

            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'wallet_balance' => $request->wallet_balance ?? 0,
                'status' => $request->status ?? 1,
            ]);

            return Settings::roleRedirect('customers.index', 'Customer Updated Successfully.');

        } catch (\Exception $e) {

            return Settings::roleRedirect('customers.index', 'Something went wrong!', 'error');
        }
    }

    /**
     * @desc function to soft delete customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function softdelete(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->input('id'));

            $deleted = Customer::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['is_deleted' => 1]);

            return response()->json([
                'success' => $deleted ? true : false,
                'message' => $deleted ? 'Deleted successfully' : 'Delete failed'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * @desc function to update customer status
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function statusUpdate(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            $updated = Customer::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => $updated ? true : false,
                'message' => $updated ? 'Status updated' : 'Update failed'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * @desc function to delete customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function destroy($id)
    {
        $id = Settings::getDecodeCode($id);

        $customer = Customer::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer permanently deleted.');
    }

    /**
     * @desc function to find customer by phone
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function findByPhone(Request $request)
    {
        $customer = Customer::where('account_id', auth()->user()->account_id)
            ->where('phone', $request->phone)
            ->first();

        return response()->json([
            'exists' => (bool) $customer,
            'customer' => $customer
        ]);
    }

    /**
     * @desc function to update customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function updateByPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
        ]);

        $customer = Customer::where('phone', $request->phone)->firstOrFail();

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    /**
     * @desc function to store customer
     * @author manvendra <[EMAIL_ADDRESS]>
     * @date 2026-07-23
     */
    public function quickStore(Request $request)
    {
        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'account_id' => auth()->user()->account_id,
            'wallet_balance' => 0
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }
}