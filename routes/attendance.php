<?php

use App\Http\Controllers\Admin\AttendanceController;

/*
|--------------------------------------------------------------------------
| Attendance Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // ===============================
    // Attendance Main Page
    // ===============================
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // ===============================
    // Save Manual Attendance
    // ===============================
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    // ===============================
    // Punch In / Punch Out
    // ===============================
    Route::get('/attendance/punch-in', [AttendanceController::class, 'punchIn'])->name('attendance.punch.in');

    Route::post('/attendance/punch-out', [AttendanceController::class, 'punchOut'])->name('attendance.punch.out');

    // ===============================
    // Monthly Attendance Report
    // ===============================
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    // ===============================
    // Today Summary Ajax/API
    // ===============================
    Route::get('/attendance/today-summary', [AttendanceController::class, 'todaySummary'])->name('attendance.today.summary');

    // ===============================
    // Delete Record
    // ===============================
    Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

    // ===============================
    // Export PDF & CSV
    // ===============================
    Route::get('/attendance/report/pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.exportPdf');
    Route::get('/attendance/report/csv', [AttendanceController::class, 'exportCsv'])->name('attendance.exportCsv');
});