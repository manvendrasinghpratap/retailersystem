<?php

use App\Http\Controllers\Admin\{
    DashboardController,  
};

Route::prefix('admin')->middleware('auth')->group(function(){
    Route::get('/dashboard',[DashboardController::class,'index'])->name('admin.dashboard');
    Route::get('change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'editPassword'])->name('admin.change-password');
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'editprofile'])->name('admin.profile');
    Route::post('/updateprofile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('update.profile');
});
