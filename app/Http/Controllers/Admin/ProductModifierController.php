<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductModifier;
use App\Models\ModifierOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductModifierController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required',
            'options.*.name' => 'required',
        ]);

        DB::beginTransaction();

        try {

            $modifier = ProductModifier::create([
                'product_id' => $request->product_id,
                'name' => $request->name,
                'is_required' => $request->is_required ?? 0,
            ]);

            foreach ($request->options as $opt) {
                ModifierOption::create([
                    'modifier_id' => $modifier->id,
                    'name' => $opt['name'],
                    'price' => $opt['price'] ?? 0,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Modifier created');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error occurred');
        }
    }
}