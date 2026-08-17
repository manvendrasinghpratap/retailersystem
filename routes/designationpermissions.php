<?php
use App\Http\Controllers\DesignationPermissionController;
Route::prefix('designations')->middleware(['auth', 'route.permission', 'subscription'])->name('designations.')->group(function () {
    Route::get('/{designation}/permissions', [DesignationPermissionController::class, 'edit'])->middleware('permission:designations.edit')->name('permissions.edit');
    Route::put('/{designation}/permissions', [DesignationPermissionController::class, 'update'])->middleware('permission:designations.update')->name('permissions.update');
});