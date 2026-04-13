<?php

use App\Http\Controllers\Admin\{
    DashboardController,
    CategoryController,
    ProductController,
    ProductModifierController,
    InventoryController,
    StockAdjustmentController,
    BarcodeController,
    CouponController,
    CustomerController
};

Route::prefix('admin')->middleware(['auth', 'route.permission'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'editPassword'])->name('admin.change-password');
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'editprofile'])->name('admin.profile');
    Route::post('/updateprofile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('update.profile');
});


Route::prefix('admin')->middleware(['auth', 'route.permission'])->group(function () {
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

Route::prefix('admin/products')->middleware(['auth', 'route.permission'])->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('admin.products');
    Route::get('create/{token?}', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('store', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::post('update', [ProductController::class, 'update'])->name('admin.products.update');
    Route::post('products/delete', [ProductController::class, 'destroy'])->name('products.delete');
    Route::post('products/softdelete', [ProductController::class, 'softdelete'])->name('admin.products.softdelete');
});

Route::prefix('admin')->middleware(['auth', 'route.permission'])->group(function () {
    Route::post('modifiers/store', [ProductModifierController::class, 'store'])->name('admin.modifiers.store');
    Route::get('inventory', [InventoryController::class, 'index'])->name('admin.inventory');
    Route::get('inventory/manage/{id?}', [InventoryController::class, 'create'])->name('admin.inventory.manage');
    Route::get('inventory/manage/update/{token}', [InventoryController::class, 'update'])->name('admin.inventory.update');
    Route::post('stock-adjust', [StockAdjustmentController::class, 'store'])->name('admin.stock.adjust');
});

Route::prefix('admin/barcode')->middleware(['auth', 'route.permission'])->group(function () {
    Route::get('/', [BarcodeController::class, 'index'])->name('admin.barcode');
    Route::get('/no-barcode', [BarcodeController::class, 'nobarcode'])->name('admin.no-barcode');
    Route::get('/sales-barcode', [BarcodeController::class, 'index'])->name('admin.sales-barcode');
    Route::get('/return-barcode', [BarcodeController::class, 'index'])->name('admin.return-barcode');
    Route::get('/damage-barcode', [BarcodeController::class, 'index'])->name('admin.damage-barcode');
    Route::get('/deduct-barcode', [BarcodeController::class, 'index'])->name('admin.deduct-barcode');
    Route::post('/validateBarcode', [BarcodeController::class, 'validateBarcode'])->name('admin.barcode.validateBarcode');
});

Route::prefix('admin')->middleware(['auth', 'route.permission'])->group(function () {
    Route::get('coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('coupons/store', [CouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('coupons/edit/{id}', [CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::post('coupons/update', [CouponController::class, 'update'])->name('admin.coupons.update');
    Route::post('coupons/delete', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');
    Route::post('coupons/status-update', [CouponController::class, 'statusUpdate'])->name('admin.coupons.status');
    Route::post('coupons/soft-delete', [CouponController::class, 'softdelete'])->name('admin.coupons.softdelete');
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/edit/{id}', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/update', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('customers/delete', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('customers/soft-delete', [CustomerController::class, 'softdelete'])->name('customers.softdelete');
    Route::post('customers/status-update', [CustomerController::class, 'statusUpdate'])->name('customers.status');
    Route::post('customers/find-by-phone', [CustomerController::class, 'findByPhone'])->name('customers.findByPhone');
    Route::post('customers/quick-store', [CustomerController::class, 'quickStore'])->name('customers.quickStore');
    Route::post('customers/update-by-phone', [CustomerController::class, 'updateByPhone'])->name('customers.updateByPhone');
});


