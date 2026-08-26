<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Auth;
use App\Models\Warehouse;
use App\Models\Store;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\Route;
use DB;
class InventoryController extends Controller
{

    protected $breadcrumbAddUpdate;
    protected $breadcrumbListing;

    public function __construct()
    {
        // echo Route::currentRouteName();   die();
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {

            $role = Settings::getUserRole(); // admin / staff / etc.

            $this->breadcrumbAddUpdate = [
                'title' => __('translation.stock_management'),

                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
                    ],
                    [
                        'route' => 'admin.inventory',
                        'title' => __('translation.stock_management')
                    ],
                ],

                'route1' => "admin.inventory.manage/291752",
                'route1Title' => __('translation.add_update_stock'),
                'route2' => 'admin.inventory',
                'route2Title' => __('translation.stock_management'),
                'reset_route' => 'admin.inventory',
                'reset_route_title' => __('translation.cancel'),
                'route' => 'add',
                'add' => 'stock.adjust'
            ];

            $this->breadcrumbListing = [
                'title' => __('translation.stock_management'),

                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
                    ],
                    // use route NAME only (not route())

                    [
                        'route' => 'admin.requisitions.pending.posting',
                        'title' => __('translation.add_stock')
                    ],
                    [
                        'route' => $role . '.return-barcode',
                        'title' => __('translation.return_stock')
                    ],
                    [
                        'route' => $role . '.damage-barcode',
                        'title' => __('translation.damage_stock')
                    ],
                    [
                        'route' => $role . '.deduct-barcode',
                        'title' => __('translation.deduct_stock')
                    ],
                ],

                'route1' => 'admin.inventory',
                'route1Title' => __('translation.stock_management'),
                'route2' => 'admin.inventory.manage',
                'route2Title' => __('translation.add_stock'),
                'reset_route' => 'admin.inventory',
                'reset_route_title' => __('translation.cancel'),
                'route3Title' => __('translation.update_stock'),
                'route3' => 'stock.adjust'
            ];

            return $next($request);
        });
    }


    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddUpdate;
        $categories = Category::getCategoriesPluck();

        $inventory = Inventory::query()
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select([
                'products.master_item_id',
                DB::raw('MAX(products.name) as product_name'),
                DB::raw('MAX(products.sku) as sku'),
                DB::raw('MAX(products.barcode) as barcode'),
                DB::raw('MAX(categories.name) as category_name'),
                DB::raw('SUM(inventory.stock) as total_stock'),
            ])
            ->where('inventory.account_id', auth()->user()->account_id);

        // Product Name Filter
        if ($request->filled('name')) {
            $inventory->where('products.name', 'LIKE', '%' . $request->name . '%');
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $inventory->where('products.category_id', $request->category_id);
        }

        $inventory->groupBy('products.master_item_id')
            ->orderBy('product_name');

        // PDF Export
        if ($request->filled('pdf')) {
            $inventory = $inventory->get();
            $pdfHeaderdata = config('constants.downloadinventorypdf');
            $pdf = Pdf::loadView('backend.pdf.stockManagement.stockManagementpdf', compact('inventory', 'pdfHeaderdata'));
            $pdf = Settings::downloadpdf($pdf);
            return $pdf->stream(
                $pdfHeaderdata['filename'] . '-' . now()->format('Y-m-d') . '.pdf'
            );
        }

        // CSV Export
        if ($request->filled('csv')) {
            $inventory = $inventory->get();
            $csvHeaderdata = config('constants.downloadinventorypdf');
            $fileName = $csvHeaderdata['filename'] . '-' . now()->format('Y-m-d') . '.csv';
            $data = [];
            $data[] = [
                '#',
                __('translation.category_name'),
                __('translation.product_name'),
                __('translation.stock'),
            ];
            foreach ($inventory as $key => $item) {
                $data[] = [
                    $key + 1,
                    $item->category_name,
                    $item->product_name,
                    $item->total_stock,
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $inventory = $inventory->paginate(account_setting('general.pagination'));
        // $this->pr($inventory); die();
        return view('backend.admin.inventory.index', compact('inventory', 'breadcrumb', 'categories'));
    }

    public function index_delete(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddUpdate;
        $categories = Category::getCategoriesPluck();
        $inventory = Inventory::with('product')
            ->where('account_id', auth()->user()->account_id)
            ->latest();

        if (request('name')) {
            $inventory->whereHas('product', function ($query) {
                $query->where('name', 'LIKE', '%' . request('name') . '%');
            });
        }
        if (request('category_id')) {
            $inventory->whereHas('product', function ($query) {
                $query->where('category_id', request('category_id'));
            });
        }
        if ($request->pdf) {
            $inventory = $inventory->get();
            $pdfHeaderdata = \Config::get('constants.downloadinventorypdf');
            $pdf = Pdf::loadView('backend.pdf.stockManagement.stockManagementpdf', compact('inventory', 'pdfHeaderdata'));
            $pdf = Settings::downloadLandscapepdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $inventory = $inventory->get();
            $csvHeaderdata = \Config::get('constants.downloadinventorypdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.category_name'),
                __('translation.product_name'),
                __('translation.sku'),
                __('translation.barcode'),
                __('translation.stock'),
            ];

            foreach ($inventory as $stock) {
                $data[++$ii] = [
                    $ii,
                    $stock->product->category->name ?? '',
                    $stock->product->name ?? '',
                    $stock->product->sku ?? '',
                    $stock->product->barcode ?? '',
                    $stock->stock,
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }

        $inventory = $inventory->paginate(\Config::get('constants.pagination'));

        return view('backend.admin.inventory.index', compact('inventory', 'breadcrumb', 'categories'));
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

    public function create(Request $request, $id = null)
    {
        $adjustmentData = Settings::getInventoryAdjustment($id);
        if (empty($adjustmentData['adjustment'])) {
            return Settings::roleRedirect('inventory', 'Something went wrong!', 'error');
        }
        $route = $adjustmentData['route'];
        $adjustment = $adjustmentData['adjustment'];
        $breadcrumb = $this->breadcrumbListing;
        $products = Product::where('account_id', auth()->user()->account_id)->notDeleted()->active()->pluck('name', 'id')->toArray();
        return view('backend.admin.inventory.form', compact('breadcrumb', 'products', 'route', 'adjustment'));
    }

    public function update(Request $request, $token)
    {   
        // $data = Crypt::decrypt($token);
        // $adjustmentData = Settings::getInventoryAdjustment($data['adjustment']);
        // echo $adjustmentData['route'];
        // echo '<pre>'; print_r($data); die();
        try {
            $data = Crypt::decrypt($token);
        } catch (\Exception $e) {
            return redirect()->route('admin.barcode')->with('error', 'Invalid link');
        }
        $warehouses = Warehouse::active()->ofAccount()->orderBy('name', 'asc')->pluck('name', 'id')->prepend(__('translation.to_warehouse'), '');
        $stores = Store::active()->ofAccount()->ofMyStore()->orderBy('name', 'asc')->pluck('name', 'id')->prepend(__('translation.my_store'), '');
        $form = 'backend.admin.inventory.form';
        $masterItemName = null;
        $qty = null;
        $requisition_item_id = null;
        $adjustmentData = Settings::getInventoryAdjustment($data['adjustment']);
        if (empty($adjustmentData['adjustment'])) {
            return Settings::roleRedirect('inventory', 'Something went wrong!', 'error');
        }
        $route = $adjustmentData['route'];
        $adjustment = $adjustmentData['adjustment'];
        $barcode = $data['barcode'];
        $productId = $data['product_id'];
        $requisition_item_id = $data['requisition_item_id'];
        // ==========================
        // GET REQUISITION ITEM
        // ========================== 
        if($requisition_item_id !=''){ 
            $requisitionItem = RequisitionItem::with('masterItem')->find(Settings::getDecodeCode($requisition_item_id));
            if ($requisitionItem) {
                $masterItemName = $requisitionItem->masterItem->name ?? null;
                $qty = (int) ($requisitionItem->qty ?? 0);
            }
        } 

        // ✅ Load product
        $product = Product::where('account_id', auth()->user()->account_id)->where('barcode', $barcode)->first();
        // $this->pr($product->stocks[0]->stock);
        if (!$product) {
            return redirect()->route('admin.barcode')->with('error', 'Product not found');
        }
        $breadcrumb = $this->breadcrumbListing;
        $products = Product::where('account_id', auth()->user()->account_id)->where('barcode', $barcode)->pluck('name', 'id')->toArray();
        if ($route == 'Deduct') {
            $form = 'backend.admin.inventory.return_to_warehouse';
        }
        if ($route == 'Damage') {
            $qty = 1;
        }
        return view($form, compact(
            'breadcrumb',
            'products',
            'route',
            'warehouses',
            'stores',
            'adjustment',
            'product',        // ✅ Pass product
            'barcode',        // ✅ Pass barcode
            'productId',      // ✅ Pass product_id
            'masterItemName', // ✅ Pass masterItemName
            'qty',            // ✅ Pass qty
            'requisition_item_id' // ✅ Pass requisition_item_id
        ));
    }



}