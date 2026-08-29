<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RequisitionController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->middleware('api.request')->group(function () {
        // Login does not require JWT
        Route::post('/login', [AuthController::class, 'login']);
        // JWT protected routes
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });


/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Requires:
| 1. X-API-Key
| 2. Valid JWT Bearer token
|
*/

Route::prefix('dashboard')->middleware(['api.request', 'auth:api'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
});


Route::prefix('reports')->middleware(['api.request', 'auth:api'])->group(function () {
    Route::get('daily-sales', [ReportController::class, 'dailySales']);
});

Route::prefix('requisitions')->middleware(['api.request', 'auth:api'])->group(function () {
        // Requisition List
        Route::get('/', [RequisitionController::class, 'index']);
        
        // Create Requisition
        Route::get('/create', [RequisitionController::class, 'create']);

        // Store Requisition
        Route::post('/store', [RequisitionController::class, 'store']);

        // Cancel Requisition
        Route::post('/cancel', [RequisitionController::class, 'cancel']);

        // Requisition Products
        Route::get('/requisition-products', [RequisitionController::class, 'requisitionProducts']);

        // Complete Requisition
        Route::post('/complete', [RequisitionController::class, 'complete']);

        // Pending Posting
        Route::get('/pending-posting', [RequisitionController::class, 'pendingPosting']);   

        // Pending Posting History
        Route::get('/pending-posting-history', [RequisitionController::class, 'pendingPostingHistory']);    

        // Pending Posting History Report
        Route::get('/pending-posting-history-report', [RequisitionController::class, 'pendingPostingHistoryReport']);

        // Cancel Requisition Item
        Route::post('/cancel-item', [RequisitionController::class, 'cancelItem']);

        // Validate Requisition Barcode
        Route::post('/validate-requisition-barcode', [RequisitionController::class, 'validateRequisitionBarcode']);

        // Search Barcode
        Route::post('/barcode/search', [RequisitionController::class, 'searchBarcode']);
});

Route::prefix('sales')->middleware(['api.request', 'auth:api'])->group(function () {
    Route::get('/', [SaleController::class, 'index']);
    Route::get('/export-pdf', [SaleController::class, 'exportPdf']);
    Route::get('/export-csv', [SaleController::class, 'exportCsv']);
    Route::get('/{sale}', [SaleController::class, 'show']);
    Route::get('/{sale}/payment-details', [SaleController::class, 'paymentDetails']);
    Route::post('/save-credit-payment', [SaleController::class, 'saveCreditPayment']);
});

Route::prefix('warehouses')->middleware(['api.request', 'auth:api'])->group(function () {
        Route::get('/', [WarehouseController::class, 'index']);
        Route::post('/', [WarehouseController::class, 'store']);
        Route::put('/{id}', [WarehouseController::class, 'update']);
        Route::delete('/{id}', [WarehouseController::class, 'softdelete']);
        Route::patch('/{id}/status', [WarehouseController::class, 'statusUpdate']);
        Route::get('/warehouse-product-stock', [WarehouseController::class, 'getProductStock']);
        Route::get('/stock-listing', [WarehouseController::class, 'stockListing']);
        Route::post('/stock-transfer', [WarehouseController::class, 'transferStore']);
        Route::get('/{id}/products', [WarehouseController::class, 'getWarehouseProducts']);
        Route::get('/export/pdf', [WarehouseController::class, 'warehousePdf']);
        Route::get('/export/csv', [WarehouseController::class, 'warehouseCsv']);
        Route::get('/{id}/products/export/pdf', [WarehouseController::class, 'warehouseproductPdf']);
        Route::get('/{id}/products/export/csv', [WarehouseController::class, 'warehouseproductCsv']);
        Route::get('/stock-listing/export/pdf', [WarehouseController::class, 'exportstocklistingPdf']);
        Route::get('/stock-listing/export/csv', [WarehouseController::class, 'exportstocklistingCsv']);
        Route::get('/{id}', [WarehouseController::class, 'show']);
});

Route::prefix('inventory')->middleware(['api.request', 'auth:api'])->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/export-pdf', [InventoryController::class, 'exportPdf']);
        Route::get('/export-csv', [InventoryController::class, 'exportCsv']);
});

Route::prefix('products')->middleware(['api.request', 'auth:api'])->group(function () {
        Route::get('/', [ProductController::class, 'index']);  // list 
        Route::get('edit/{id}', [ProductController::class, 'edit']);  // edit
        Route::put('update/{id}', [ProductController::class, 'update']);  // update
        Route::post('/', [ProductController::class, 'store']); // store
        Route::get('/{id}', [ProductController::class, 'show']);  // show
        Route::delete('/{id}', [ProductController::class, 'destroy']);  // delete
        Route::delete('/{id}/soft-delete', [ProductController::class, 'softdelete']);  // soft delete
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/last-price', [ProductController::class, 'getLastPrice']);
        Route::get('/export/pdf', [ProductController::class, 'exportPdf']);
        Route::get('/export/csv', [ProductController::class, 'exportCsv']);
});
