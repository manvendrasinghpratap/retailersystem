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
    CustomerController,
    StoreController,
    CreditDurationController,
    PaymentTypeController,
    AccountSettingController
};

Route::prefix('admin')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/graph', [DashboardController::class, 'graph'])->name('graph');
    Route::get('change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'editPassword'])->name('admin.change-password');
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'editprofile'])->name('admin.profile');
    Route::post('/updateprofile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('update.profile');
});


Route::prefix('admin')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    // Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories/store', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::post('categories/update', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::post('categories/delete', [CategoryController::class, 'softdelete'])->name('admin.categories.delete');
    Route::post('categories/status', [CategoryController::class, 'statusUpdate'])->name('admin.categories.statusUpdate');
    Route::post('categories/softdelete', [CategoryController::class, 'softdelete'])->name('admin.categories.softdelete');
    Route::get('categories/export-pdf', [CategoryController::class, 'exportPdf'])->name('admin.category.exportPdf');
    Route::get('categories/export-csv', [CategoryController::class, 'exportCsv'])->name('admin.category.exportCsv');
});

Route::prefix('admin/products')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('admin.products');
    Route::get('create/{token?}', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('store', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::post('update', [ProductController::class, 'update'])->name('admin.products.update');
    Route::post('products/delete', [ProductController::class, 'destroy'])->name('products.delete');
    Route::post('products/softdelete', [ProductController::class, 'softdelete'])->name('admin.products.softdelete');
    Route::get('pdf', [ProductController::class, 'exportPdf'])->name('admin.products.pdf');
    Route::get('csv', [ProductController::class, 'exportCsv'])->name('admin.products.csv');
    Route::get('last-price', [ProductController::class, 'getLastPrice'])->name('admin.products.lastPrice');
    Route::get('search', [ProductController::class, 'search'])->name('admin.products.search');
});

Route::prefix('admin')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::post('modifiers/store', [ProductModifierController::class, 'store'])->name('admin.modifiers.store');
    Route::get('inventory', [InventoryController::class, 'index'])->name('admin.inventory');
    Route::get('inventory/manage/{id?}', [InventoryController::class, 'create'])->name('admin.inventory.manage');
    Route::get('inventory/manage/update/{token}', [InventoryController::class, 'update'])->name('admin.inventory.update');
    Route::post('stock-adjust', [StockAdjustmentController::class, 'store'])->name('admin.stock.adjust');
    Route::get('inventory/export-pdf', [InventoryController::class, 'exportPdf'])->name('admin.inventory.exportPdf');
    Route::get('inventory/export-csv', [InventoryController::class, 'exportCsv'])->name('admin.inventory.exportCsv');
});

Route::prefix('admin/barcode')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('/', [BarcodeController::class, 'index'])->name('admin.barcode');
    Route::get('/sales-barcode', [BarcodeController::class, 'salesBarcode'])->name('admin.sales-barcode');
    Route::get('/return-barcode', [BarcodeController::class, 'returnBarcode'])->name('admin.return-barcode');
    Route::get('/damage-barcode', [BarcodeController::class, 'damageBarcode'])->name('admin.damage-barcode');
    Route::get('/deduct-barcode', [BarcodeController::class, 'deductBarcode'])->name('admin.deduct-barcode');
    Route::get('/no-barcode', [BarcodeController::class, 'nobarcode'])->name('admin.no-barcode');
    Route::post('/validateBarcode', [BarcodeController::class, 'validateBarcode'])->name('admin.barcode.validateBarcode');
    Route::post('/validateBarcodeRequisitionId', [BarcodeController::class, 'validateBarcodeRequisitionId'])->name('admin.barcode.validateBarcodeRequisitionId');
    Route::post('/purchase/validate-barcode', [BarcodeController::class, 'validatePurchaseBarcode'])->name('admin.purchase.validateBarcode');
});

Route::prefix('admin')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('coupons/store', [CouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('coupons/edit/{id}', [CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::post('coupons/update', [CouponController::class, 'update'])->name('admin.coupons.update');
    Route::post('coupons/delete', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');
    Route::post('coupons/status-update', [CouponController::class, 'statusUpdate'])->name('admin.coupons.status');
    Route::post('coupons/soft-delete', [CouponController::class, 'softdelete'])->name('admin.coupons.softdelete');
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
    Route::get('coupons/export-pdf', [CouponController::class, 'exportPdf'])->name('admin.coupons.exportPdf');
    Route::get('coupons/export-csv', [CouponController::class, 'exportCsv'])->name('admin.coupons.exportCsv');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
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
    Route::get('customers/export-pdf', [CustomerController::class, 'exportPdf'])->name('customers.exportPdf');
    Route::get('customers/export-csv', [CustomerController::class, 'exportCsv'])->name('customers.exportCsv');
});


Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('stores')->name('stores.')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::get('/create', [StoreController::class, 'create'])->name('create');
        Route::post('/store', [StoreController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [StoreController::class, 'edit'])->name('edit');
        Route::post('/update', [StoreController::class, 'update'])->name('update');
        Route::post('/delete', [StoreController::class, 'destroy'])->name('destroy');
        Route::post('/status-update', [StoreController::class, 'statusUpdate'])->name('status.update');
        Route::post('/soft-delete', [StoreController::class, 'softdelete'])->name('soft.delete');
        Route::get('export-pdf', [StoreController::class, 'exportPdf'])->name('exportPdf');
        Route::get('export-csv', [StoreController::class, 'exportCsv'])->name('exportCsv');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('credit-durations')->name('credit-durations.')->group(function () {
        Route::get('/', [CreditDurationController::class, 'index'])->name('index');
        Route::get('/create', [CreditDurationController::class, 'create'])->name('create');
        Route::post('/store', [CreditDurationController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [CreditDurationController::class, 'edit'])->name('edit');
        Route::post('/update', [CreditDurationController::class, 'update'])->name('update');
        Route::post('/delete', [CreditDurationController::class, 'destroy'])->name('destroy');
        Route::post('/status-update', [CreditDurationController::class, 'statusUpdate'])->name('status.update');
        Route::post('/soft-delete', [CreditDurationController::class, 'softdelete'])->name('soft.delete');
        Route::get('export-pdf', [CreditDurationController::class, 'exportPdf'])->name('exportPdf');
        Route::get('export-csv', [CreditDurationController::class, 'exportCsv'])->name('exportCsv');
    });
});

/*
|--------------------------------------------------------------------------
| Payment Types
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('payment-types')->name('payment-types.')->group(function () {
        Route::get('/', [PaymentTypeController::class, 'index'])->name('index');
        Route::get('/create', [PaymentTypeController::class, 'create'])->name('create');
        Route::post('/store', [PaymentTypeController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [PaymentTypeController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [PaymentTypeController::class, 'update'])->name('update');
        Route::post('/softdelete/{id}', [PaymentTypeController::class, 'softdelete'])->name('softdelete');
        Route::post('/status-update', [PaymentTypeController::class, 'statusUpdate'])->name('statusUpdate');
        Route::get('/export-pdf', [PaymentTypeController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/export-csv', [PaymentTypeController::class, 'exportCsv'])->name('exportCsv');
    });
});

/*
|--------------------------------------------------------------------------
| Account Settings
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('account-settings')->name('account-settings.')->group(function () {
        Route::get('/', [AccountSettingController::class, 'index'])->name('index');
        Route::get('/create', [AccountSettingController::class, 'create'])->name('create');
        Route::post('/', [AccountSettingController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AccountSettingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountSettingController::class, 'update'])->name('update');
    });
});

