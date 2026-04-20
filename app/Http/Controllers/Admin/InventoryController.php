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
class InventoryController extends Controller
{

    protected $breadcrumbAddUpdate;
    protected $breadcrumbListing;

    public function __construct()
    {
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
                    // use route NAME only (not route())
                    [
                        'route' => $role . '.no-barcode',
                        'title' => __('translation.add_product_without_barcode')
                    ],
                    [
                        'route' => $role . '.barcode',
                        'title' => __('translation.add_stock')
                    ],
                    [
                        'route' => $role . '.sales-barcode',
                        'title' => __('translation.sale_stock')
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
                        'route' => $role . '.no-barcode',
                        'title' => __('translation.add_product_without_barcode')
                    ],
                    [
                        'route' => $role . '.barcode',
                        'title' => __('translation.add_stock')
                    ],
                    [
                        'route' => $role . '.sales-barcode',
                        'title' => __('translation.sale_stock')
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
                __('translation.low_alert'),
                __('translation.status'),
            ];

            foreach ($inventory as $stock) {
                $data[++$ii] = [
                    $ii,
                    $stock->product->category->name ?? '',
                    $stock->product->name ?? '',
                    $stock->product->sku ?? '',
                    $stock->product->barcode ?? '',
                    $stock->stock,
                    $stock->low_stock_alert,
                    $stock->isLowStock() ? __('translation.low_stock') : __('translation.normal_stock'),
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
        try {
            $data = Crypt::decrypt($token);
        } catch (\Exception $e) {
            return redirect()->route('admin.barcode')->with('error', 'Invalid link');
        }
        $adjustmentData = Settings::getInventoryAdjustment($data['adjustment']);
        if (empty($adjustmentData['adjustment'])) {
            return Settings::roleRedirect('inventory', 'Something went wrong!', 'error');
        }
        $route = $adjustmentData['route'];
        $adjustment = $adjustmentData['adjustment'];
        $barcode = $data['barcode'];
        $productId = $data['product_id'];

        // ✅ Load product
        $product = Product::where('account_id', auth()->user()->account_id)->where('barcode', $barcode)->first();

        if (!$product) {
            return redirect()->route('admin.barcode')->with('error', 'Product not found');
        }
        $breadcrumb = $this->breadcrumbListing;
        $products = Product::where('account_id', auth()->user()->account_id)->where('barcode', $barcode)->pluck('name', 'id')->toArray();
        return view('backend.admin.inventory.form', compact(
            'breadcrumb',
            'products',
            'route',
            'adjustment',
            'product',        // ✅ Pass product
            'barcode',        // ✅ Pass barcode
            'productId'       // ✅ Pass product_id
        ));
    }

}