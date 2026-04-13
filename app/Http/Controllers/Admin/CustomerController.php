<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Helpers\Settings;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.customers'),
            'route1' => "admin.customers.create",
            'route1Title' => __('translation.add_new_customer'),
            'route2Title' => __('translation.add_new_customer'),
            'route2' => 'admin.customers.index',
            'reset_route' => 'admin.customers.index',
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

    /**
     * Customer Listing
     */
    public function index()
    {
        $breadcrumb = $this->breadcrumbAddNew;

        $customers = Customer::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0);

        // Search filter
        if (request('name')) {
            $customers->where('name', 'LIKE', '%' . trim(request('name')) . '%');
        }

        if (request('phone')) {
            $customers->where('phone', 'LIKE', '%' . trim(request('phone')) . '%');
        }

        if (request('status') !== null) {
            $customers->where('status', request('status'));
        }

        $customers = $customers->orderBy('id', 'desc')->paginate(config('constants.pagination'));
        $status = config('constants.accountstatus');

        return view('backend.admin.customers.index', compact('customers', 'breadcrumb', 'status'));
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('backend.admin.customers.form', [
            'breadcrumb' => $this->breadcrumbListing
        ]);
    }

    /**
     * Store Customer
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
     * Edit Customer
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
     * Update Customer
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
     * Soft Delete
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
     * Status Update (AJAX)
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
     * Hard Delete (Optional)
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