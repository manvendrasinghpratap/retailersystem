<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\Countries;
use App\Models\State;
use App\Models\LocalGovernment;

class StoreController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.stores'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.stores.index',
                    'title' => __('translation.stores')
                ],
                [
                    'route' => 'admin.stores.create',
                    'title' => __('translation.add_store')
                ]
            ],
            'route1' => 'admin.stores.create',
            'route1Title' => __('translation.add_store'),
            'route2Title' => __('translation.add_store'),
            'route2' => 'admin.stores.index',
            'reset_route' => 'admin.stores.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.stores'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.stores.index',
                    'title' => __('translation.stores')
                ],
                [
                    'route' => 'admin.stores.create',
                    'title' => __('translation.add_store')
                ]
            ],
            'route1' => 'admin.stores.index',
            'route1Title' => __('translation.stores'),
            'route2Title' => __('translation.add_store'),
            'route2' => 'admin.stores.create',
            'route3Title' => __('translation.update_store'),
            'route3' => 'admin.stores.edit',
            'reset_route' => 'admin.stores.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /*
    @store list
    @description show store list
    @param $request
    @return $response
    @author Manvendra Pratap Singh
    @date 2026-06-30
    @modified 
    */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $stores = Store::ofAccount()->latest()->active();

        if ($request->name) {
            $stores->where('name', 'LIKE', '%' . trim($request->name) . '%');
        }

        if ($request->phone) {
            $stores->where('phone', 'LIKE', '%' . trim($request->phone) . '%');
        }

        if ($request->status !== null) {
            $stores->where('status', $request->status);
        }

        if ($request->has('pdf')) {
            $stores = $stores->get();
            $pdf = PDF::loadView('backend.pdf.store.list', compact('stores', 'breadcrumb'));
            return Settings::downloadpdf($pdf)->stream('stores-' . date('Y-m-d') . '.pdf');
        }
        if ($request->has('csv')) {
            $stores = $stores->get();
            $data[] = ['#', 'Store', 'Code', 'Phone', 'City', 'Status'];
            foreach ($stores as $key => $store) {
                $data[] = [
                    $key + 1,
                    $store->name,
                    $store->code,
                    $store->phone,
                    $store->city,
                    $store->status ? 'Active' : 'Inactive'
                ];
            }
            return Settings::downloadcsvfile(
                $data,
                'stores-' . date('Y-m-d') . '.csv'
            );
        }

        $stores = $stores->paginate(config('constants.pagination'));
        return view(
            'backend.admin.store.index',
            compact('stores', 'breadcrumb')
        );
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

    public function create()
    {
        $managers = User::ofAccount()->orderBy('name')->pluck('name', 'id')->toArray();
        $states = State::getList();
        $localGovernments = LocalGovernment::getList();
        $countries = Countries::getList();
        return view(
            'backend.admin.store.form',
            [
                'breadcrumb' => $this->breadcrumbListing,
                'managers' => $managers,
                'countries' => $countries,
                'states' => $states,
                'localGovernments' => $localGovernments,
            ]
        );
    }

    /**
     * Save
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|max:255',
                'phone' => 'required|max:20|unique:stores,phone',
                'address' => 'required',
                'local_government' => 'required',
                'state' => 'required',
                'country' => 'required',
                'manager_id' => 'required',
                'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            $lastStore = Store::latest('id')->first();
            $nextNumber = $lastStore ? ($lastStore->id + 1) : 1;
            $storeCode = 'STR' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            $storeLogo = Settings::imageToBase64($request->file('logo'));

            Store::create([
                'account_id' => auth()->user()->account_id,
                'name' => $request->name,
                'code' => $request->filled('code') ? $request->code : $storeCode,
                'email' => $request->filled('email') ? $request->email : '',
                'phone' => $request->filled('phone') ? $request->phone : '',
                'alternate_phone' => $request->filled('alternate_phone') ? $request->alternate_phone : '',
                'gst_number' => $request->filled('gst_number') ? $request->gst_number : '',
                'address' => $request->filled('address') ? $request->address : '',
                'city' => $request->filled('local_government') ? $request->local_government : null,
                'state' => $request->filled('state') ? $request->state : null,
                'country' => $request->filled('country') ? $request->country : null,
                'pincode' => $request->filled('pincode') ? $request->pincode : null,
                'manager_id' => $request->filled('manager_id') ? $request->manager_id : null,
                'status' => $request->status ?? 1,
                'logo' => $storeLogo,
                'created_by' => auth()->id(),
            ]);

            return Settings::roleRedirect('stores.index', __('translation.store_added_successfully'));
        } catch (\Exception $e) {

            return Settings::roleRedirect(
                'stores.index',
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute(
            $this->breadcrumbListing,
            ['route3', 'route3Title'],
            ['admin.stores.update', __('translation.update_store')]
        );

        $id = Settings::getDecodeCode($id);
        $managers = User::ofAccount()->orderBy('name')->pluck('name', 'id')->toArray();
        $states = State::getList();
        $localGovernments = LocalGovernment::getList();
        $countries = Countries::getList();
        $store = Store::ofAccount()->findOrFail($id);

        return view(
            'backend.admin.store.form',
            compact('store', 'breadcrumb', 'managers', 'states', 'localGovernments', 'countries')
        );
    }

    /**
     * Update
     */
    public function update(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->store_id);

            $store = Store::ofAccount()->findOrFail($id);

            $request->validate([
                'name' => 'required|max:255',
                'phone' => 'required|max:20|unique:stores,phone,' . $id,
                'address' => 'required',
                'local_government' => 'required',
                'state' => 'required',
                'country' => 'required',
                'manager_id' => 'required',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email ?? '',
                'phone' => $request->phone,
                'alternate_phone' => $request->alternate_phone ?? '',
                'gst_number' => $request->gst_number ?? '',
                'address' => $request->address,
                'city' => $request->local_government,
                'state' => $request->state,
                'country' => $request->country,
                'pincode' => $request->pincode,
                'manager_id' => $request->manager_id,
                'status' => $request->status ?? 1,
                'updated_by' => auth()->id(),
            ];

            // Update logo only if a new one is uploaded
            if ($request->hasFile('logo')) {
                $data['logo'] = Settings::imageToBase64($request->file('logo'));
            }

            $store->update($data);

            return Settings::roleRedirect(
                'stores.index',
                __('translation.store_updated_successfully')
            );

        } catch (\Exception $e) {

            return Settings::roleRedirect(
                'stores.index',
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Soft Delete
     */
    public function softdelete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);
            $deleted = Store::ofAccount()->where('id', $id)->update(['is_deleted' => 1]);
            return Settings::roleRedirect('stores.index', __('translation.store_deleted_successfully'));
        } catch (\Exception $e) {
            return Settings::roleRedirect('stores.index', $e->getMessage(), 'error');
        }
    }

    /**
     * Status Update
     */
    public function statusUpdate(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            $updated = Store::where(
                'account_id',
                auth()->user()->account_id
            )
                ->where('id', $id)
                ->update([
                    'status' => $request->status
                ]);

            return response()->json([
                'success' => $updated,
                'message' => 'Status Updated'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}