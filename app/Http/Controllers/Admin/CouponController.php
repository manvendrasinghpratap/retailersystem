<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
class CouponController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.coupons'),
            'route1' => "admin.coupons.create",
            'route1Title' => __('translation.add_new_coupon'),
            'route2Title' => __('translation.add_new_coupon'),
            'route2' => 'admin.coupons.index',
            'reset_route' => 'admin.coupons.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.coupons'),
            'route1' => "admin.coupons.index",
            'route1Title' => __('translation.coupons'),
            'route2Title' => __('translation.add_new_coupon'),
            'route2' => 'admin.coupons.create',
            'route3Title' => __('translation.update_coupon'),
            'route3' => 'admin.coupons.edit',
            'reset_route' => 'admin.coupons.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Coupon Listing
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;

        $coupons = Coupon::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0);

        // Filters
        if (request('code')) {
            $coupons->where('code', 'LIKE', '%' . trim(request('code')) . '%');
        }

        if (request('status') !== null) {
            $coupons->where('is_active', request('status'));
        }
        if ($request->has('pdf')) {
            $coupons = $coupons->get();
            $pdfHeaderdata = \Config::get('constants.couponListpdf');
            $pdf = PDF::loadView('backend.pdf.coupons.couponListpdf', compact('coupons', 'pdfHeaderdata', 'breadcrumb'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $coupons = $coupons->get();
            $csvHeaderdata = \Config::get('constants.couponListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.couponcode'),
                __('translation.type'),
                __('translation.value'),
                __('translation.minamount'),
                __('translation.maxdiscount'),
                __('translation.expirydate'),
                __('translation.status'),
                __('translation.createdat'),
            ];

            foreach ($coupons as $coupon) {
                $data[++$ii] = [
                    $ii,
                    $coupon->code,
                    $coupon->type == 'flat' ? 'Flat' : 'Percent',
                    $coupon->type == 'percent' ? $coupon->value . '%' : __('translation.currency') . number_format($coupon->value, 2),
                    __('translation.currency') . number_format($coupon->min_amount ?? 0, 2),
                    __('translation.currency') . number_format($coupon->max_discount ?? 0, 2),
                    !empty($coupon->expired_date) ? "\t" . Settings::getFormattedDatetime($coupon->expired_date) : '-',
                    $coupon->is_active == 1 ? 'Active' : 'Inactive',
                    !empty($coupon->created_date) ? "\t" . Settings::getFormattedDatetime($coupon->created_date) : '-',
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }

        $coupons = $coupons->paginate(config('constants.pagination'));

        return view('backend.admin.coupon.index', compact('coupons', 'breadcrumb'));
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

    /**
     * Create Page
     */
    public function create()
    {
        return view('backend.admin.coupon.form', [
            'breadcrumb' => $this->breadcrumbListing
        ]);
    }

    /**
     * Store Coupon
     */
    public function store(Request $request)
    {
        $request->merge([
            'expires_at' => Settings::formatDate($request->expires_at, 'Y-m-d'),
        ]);

        $request->validate([
            'code' => 'required|max:255|unique:coupons,code',
            'type' => 'required|in:flat,percent',
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        try {

            Coupon::create([
                'account_id' => auth()->user()->account_id,
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'value' => $request->value,
                'min_amount' => $request->min_amount,
                'max_discount' => $request->max_discount,
                'expires_at' => $request->expires_at,
                'is_active' => $request->status ?? 1,
            ]);

            return Settings::roleRedirect('coupons.index', 'Coupon Added Successfully.');

        } catch (\Exception $e) {
            return Settings::roleRedirect('coupons.index', 'Something went wrong!', 'error');
        }
    }

    /**
     * Edit Coupon
     */
    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute(
            $this->breadcrumbListing,
            ['route3', 'route3Title'],
            ['admin.coupons.update', __('translation.update_coupon')]
        );

        $id = Settings::getDecodeCode($id);

        $coupon = Coupon::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0)
            ->findOrFail($id);

        return view('backend.admin.coupon.form', [
            'breadcrumb' => $breadcrumb,
            'coupon' => $coupon
        ]);
    }

    /**
     * Update Coupon
     */
    public function update(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->coupon_id);
            $request->merge([
                'expires_at' => Settings::formatDate($request->expires_at, 'Y-m-d'),
            ]);
            $coupon = Coupon::where('account_id', auth()->user()->account_id)
                ->where('is_deleted', 0)
                ->findOrFail($id);

            $request->validate([
                'code' => 'required|max:255|unique:coupons,code,' . $coupon->id,
                'type' => 'required|in:flat,percent',
                'value' => 'required|numeric|min:0',
                'min_amount' => 'nullable|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'expires_at' => 'nullable|date',
            ]);

            $coupon->update([
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'value' => $request->value,
                'min_amount' => $request->min_amount,
                'max_discount' => $request->max_discount,
                'expires_at' => $request->expires_at,
                'is_active' => $request->status ?? 1,
            ]);

            return Settings::roleRedirect('coupons.index', 'Coupon Updated Successfully.');

        } catch (\Exception $e) {
            return Settings::roleRedirect('coupons.index', 'Something went wrong!', 'error');
        }
    }

    /**
     * Soft Delete
     */
    public function softdelete(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            $deleted = Coupon::where('account_id', auth()->user()->account_id)
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

            $updated = Coupon::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['is_active' => $request->status]);

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

        $coupon = Coupon::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $coupon->delete();

        return redirect()->route('coupons.index')
            ->with('success', 'Coupon permanently deleted.');
    }


    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'total' => 'required|numeric|min:0'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', 1)
            ->first();

        // ❌ Not found
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ]);
        }

        // ❌ Expired
        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon expired'
            ]);
        }

        // ❌ Minimum amount
        if ($coupon->min_amount && $request->total < $coupon->min_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum amount not reached'
            ]);
        }

        // ✅ Calculate discount
        $discount = 0;

        if ($coupon->type === 'flat') {
            $discount = $coupon->value;
        } else {
            $discount = ($request->total * $coupon->value) / 100;

            if ($coupon->max_discount) {
                $discount = min($discount, $coupon->max_discount);
            }
        }

        // ❌ Prevent negative total
        if ($discount > $request->total) {
            $discount = $request->total;
        }

        return response()->json([
            'success' => true,
            'discount' => round($discount, 2),
            'final_total' => round($request->total - $discount, 2),
            'code' => $coupon->code
        ]);
    }
}