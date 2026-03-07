<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\{
    DashboardController,
    CategoryController,
    ProductController,
    OrderController
};
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {return redirect()->route('login');})->name('home');
Route::get('/', function () {return view('welcome');})->name('home');

Route::middleware(['auth'])->get('admin',[App\Http\Controllers\Admin\DashboardController::class,'root'])->name('dashboard');
Route::middleware(['auth'])->prefix('admin/staff')->group(function () {    
        Route::get('/', [StaffController::class, 'index'])->name('staff');
        Route::get('/add', [StaffController::class, 'create'])->name('staff.add');
        Route::post('/store', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/edit/{id}', [StaffController::class, 'editstaff'])->name('staff.edit');
        Route::post('/update', [StaffController::class, 'update'])->name('staff.update');
        Route::post('/updatepassword', [StaffController::class, 'updatepassword'])->name('staff.updatepassword');
        Route::get('/downloadstaffpdf', [StaffController::class, 'downloadstaffpdf'])->name('downloadstaffpdf');
});

Route::middleware(['auth'])->prefix('admin/members')->group(function () {
        Route::post('/destroy', [StaffController::class, 'delete'])->name('destroy');
        Route::post('/status-update', [StaffController::class, 'statusUpdate'])->name('statusUpdate');
    });


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';