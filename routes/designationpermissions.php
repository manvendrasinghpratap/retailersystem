<?php
use App\Http\Controllers\DesignationPermissionController;
Route::prefix('designations')
    ->name('designations.')
    ->group(function () {

        Route::get(
            '/{designation}/permissions',
            [
                DesignationPermissionController::class,
                'edit',
            ]
        )->name('permissions.edit');

        Route::put(
            '/{designation}/permissions',
            [
                DesignationPermissionController::class,
                'update',
            ]
        )->name('permissions.update');

    });