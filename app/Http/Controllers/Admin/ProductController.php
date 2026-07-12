<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\StockAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseItem;
use App\Models\MasterItem;
use App\Models\RequisitionItem;
use App\Models\Requisition;
use DB;

class ProductController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {

            $role = Settings::getUserRole(); // admin / staff / etc.

            $this->breadcrumbAddNew = [
                'title' => __('translation.product'),

                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
                    ],
                    [
                        'route' => 'admin.products',
                        'title' => __('translation.brands')
                    ],
                    // use route NAME only (not route())
                    // [
                    //     'route' => $role . '.no-barcode',
                    //     'title' => __('translation.add_product_without_barcode')
                    // ],
                    [
                        'route' => 'admin.requisitions.index',
                        'title' => __('translation.add_edit_product')
                    ],
                ],

                'route1' => "admin.barcode",
                'route1Title' => __('translation.add_edit_product'),
                'route2Title' => __('translation.brands'),
                'route2' => 'admin.products',
                'reset_route' => 'admin.products',
                'reset_route_title' => __('translation.cancel'),
                'route4Title' => __('translation.add_product_without_barcode'),
                'route4' => 'admin.no-barcode',
            ];

            $this->breadcrumbListing = [
                'title' => __('translation.product'),

                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
                    ],
                    // use route NAME only (not route()) 
                    [
                        'route' => 'admin.requisitions.pending.posting',
                        'title' => __('translation.add_product_without_barcode')
                    ],
                    [
                        'route' => 'admin.requisitions.pending.posting',
                        'title' => __('translation.add_edit_product')
                    ],

                ],

                'route1' => "admin.products",
                'route1Title' => __('translation.product_listing'),
                'route2Title' => __('translation.add_edit_product'),
                'route2' => 'admin.products.create',
                'route3Title' => __('translation.add_edit_product'),
                'route3' => 'admin.products.edit',
                'reset_route' => 'admin.products',
                'reset_route_title' => __('translation.cancel'),
                'route4Title' => __('translation.add_edit_product'),
                'route4' => 'admin.barcode',
            ];

            return $next($request);
        });
    }

    /**
     * @description Show list of products
     * @access      public
     * @param       Request $request
     * @return      View
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $categories = Category::getCategoriesPluck();
        $products = Product::getProducts();

        if (request('name')) {
            $products->where('name', 'LIKE', '%' . request('name') . '%');
        }
        if (request('category_id')) {
            $products->where('category_id', request('category_id'));
        }
        if (request('is_active')) {
            $products->where('status', request('is_active'));
        }
        if ($request->pdf) {
            $products = $products->get();
            $pdfHeaderdata = \Config::get('constants.downloadproductpdf');
            $pdf = Pdf::loadView('backend.pdf.products.productpdf', compact('products', 'pdfHeaderdata'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $products = $products->get();
            $csvHeaderdata = \Config::get('constants.downloadproductpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.category_name'),
                __('translation.product_name'),
                __('translation.selling_price'),
                __('translation.barcode'),
                __('translation.sku'),
                __('translation.status'),
            ];

            foreach ($products as $product) {
                $data[++$ii] = [
                    $ii,
                    $product->category->name ?? '-',
                    $product->name,
                    $product->selling_price,
                    $product->barcode,
                    $product->sku,
                    $product->status == 1 ? __('translation.active') : __('translation.inactive'),
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $products = $products->paginate(config('constants.pagination'));
        return view('backend.admin.product.index', compact('products', 'breadcrumb', 'categories'));
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


    public function create(Request $request, $token = null)
    {
        $barcode = $productId = $route = $adjustment = $requisition_item_id = null;
        $masterItemName = null;
        $qty = null;
        if ($token) {
            try {
                $data = Crypt::decrypt($token);
                $adjustmentData = Settings::getInventoryAdjustment($data['adjustment']);
                if (empty($adjustmentData['adjustment'])) {
                    return Settings::roleRedirect(
                        'inventory',
                        'Something went wrong!',
                        'error'
                    );
                }
                $route = $adjustmentData['route'];
                $adjustment = $adjustmentData['adjustment'];
                $barcode = $data['barcode'];
                $productId = $data['product_id'];
                $requisition_item_id = $data['requisition_item_id'];

                // ==========================
                // GET REQUISITION ITEM
                // ==========================
                $requisitionItem = RequisitionItem::with('masterItem')->find(Settings::getDecodeCode($requisition_item_id));
                if ($requisitionItem) {
                    $masterItemName = $requisitionItem->masterItem->name ?? null;
                    $qty = $requisitionItem->qty;
                }
            } catch (\Exception $e) {
                return redirect()
                    ->route('admin.barcode')
                    ->with('error', 'Invalid link');
            }
        }
        $breadcrumb = $this->breadcrumbListing;
        $categories = Category::getCategoriesPluck();
        return view(
            'backend.admin.product.form',
            compact(
                'categories',
                'breadcrumb',
                'barcode',
                'productId',
                'route',
                'adjustment',
                'requisition_item_id',
                'masterItemName',
                'qty'
            )
        );
    }


    public function store(Request $request)
    {
        $prefix = strtoupper(substr($request->name, 0, 3));

        $request->merge([
            'barcode' => $request->filled('barcode')
                ? $request->barcode
                : Settings::generateEan13(),

            'sku' => $request->filled('sku')
                ? $request->sku
                : $prefix . '-' . time() . rand(100, 999),
        ]);

        try {
            DB::transaction(function () use ($request) {
                $masterItemId = Settings::getDecodeCode($request->requisition_item_id);
                $requisitionItem = RequisitionItem::with('masterItem')->lockForUpdate()->find($masterItemId);
                $request->merge(['master_item_id' => $requisitionItem->master_item_id]);
                $request->merge(['cost_price' => $request->selling_price,]);
                $request->validate([
                    'name' => 'required|string|max:255',
                    'selling_price' => 'required|numeric|min:0',
                    'cost_price' => 'required|numeric|min:0',
                    'status' => 'nullable|in:0,1',
                    'category_id' => 'nullable|exists:categories,id',
                    'description' => 'nullable|string',
                ]);

                // ====================================
                // CHECK REQUISITION ITEM
                // ====================================

                $id = Settings::getDecodeCode($request->requisition_item_id);

                $requisitionItem = RequisitionItem::with('masterItem')
                    ->lockForUpdate()
                    ->find($id);

                if (!$requisitionItem) {
                    throw new \Exception('Invalid requisition item');
                }

                // ====================================
                // DUPLICATE CHECK
                // ====================================

                if (!empty($requisitionItem->accepted_by)) {

                    throw new \Exception(
                        'This requisition item is already accepted by another user.'
                    );
                }

                // ====================================
                // CREATE PRODUCT
                // ====================================

                $product = Product::create($request->all());

                $product->update([
                    'sku' => strtoupper(
                        substr($product->category->name ?? 'PRD', 0, 3)
                    ) . '-' . $product->id
                ]);
                // ====================================
                // UPDATE REQUISITION ITEM
                // ====================================
                $requisitionItem->update(['accepted_by' => auth()->id()]);
                // ====================================
                // UPDATE REQUISITION STATUS
                // ====================================
                $requisition = Requisition::find($requisitionItem->requisition_id);
                $requisition->updateStatusByItems();

                // ====================================
                // INITIAL STOCK
                // ====================================

                if ($request->route == 'Add') {

                    StockAdjustment::create([
                        'product_id' => $product->id,
                        'type' => 'add',
                        'quantity' => $request->quantity ?? 0,
                        'note' => 'Initial stock added'
                    ]);
                }
            });
            return redirect()->route('admin.requisitions.pending.posting')->with('success', 'Product Added Successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.requisitions.pending.posting')->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);
        $route = 'edit';
        $breadcrumb = $this->breadcrumbListing;
        $product = Product::where('account_id', auth()->user()->account_id)->findOrFail($id);
        $categories = Category::ofAccount()->notDeleted()->pluck('name', 'id');


        return view('backend.admin.product.form', compact('product', 'categories', 'breadcrumb', 'route'));
    }

    public function update(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->product_id);
            $product = Product::where('account_id', auth()->user()->account_id)->findOrFail($id);
            $request->validate([
                'name' => 'required|string|max:255',
                'selling_price' => 'required|numeric|min:0',
                // 'cost_price' => 'required|numeric|min:0',
                'status' => 'nullable|in:0,1',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
            ]);
            $product->update($request->all());
            return Settings::roleRedirect('products', 'Product Updated Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('products', 'Something went wrong!', 'error');
        }
    }

    public function destroy(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);

        Product::where('account_id', auth()->user()->account_id)
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }


    /**
     * Soft Delete
     */
    public function softdelete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);

            $deleted = Product::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['is_deleted' => 1, 'deleted_by' => auth()->user()->id, 'deleted_at' => now()]);

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

    public function getLastPrice(Request $request)
    {
        $masterItemId = $request->master_item_id;
        $vendorId = $request->vendor_id;
        $warehouseId = $request->warehouse_id;
        $accountId = auth()->user()->account_id;

        $baseQuery = PurchaseItem::query()
            ->where('master_item_id', $masterItemId)
            ->whereHas('purchase', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            });

        // =========================
        // 1️⃣ Same Item + Vendor + Warehouse
        // =========================
        $last = (clone $baseQuery)
            ->whereHas('purchase', function ($q) use ($vendorId, $warehouseId) {

                $q->when($vendorId, function ($sq) use ($vendorId) {
                    $sq->where('vendor_id', $vendorId);
                });

                $q->when($warehouseId, function ($sq) use ($warehouseId) {
                    $sq->where('warehouse_id', $warehouseId);
                });
            })
            ->latest('id')
            ->first();

        // =========================
        // 2️⃣ Same Item + Vendor
        // =========================
        if (!$last && $vendorId) {

            $last = (clone $baseQuery)
                ->whereHas('purchase', function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId);
                })
                ->latest('id')
                ->first();
        }

        // =========================
        // 3️⃣ Same Item + Warehouse
        // =========================
        if (!$last && $warehouseId) {

            $last = (clone $baseQuery)
                ->whereHas('purchase', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                })
                ->latest('id')
                ->first();
        }

        // =========================
        // 4️⃣ Fallback → Item Only
        // =========================
        if (!$last) {

            $last = (clone $baseQuery)
                ->latest('id')
                ->first();
        }

        return response()->json([
            'price' => $last?->cost_price ?? 0
        ]);
    }




    public function search(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $search = trim($request->q);

        $items = MasterItem::query()
            ->ofAccount()
            ->where('is_deleted', 0)->orderBy('name', 'asc')

            // Search filter
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            })

            // Only items available in warehouse
            ->when($warehouseId, function ($q) use ($warehouseId) {

                $q->whereHas('stocks', function ($sq) use ($warehouseId) {

                    $sq->where('warehouse_id', $warehouseId)
                        ->where('stock', '>', 0);
                });
            })

            ->limit(20)
            ->get();

        return response()->json(
            $items->map(function ($item) {

                return [
                    'id' => $item->id,
                    'text' => $item->name
                ];
            })
        );
    }

}