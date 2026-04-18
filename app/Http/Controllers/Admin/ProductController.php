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

class ProductController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.product'),
            'route1' => "admin.barcode",
            'route1Title' => __('translation.add_edit_product'),
            'route2Title' => __('translation.add_edit_product'),
            'route2' => 'admin.products',
            'reset_route' => 'admin.products',
            'reset_route_title' => __('translation.cancel'),
            'route4Title' => __('translation.add_product_without_barcode'),
            'route4' => 'admin.no-barcode',
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.product'),
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
    }

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
        $barcode = $productId = $route = $adjustment = null;
        if ($token) {
            try {
                $data = Crypt::decrypt($token);
                $adjustmentData = Settings::getInventoryAdjustment($data['adjustment']);
                if (empty($adjustmentData['adjustment'])) {
                    return Settings::roleRedirect('inventory', 'Something went wrong!', 'error');
                }
                $route = $adjustmentData['route'];
                $adjustment = $adjustmentData['adjustment'];
                $barcode = $data['barcode'];
                $productId = $data['product_id'];

            } catch (\Exception $e) {
                return redirect()->route('admin.barcode')->with('error', 'Invalid link');
            }
        }
        $breadcrumb = $this->breadcrumbListing;
        $categories = Category::getCategoriesPluck();
        return view('backend.admin.product.form', compact('categories', 'breadcrumb', 'barcode', 'productId', 'route', 'adjustment'));
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
            $request->merge([
                'cost_price' => $request->selling_price,
            ]);
            $request->validate([
                'name' => 'required|string|max:255',
                'selling_price' => 'required|numeric|min:0',
                'cost_price' => 'required|numeric|min:0',
                'status' => 'nullable|in:0,1',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
            ]);
            $product = Product::create($request->all());
            $product->update([
                'sku' => strtoupper(substr($product->category->name, 0, 3)) . '-' . $product->id
            ]);

            if ($request->route == 'Add') {
                StockAdjustment::create([
                    'product_id' => $product->id,
                    'type' => 'add',
                    'quantity' => $request->quantity ?? 0,
                    'note' => 'Initial stock added'
                ]);
                return Settings::roleRedirect('barcode', 'Product Added Successfully.');
            }

            return Settings::roleRedirect('products', 'Product Added Successfully.');

        } catch (\Exception $e) {
            return Settings::roleRedirect('products', 'Something went wrong!', 'error');
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
                'cost_price' => 'required|numeric|min:0',
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
}