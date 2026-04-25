<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Helpers\Settings;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcode');
    }

    public function barcodeForm($id)
    {
        try {
            $productId = Settings::getDecodeCodeWithHashids($id)[0];
            $product = Product::findOrFail($productId);
            return view('backend.admin.product.barcode-form', compact('product'));
        } catch (\Throwable $th) {
            return redirect()->route('admin.inventory');
        }
    }

    public function barcodePrint(Request $request)
    {
        $id = Settings::getDecodeCodeWithHashids($request->product_id)[0];
        $product = Product::findOrFail($id);
        $qty = $request->qty;
        $size = $request->size;

        return view('backend.admin.product.barcode-print', compact(
            'product',
            'qty',
            'size'
        ));
    }
}
