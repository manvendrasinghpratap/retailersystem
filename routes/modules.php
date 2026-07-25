<?php
use App\Http\Controllers\Admin\ModuleController;
Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/create', [ModuleController::class, 'create'])->name('create');
        Route::post('/store', [ModuleController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ModuleController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ModuleController::class, 'update'])->name('update');
        Route::post('/softdelete', [ModuleController::class, 'softdelete'])->name('softdelete');
        Route::post('/status-update', [ModuleController::class, 'statusUpdate'])->name('statusUpdate');
        Route::get('/exportpdf', [ModuleController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/exportcsv', [ModuleController::class, 'exportCsv'])->name('exportCsv');
    });
});

