<?php
use App\Http\Controllers\Admin\SaleReturnController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::prefix('sales-return')->name('sales-return.')->group(function () {
        Route::get('/', [SaleReturnController::class, 'index'])->name('index');
        Route::get('/create', [SaleReturnController::class, 'create'])->name('create');
        Route::post('/store', [SaleReturnController::class, 'store'])->name('store');
        Route::get('/sale-details', [SaleReturnController::class, 'saleDetails'])->name('sale-details');
    });
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('sale-returns', [SaleReturnController::class, 'index'])->name('admin.sale-returns');
    Route::get('sale-returns/create', [SaleReturnController::class, 'create'])->name('admin.sale-returns.create');
    Route::post('sale-returns/search-invoice', [SaleReturnController::class, 'searchInvoice'])->name('admin.sale-returns.search-invoice');
    Route::post('sale-returns/find-barcode', [SaleReturnController::class, 'findBarcode'])->name('admin.sale-returns.find-barcode');
    Route::post('sale-returns/store', [SaleReturnController::class, 'store'])->name('admin.sale-returns.store');
    Route::get('sale-returns/{id}', [SaleReturnController::class, 'show'])->name('admin.sale-returns.show');
    Route::get('sales-return/scan-barcode', [SaleReturnController::class, 'scanBarcode'])->name('admin.sales-return.scan-barcode');
    Route::post('sales-return/assign-customer', [SaleReturnController::class, 'assignCustomer'])->name('admin.sales-return.assign-customer');
});


