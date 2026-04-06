<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    BarcodeController,
    SaleController
};
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Admin\{
    DashboardController,
    StaffController
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
Route::get('syncroutes', function () {
    \Artisan::call('sync:routes');
    echo 'routes synced';
});
Route::get('admin/acl', [\App\Http\Controllers\Administrator\AclController::class, 'index'])->name('acl');
// Mail::raw('Test email', function ($message) {
//     $message->to('m8005029425@gmail.com')
//             ->subject('Test Mail');
// });

Route::get('/generate-barcode', [BarcodeController::class, 'index']);
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->get('admin', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth', 'route.permission'])->prefix('admin/staff')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->name('admin.staff');
    Route::get('/index', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/add', [StaffController::class, 'create'])->name('admin.staff.add');
    Route::post('/store', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::get('/edit/{id}', [StaffController::class, 'editstaff'])->name('admin.staff.edit');
    Route::post('/update', [StaffController::class, 'update'])->name('admin.staff.update');
    Route::post('/updatepassword', [StaffController::class, 'updatepassword'])->name('admin.staff.updatepassword');
    Route::get('/downloadstaffpdf', [StaffController::class, 'downloadstaffpdf'])->name('downloadstaffpdf');
});

Route::middleware(['auth', 'route.permission'])->prefix('admin/members')->group(function () {
    Route::post('/destroy', [StaffController::class, 'delete'])->name('destroy');
    Route::post('/status-update', [StaffController::class, 'statusUpdate'])->name('statusUpdate');
});


Route::middleware(['route.permission', 'auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'route.permission'])->group(function () {
    Route::get('/barcode-scan-product', [BarcodeController::class, 'index'])->name('barcode.scan.product');
    Route::post('/barcode-scan-inventory', [BarcodeController::class, 'scan'])->name('barcode.scan.inventory');
});

//  Route::get('/profile', [App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('profile');
Route::post('update-password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('update-password');

Route::middleware(['auth', 'route.permission'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/scan', [BillingController::class, 'scanProduct'])->name('billing.scan');
    Route::post('/billing/complete', [BillingController::class, 'completeSale'])->name('billing.complete');
});
Route::middleware(['auth', 'route.permission'])->group(function () {
    Route::get('/sales', [SaleController::class, 'index'])->name('admin.sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('admin.sales.show');
});
Route::middleware(['auth', 'route.permission'])->group(function () {
    Route::get('admin/print/invoice/{id}', [SaleController::class, 'printinvoice'])->name('printinvoice');
});
Route::middleware(['auth'])->group(function () {
    Route::get('admin/sync-routes', [\App\Http\Controllers\Administrator\AclController::class, 'syncRoutes'])->name('syncroutes');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/administrator.php';
require __DIR__ . '/acl.php';