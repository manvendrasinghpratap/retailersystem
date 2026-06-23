<?php

use App\Http\Controllers\Admin\{
    MasterItemController,
};

Route::prefix('admin/master-items')->middleware(['auth', 'subscription'])->group(function () {
    Route::get('/', [MasterItemController::class, 'index'])->name('admin.master_items.index');
    Route::get('/create', [MasterItemController::class, 'create'])->name('admin.master_items.create');
    Route::post('/store', [MasterItemController::class, 'store'])->name('admin.master_items.store');
    Route::get('/edit/{id}', [MasterItemController::class, 'edit'])->name('admin.master_items.edit');
    Route::post('/update', [MasterItemController::class, 'update'])->name('admin.master_items.update');
    Route::post('/delete', [MasterItemController::class, 'delete'])->name('admin.master_items.delete');
    Route::post('/status-update', [MasterItemController::class, 'statusUpdate'])->name('admin.master_items.status');
    Route::get('/export-pdf', [MasterItemController::class, 'exportPdf'])->name('admin.master_items.exportPdf');
    Route::get('/export-csv', [MasterItemController::class, 'exportCsv'])->name('admin.master_items.exportCsv');
    Route::get('search', [MasterItemController::class, 'search'])->name('admin.master_items.search');
    Route::post('store/ajax', [MasterItemController::class, 'storeAjax'])->name('admin.master_items.store.ajax');
});



