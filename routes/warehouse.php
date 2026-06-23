<?php

use App\Http\Controllers\Admin\WarehouseController;

Route::middleware(['auth', 'route.permission', 'subscription'])->prefix('admin/warehouses')->name('admin.warehouses.')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('index');
    Route::get('/create', [WarehouseController::class, 'create'])->name('create');
    Route::post('/store', [WarehouseController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [WarehouseController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [WarehouseController::class, 'update'])->name('update');
    Route::post('/softdelete/{id}', [WarehouseController::class, 'softdelete'])->name('softdelete');
    Route::post('/status-update', [WarehouseController::class, 'statusUpdate'])->name('statusUpdate');

    Route::get('/stock-transfer/create', [WarehouseController::class, 'transferForm'])->name('stockTransfer.create');
    Route::post('/stock-transfer', [WarehouseController::class, 'transferStore'])->name('stockTransfer.store');
    Route::get('/stock-transfer/export-pdf', [WarehouseController::class, 'exportPdf'])->name('exportPdf');
    Route::get('/stock-transfer/export-csv', [WarehouseController::class, 'exportCsv'])->name('exportCsv');
    Route::get('/{id}/products', [WarehouseController::class, 'getWarehouseProducts'])->name('products');
    Route::get('/warehouse-product-stock', [WarehouseController::class, 'getProductStock'])->name('warehouse.product.stock');
    Route::get('/stock-listing', [WarehouseController::class, 'stockListing'])->name('stock.listing');
    // EXPORT PDF
    Route::get('/stock-listing/export/pdf', [WarehouseController::class, 'stockListingPdf'])->name('stock.listing.pdf');
    // EXPORT CSV
    Route::get('/stock-listing/export/csv', [WarehouseController::class, 'stockListingCsv'])->name('stock.listing.csv');
});