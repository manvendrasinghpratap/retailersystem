<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Collection;

class InventoryController extends Controller
{

    protected $breadcrumbAddNew;
    protected $breadcrumbListing;
    protected $breadcrumbSubscribeListing;
    protected $breadcrumbChangePassword;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbAddUpdate = ['title' => __('translation.stock_management'), 'route1' => "admin.inventory.manage/291752", 'route1Title' => __('translation.add_update_stock'), 'route2' => 'admin.inventory', 'route2Title' => __('translation.stock_management'), 'reset_route' => 'admin.inventory', 'reset_route_title' => __('translation.cancel'), 'route' => 'add', 'add' => 'stock.adjust'];

        $this->breadcrumbListing = ['title' => __('translation.stock_management'), 'route1' => 'admin.inventory', 'route1Title' => __('translation.stock_management'), 'route2' => 'admin.inventory.manage', 'route2Title' => __('translation.add_stock'), 'reset_route' => 'admin.inventory', 'reset_route_title' => __('translation.cancel'), 'route3Title' => __('translation.update_stock'), 'route3' => 'stock.adjust'];
    }


    public function index()
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

        $inventory = $inventory->paginate(\Config::get('constants.pagination'));

        return view('backend.admin.inventory.index', compact('inventory', 'breadcrumb', 'categories'));
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