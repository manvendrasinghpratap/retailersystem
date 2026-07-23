<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    BarcodeController,
    SaleController,
    ReportController
};
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Admin\{
    DashboardController,
    StaffController,
    VendorController,
    PurchaseController,
    StockReturnController,
    RequisitionController,
    PurchaseReturnController
};

Route::get('/updateapp', function () {
    \Artisan::call('key:generate');
    \Artisan::call('config:cache');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('optimize:clear');
    echo 'dump-autoload complete';
});
Route::get('/composerautofix', function () {

    \Artisan::call('key:generate');
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('optimize:clear');

    // ✅ Run composer dump-autoload
    try {
        exec('composer dump-autoload 2>&1', $output, $resultCode);
    } catch (\Exception $e) {
        $output = ['Composer execution failed'];
    }

    return response()->json([
        'status' => 'success',
        'composer_output' => $output
    ]);
});
Route::get('syncroutes', function () {
    \Artisan::call('sync:routes');
    echo 'routes synced';
});
Route::get('admin/acl', [\App\Http\Controllers\Administrator\AclController::class, 'index'])->name('acl');
// Mail::raw('Test email', function ($message) {
//     $message->to('m8005029425@gmail.com')
//         ->subject('Test Mail');
// });

Route::get('/generate-barcode', [BarcodeController::class, 'index']);
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'subscription'])->get('admin', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth', 'route.permission', 'subscription'])->prefix('admin/staff')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->name('admin.staff');
    Route::get('/index', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/add', [StaffController::class, 'create'])->name('admin.staff.add');
    Route::post('/store', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::get('/edit/{id}', [StaffController::class, 'editstaff'])->name('admin.staff.edit');
    Route::post('/update', [StaffController::class, 'update'])->name('admin.staff.update');
    Route::post('/updatepassword', [StaffController::class, 'updatepassword'])->name('admin.staff.updatepassword');
    Route::get('/exportpdf', [StaffController::class, 'exportPdf'])->name('staff.pdf');
    Route::get('/exportcsv', [StaffController::class, 'exportCsv'])->name('staff.csv');
});

Route::middleware(['auth', 'route.permission', 'subscription'])->prefix('admin/members')->group(function () {
    Route::post('/destroy', [StaffController::class, 'delete'])->name('destroy');
    Route::post('/status-update', [StaffController::class, 'statusUpdate'])->name('statusUpdate');
});


Route::middleware(['route.permission', 'auth', 'subscription'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('barcode/scan-product', [BarcodeController::class, 'index'])->name('barcode.scan.product');
    Route::get('barcode/{id}', [BarcodeController::class, 'barcodeForm'])->name('barcode.form');
    Route::post('barcode/print', [BarcodeController::class, 'barcodePrint'])->name('barcode.print');
});

//  Route::get('/profile', [App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('profile');
Route::post('update-password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('update-password');

Route::middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('/create/transaction', [BillingController::class, 'index'])->name('billing.index');
    Route::get('credit-duration/{id}', [BillingController::class, 'getCreditDuration'])->name('credit.duration');
    Route::post('/billing/scan', [BillingController::class, 'scanProduct'])->name('billing.scan');
    Route::post('/billing/complete', [BillingController::class, 'completeSale'])->name('billing.complete');
});
Route::middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('/sales', [SaleController::class, 'index'])->name('admin.sales.index');
    Route::get('/sales/export-pdf', [SaleController::class, 'exportPdf'])->name('admin.sales.exportPdf');
    Route::get('/sales/export-csv', [SaleController::class, 'exportCsv'])->name('admin.sales.exportCsv');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('admin.sales.show');
    Route::get('/sales/{sale}/payment-details', [SaleController::class, 'paymentDetails'])->name('admin.sales.payment-details');
    Route::post('/sales/save-credit-payment', [SaleController::class, 'saveCreditPayment'])->name('admin.sales.save-credit-payment');
});
Route::middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::get('admin/print/invoice/{id}', [SaleController::class, 'printinvoice'])->name('printinvoice');
    Route::post('admin/send-invoice-email', [SaleController::class, 'sendInvoiceEmail'])->name('sendinvoice');
    Route::get('admin/download-invoice/{id}', [SaleController::class, 'downloadInvoice'])->name('downloadinvoice');
});
Route::middleware(['auth', 'subscription'])->group(function () {
    Route::get('admin/sync-routes', [\App\Http\Controllers\Administrator\AclController::class, 'syncRoutes'])->name('syncroutes');
    Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily.sales');
    Route::get('/reports/daily-sales/pdf', [ReportController::class, 'dailySalesPdf'])->name('reports.daily.sales.pdf');
    Route::get('/reports/daily-sales/csv', [ReportController::class, 'dailySalesCsv'])->name('reports.daily.sales.csv');
});


