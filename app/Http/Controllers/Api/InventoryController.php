<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Store;
use App\Models\RequisitionItem;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryController extends Controller
{
    /**
     * Inventory Listing
     *
     * GET /api/inventory
     */
    public function index(Request $request)
    {
        try {

            $accountId = auth()->user()->account_id;

            $query = Inventory::query()
                ->join(
                    'products',
                    'products.id',
                    '=',
                    'inventory.product_id'
                )
                ->leftJoin(
                    'categories',
                    'categories.id',
                    '=',
                    'products.category_id'
                )
                ->select([
                    'products.master_item_id',
                    DB::raw('MAX(products.name) as product_name'),
                    DB::raw('MAX(products.sku) as sku'),
                    DB::raw('MAX(products.barcode) as barcode'),
                    DB::raw('MAX(categories.name) as category_name'),
                    DB::raw('SUM(inventory.stock) as total_stock'),
                ])
                ->where(
                    'inventory.account_id',
                    $accountId
                );

            /*
            |--------------------------------------------------------------------------
            | Product Name Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('name')) {
                $query->where(
                    'products.name',
                    'LIKE',
                    '%' . trim($request->name) . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Category Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('category_id')) {
                $query->where(
                    'products.category_id',
                    $request->category_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Grouping
            |--------------------------------------------------------------------------
            */

            $query
                ->groupBy('products.master_item_id')
                ->orderBy('product_name');

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            $perPage = (int) $request->get(
                'per_page',
                account_setting('general.pagination')
            );

            $perPage = min(
                max($perPage, 1),
                100
            );

            $inventory = $query
                ->paginate($perPage)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Inventory fetched successfully.',
                'data' => [
                    'inventory' => $inventory->items(),

                    'pagination' => [
                        'current_page' => $inventory->currentPage(),
                        'last_page' => $inventory->lastPage(),
                        'per_page' => $inventory->perPage(),
                        'total' => $inventory->total(),
                        'from' => $inventory->firstItem(),
                        'to' => $inventory->lastItem(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get Inventory Adjustment Data
     *
     * GET /api/inventory/adjustment/{id?}
     */
    public function create(Request $request, $id = null)
    {
        try {

            $adjustmentData = Settings::getInventoryAdjustment($id);

            if (empty($adjustmentData['adjustment'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid inventory adjustment.',
                ], 400);
            }

            $products = Product::query()
                ->where(
                    'account_id',
                    auth()->user()->account_id
                )
                ->notDeleted()
                ->active()
                ->select([
                    'id',
                    'name',
                    'sku',
                    'barcode',
                    'master_item_id',
                ])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Inventory adjustment data fetched successfully.',
                'data' => [
                    'route' => $adjustmentData['route'],
                    'adjustment' => $adjustmentData['adjustment'],
                    'products' => $products,
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get Inventory Adjustment Details
     *
     * GET /api/inventory/adjustment-details/{token}
     */
    public function update(Request $request, $token)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Decrypt Token
            |--------------------------------------------------------------------------
            */

            try {
                $data = Crypt::decrypt($token);
            } catch (\Exception $e) {

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired link.',
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Token Data
            |--------------------------------------------------------------------------
            */

            $adjustment = $data['adjustment'] ?? null;
            $barcode = $data['barcode'] ?? null;
            $productId = $data['product_id'] ?? null;
            $requisitionItemId = $data['requisition_item_id'] ?? null;

            if (!$adjustment || !$barcode) {

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid adjustment data.',
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Adjustment Configuration
            |--------------------------------------------------------------------------
            */

            $adjustmentData = Settings::getInventoryAdjustment(
                $adjustment
            );

            if (empty($adjustmentData['adjustment'])) {

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid inventory adjustment.',
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Warehouses
            |--------------------------------------------------------------------------
            */

            $warehouses = Warehouse::active()
                ->ofAccount()
                ->orderBy('name', 'asc')
                ->select([
                    'id',
                    'name',
                ])
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Stores
            |--------------------------------------------------------------------------
            */

            $stores = Store::active()
                ->ofAccount()
                ->ofMyStore()
                ->orderBy('name', 'asc')
                ->select([
                    'id',
                    'name',
                ])
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Requisition Item
            |--------------------------------------------------------------------------
            */

            $masterItemName = null;
            $qty = null;

            if (!empty($requisitionItemId)) {

                $decodedRequisitionItemId =
                    Settings::getDecodeCode($requisitionItemId);

                $requisitionItem = RequisitionItem::with('masterItem')
                    ->find($decodedRequisitionItemId);

                if ($requisitionItem) {

                    $masterItemName =
                        optional($requisitionItem->masterItem)->name;

                    $qty = (int) ($requisitionItem->qty ?? 0);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $product = Product::query()
                ->where(
                    'account_id',
                    auth()->user()->account_id
                )
                ->where(
                    'barcode',
                    $barcode
                )
                ->first();

            if (!$product) {

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Damage Adjustment
            |--------------------------------------------------------------------------
            */

            if ($adjustmentData['route'] === 'Damage') {
                $qty = 1;
            }

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Inventory adjustment details fetched successfully.',
                'data' => [
                    'route' => $adjustmentData['route'],
                    'adjustment' => $adjustmentData['adjustment'],

                    'product' => [
                        'id' => $product->id,
                        'master_item_id' => $product->master_item_id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                    ],

                    'barcode' => $barcode,
                    'product_id' => $productId,

                    'master_item_name' => $masterItemName,

                    'qty' => $qty,

                    'requisition_item_id' =>
                        $requisitionItemId,

                    'warehouses' => $warehouses,
                    'stores' => $stores,
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Export Inventory PDF
     *
     * GET /api/inventory/export/pdf
     */
    public function exportPdf(Request $request)
    {
        try {

            $accountId = auth()->user()->account_id;

            $query = Inventory::query()
                ->join(
                    'products',
                    'products.id',
                    '=',
                    'inventory.product_id'
                )
                ->leftJoin(
                    'categories',
                    'categories.id',
                    '=',
                    'products.category_id'
                )
                ->select([
                    'products.master_item_id',
                    DB::raw('MAX(products.name) as product_name'),
                    DB::raw('MAX(products.sku) as sku'),
                    DB::raw('MAX(products.barcode) as barcode'),
                    DB::raw('MAX(categories.name) as category_name'),
                    DB::raw('SUM(inventory.stock) as total_stock'),
                ])
                ->where(
                    'inventory.account_id',
                    $accountId
                );

            if ($request->filled('name')) {
                $query->where(
                    'products.name',
                    'LIKE',
                    '%' . trim($request->name) . '%'
                );
            }

            if ($request->filled('category_id')) {
                $query->where(
                    'products.category_id',
                    $request->category_id
                );
            }

            $inventory = $query
                ->groupBy('products.master_item_id')
                ->orderBy('product_name')
                ->get();

            $pdfHeaderdata = config(
                'constants.downloadinventorypdf'
            );

            $pdf = Pdf::loadView(
                'backend.pdf.stockManagement.stockManagementpdf',
                compact(
                    'inventory',
                    'pdfHeaderdata'
                )
            );

            $pdf = Settings::downloadpdf($pdf);

            $fileName =
                $pdfHeaderdata['filename']
                . '-'
                . now()->format('Y-m-d')
                . '.pdf';

            return $pdf->stream($fileName);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Export Inventory CSV
     *
     * GET /api/inventory/export/csv
     */
    public function exportCsv(Request $request)
    {
        try {

            $accountId = auth()->user()->account_id;

            $query = Inventory::query()
                ->join(
                    'products',
                    'products.id',
                    '=',
                    'inventory.product_id'
                )
                ->leftJoin(
                    'categories',
                    'categories.id',
                    '=',
                    'products.category_id'
                )
                ->select([
                    'products.master_item_id',
                    DB::raw('MAX(products.name) as product_name'),
                    DB::raw('MAX(products.sku) as sku'),
                    DB::raw('MAX(products.barcode) as barcode'),
                    DB::raw('MAX(categories.name) as category_name'),
                    DB::raw('SUM(inventory.stock) as total_stock'),
                ])
                ->where(
                    'inventory.account_id',
                    $accountId
                );

            if ($request->filled('name')) {
                $query->where(
                    'products.name',
                    'LIKE',
                    '%' . trim($request->name) . '%'
                );
            }

            if ($request->filled('category_id')) {
                $query->where(
                    'products.category_id',
                    $request->category_id
                );
            }

            $inventory = $query
                ->groupBy('products.master_item_id')
                ->orderBy('product_name')
                ->get();

            $pdfHeaderdata = config(
                'constants.downloadinventorypdf'
            );

            $fileName =
                $pdfHeaderdata['filename']
                . '-'
                . now()->format('Y-m-d')
                . '.csv';

            $data = [];

            $data[] = [
                '#',
                __('translation.category_name'),
                __('translation.product_name'),
                __('translation.sku'),
                __('translation.barcode'),
                __('translation.stock'),
            ];

            foreach ($inventory as $key => $item) {

                $data[] = [
                    $key + 1,
                    $item->category_name ?? '',
                    $item->product_name ?? '',
                    $item->sku ?? '',
                    $item->barcode ?? '',
                    $item->total_stock ?? 0,
                ];
            }

            return Settings::downloadcsvfile(
                $data,
                $fileName
            );

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}