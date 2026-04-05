<?php

use App\Http\Controllers\Administrator\AclController;
Route::prefix('administrator')->middleware('auth')->group(function () {
    Route::get('/acl', [AclController::class, 'index'])->name('administrator.acl');
    Route::get('acl/sync', [AclController::class, 'sync'])->name('administrator.acl.sync');
    Route::post('acl/update', [AclController::class, 'update'])->name('administrator.acl.update');
});