Route::middleware(['auth', 'route.permission', 'subscription'])->prefix('admin')->group(function () {
    Route::get('/vendors', [VendorController::class, 'index'])->name('admin.vendors.index');
    Route::get('/vendors/create', [VendorController::class, 'create'])->name('admin.vendors.create');
    Route::post('/vendors/store', [VendorController::class, 'store'])->name('admin.vendors.store');
    Route::get('/vendors/edit/{id}', [VendorController::class, 'edit'])->name('admin.vendors.edit');
    Route::post('/vendors/update', [VendorController::class, 'update'])->name('admin.vendors.update');
    Route::post('/vendors/delete', [VendorController::class, 'softdelete'])->name('admin.vendors.delete');
    Route::post('/vendors/status-update', [VendorController::class, 'statusUpdate'])->name('admin.vendors.statusUpdate');
    Route::get('/vendors/export-pdf', [VendorController::class, 'exportPdf'])->name('admin.vendors.exportPdf');
    Route::get('/vendors/export-csv', [VendorController::class, 'exportExcel'])->name('admin.vendors.exportCsv');

    /*
        |--------------------------------------------------------------------------
        | Vendor Payment Routes
        |--------------------------------------------------------------------------
        */

    // Payment form
    Route::get('/vendors/payment/{id}', [VendorController::class, 'paymentForm'])
        ->name('admin.vendors.paymentForm');

    // Save payment
    Route::post('/vendors/payment/store', [VendorController::class, 'paymentStore'])
        ->name('admin.vendors.paymentStore');

    /*
    |--------------------------------------------------------------------------
    | Vendor Ledger / Statement
    |--------------------------------------------------------------------------
    */

    Route::get('/vendors/ledger/{id}', [VendorController::class, 'ledger'])->name('admin.vendors.ledger');
    Route::get('/vendors/ledger/{id}/export-pdf', [VendorController::class, 'ledgerExportPdf'])->name('admin.vendors.ledger.pdf');
    Route::get('/vendors/ledger/{id}/export-csv', [VendorController::class, 'ledgerExportCsv'])->name('admin.vendors.ledger.csv');
});


Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('purchases')->name('purchases.')->group(function () {
        // List all purchases
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        // Create purchase form
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        // Store purchase
        Route::post('/store', [PurchaseController::class, 'store'])->name('store');
        // View purchase
        Route::get('/view/{id}', [PurchaseController::class, 'show'])->name('view');
        // Cancel purchase
        Route::post('/cancel', [PurchaseController::class, 'destroy'])->name('cancel');
        // Soft delete purchase
        Route::post('/softdelete', [PurchaseController::class, 'softdelete'])->name('softdelete');
        // Update status
        Route::post('/status-update', [PurchaseController::class, 'statusUpdate'])->name('status.update');
        // Export PDF
        Route::get('/exportpdf', [PurchaseController::class, 'exportPdf'])->name('exportPdf');
        // Export CSV
        Route::get('/exportcsv', [PurchaseController::class, 'exportCsv'])->name('exportCsv');
        // Ajax view
        Route::get('/view/ajax/{id}', [PurchaseController::class, 'viewAjax'])->name('view.ajax');
    });

    // Purchase Return
    Route::prefix('purchase-returns')->name('admin.purchase_returns.')->group(function () {
        Route::get('/create/{id}', [PurchaseReturnController::class, 'create'])->name('create');
        Route::post('/store', [PurchaseReturnController::class, 'store'])->name('store');
    });


    Route::prefix('stock-returns')->name('stock_returns.')->group(function () {
        Route::get('/', [StockReturnController::class, 'index'])->name('index');
        Route::get('/create', [StockReturnController::class, 'create'])->name('create');
        Route::post('/store', [StockReturnController::class, 'store'])->name('store');
        Route::get('/view/ajax/{id}', [StockReturnController::class, 'viewAjax'])->name('view.ajax');
        Route::get('/view/ajax/pdf/{id}', [StockReturnController::class, 'viewAjaxPdf'])->name('view.ajax.pdf');
        Route::get('/show/{id}', [StockReturnController::class, 'show'])->name('show');
        Route::get('/stock-check', [StockReturnController::class, 'getStock'])->name('stock.check');
        Route::post('/cancel', [StockReturnController::class, 'cancel'])->name('cancel');
        Route::get('/exportpdf', [StockReturnController::class, 'exportPdf'])->name('exportpdf');
        Route::get('/exportcsv', [StockReturnController::class, 'exportCsv'])->name('exportcsv');
    });

    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [RequisitionController::class, 'index'])->name('index');
        Route::get('/create', [RequisitionController::class, 'create'])->name('create');
        Route::post('/store', [RequisitionController::class, 'store'])->name('store');
        Route::get('/view/{id}', [RequisitionController::class, 'show'])->name('view');
        Route::post('/cancel', [RequisitionController::class, 'cancel'])->name('cancel');
        Route::get('/requisition-products', [RequisitionController::class, 'requisitionProducts'])->name('requisition.products');
        Route::post('/complete', [RequisitionController::class, 'complete'])->name('complete');
        Route::get('/exportpdf', [RequisitionController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/exportcsv', [RequisitionController::class, 'exportCsv'])->name('exportCsv');
        Route::get('/pdf/{id}', [RequisitionController::class, 'pdf'])->name('pdf');
        Route::get('/csv/{id}', [RequisitionController::class, 'csv'])->name('csv');
        Route::get('/view/ajax/{id}', [RequisitionController::class, 'viewAjax'])->name('view.ajax');
        Route::get('/view/ajax/pdf/{id}', [RequisitionController::class, 'viewAjaxPdf'])->name('view.ajax.pdf');
        Route::get('/pending-posting', [RequisitionController::class, 'pendingPosting'])->name('pending.posting');
        Route::post('/cancel-item', [RequisitionController::class, 'cancelItem'])->name('cancel.item');

    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/administrator.php';
require __DIR__ . '/acl.php';
require __DIR__ . '/attendance.php';
require __DIR__ . '/warehouse.php';
require __DIR__ . '/masteritem.php';
require __DIR__ . '/sessionout.php';


