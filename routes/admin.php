<?php

use App\Http\Controllers\Admin\{
    DashboardController,
    CategoryController,
    ProductController,
    ProductModifierController,
    InventoryController,
    StockAdjustmentController,
    BarcodeController
};

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'editPassword'])->name('admin.change-password');
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'editprofile'])->name('admin.profile');
    Route::post('/updateprofile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('update.profile');
});


Route::prefix('admin')->middleware('auth')->group(function () {
    // Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories/store', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::post('categories/update', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::post('categories/delete', [CategoryController::class, 'softdelete'])->name('admin.categories.delete');
    Route::post('categories/status', [CategoryController::class, 'statusUpdate'])->name('admin.categories.statusUpdate');
    Route::post('categories/softdelete', [CategoryController::class, 'softdelete'])->name('admin.categories.softdelete');
});

Route::prefix('admin/products')->middleware('auth')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('admin.products');
    Route::get('create/{token?}', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('store', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::post('update', [ProductController::class, 'update'])->name('admin.products.update');
    Route::post('products/delete', [ProductController::class, 'destroy'])->name('products.delete');
    Route::post('products/softdelete', [ProductController::class, 'softdelete'])->name('admin.products.softdelete');
});

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::post('modifiers/store', [ProductModifierController::class, 'store'])->name('admin.modifiers.store');
    Route::get('inventory', [InventoryController::class, 'index'])->name('admin.inventory');
    Route::get('inventory/manage/{id?}', [InventoryController::class, 'create'])->name('admin.inventory.manage');
    Route::get('inventory/manage/update/{token}', [InventoryController::class, 'update'])->name('admin.inventory.update');
    Route::post('stock-adjust', [StockAdjustmentController::class, 'store'])->name('admin.stock.adjust');
});

Route::prefix('admin/barcode')->group(function () {
    Route::get('/', [BarcodeController::class, 'index'])->name('admin.barcode');
    Route::get('/no-barcode', [BarcodeController::class, 'nobarcode'])->name('admin.no-barcode');
    Route::get('/sales-barcode', [BarcodeController::class, 'index'])->name('admin.sales-barcode');
    Route::get('/return-barcode', [BarcodeController::class, 'index'])->name('admin.return-barcode');
    Route::get('/damage-barcode', [BarcodeController::class, 'index'])->name('admin.damage-barcode');
    Route::get('/deduct-barcode', [BarcodeController::class, 'index'])->name('admin.deduct-barcode');
    Route::post('/validateBarcode', [BarcodeController::class, 'validateBarcode'])->name('admin.barcode.validateBarcode');
});
