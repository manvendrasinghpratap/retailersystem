<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.product'),
            'route1' => "admin.products.create",
            'route1Title' => __('translation.add_edit_product'),
            'route2Title' => __('translation.add_edit_product'),
            'route2' => 'admin.products',
            'reset_route' => 'admin.products', 'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.product'),
            'route1' => "admin.products",
            'route1Title' => __('translation.product_listing'),
            'route2Title' => __('translation.add_edit_product'),
            'route2' => 'admin.products.create',
            'route3Title' => __('translation.add_edit_product'),
            'route3' => 'admin.products.edit',
            'reset_route' => 'admin.products', 'reset_route_title' => __('translation.cancel')
        ];
    }

    public function index()
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $categories = Category::ofAccount()->notDeleted()->active()->pluck('name','id');
        $products = Product::with('category')
            ->where('account_id', auth()->user()->account_id)->notDeleted()
            ->latest();

        if (request('name')) {
            $products->where('name', 'LIKE', '%' . request('name') . '%');
        }
        if(request('category_id')) {
            $products->where('category_id', request('category_id'));
        }
        if(request('is_active')) {
            $products->where('status', request('is_active'));
        }
        

        $products = $products->paginate(config('constants.pagination'));

        return view('backend.admin.product.index', compact('products','breadcrumb','categories'));
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbListing;
        $categories = Category::ofAccount()->notDeleted()->pluck('name','id');
        return view('backend.admin.product.form', compact('categories','breadcrumb'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'barcode' => 'BAR-'.time().rand(1000,9999),
            'sku' => 'SKU-'.time().rand(1000,9999),
        ]);
        try {
            $request->validate([
                'name'        => 'required|string|max:255',
                'price'       => 'required|numeric|min:0',
                'cost_price'  => 'required|numeric|min:0',
                'status'      => 'nullable|in:0,1',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
            ]);
            $product = Product::create($request->all());  
            $product->update([
                'sku' => 'PROD-'.$product->id,
                'barcode' => 'BAR-'.$product->id,
            ]);          
            return Settings::roleRedirect('products','Product Added Successfully.');

        } catch (\Exception $e) {
            return Settings::roleRedirect('products','Something went wrong!','error');
        }
    }

    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);
        $breadcrumb = $this->breadcrumbListing;
        $product = Product::where('account_id', auth()->user()->account_id)->findOrFail($id);
        $categories = Category::ofAccount()->notDeleted()->pluck('name','id');

        return view('backend.admin.product.form', compact('product','categories','breadcrumb'));
    }

    public function update(Request $request)
    {
        try{
        $id = Settings::getDecodeCode($request->product_id);
        $product = Product::where('account_id', auth()->user()->account_id)->findOrFail($id);
        $request->validate([
                'name'        => 'required|string|max:255',
                'price'       => 'required|numeric|min:0',
                'cost_price'  => 'required|numeric|min:0',
                'status'      => 'nullable|in:0,1',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
            ]);
        $product->update($request->all());
        return Settings::roleRedirect('products','Product Updated Successfully.');
        }catch (\Exception $e) {
            return Settings::roleRedirect('products','Something went wrong!','error');
        }
    }

    public function destroy(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);

        Product::where('account_id', auth()->user()->account_id)
            ->where('id', $id)
            ->delete();

        return response()->json(['success'=>true]);
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
                ->update(['is_deleted' => 1,'deleted_by' => auth()->user()->id,'deleted_at' => now()]);

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
}