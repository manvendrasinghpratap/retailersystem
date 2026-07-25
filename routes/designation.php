<?php
use App\Http\Controllers\Admin\DesignationController;
Route::prefix('admin')->name('admin.')->middleware(['auth', 'route.permission', 'subscription'])->group(function () {
    Route::prefix('designations')->name('designations.')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('index');
        Route::get('/create', [DesignationController::class, 'create'])->name('create');
        Route::post('/store', [DesignationController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [DesignationController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [DesignationController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [DesignationController::class, 'destroy'])->name('destroy');
        Route::get('/export/pdf', [DesignationController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/csv', [DesignationController::class, 'exportCsv'])->name('export.csv');
        Route::post('/softdelete', [DesignationController::class, 'softdelete'])->name('softdelete');
    });
});