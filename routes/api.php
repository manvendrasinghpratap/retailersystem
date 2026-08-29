<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RequisitionController;
/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->middleware('api.request')->group(function () {
        // Login does not require JWT
        Route::post('/login', [AuthController::class, 'login']);
        // JWT protected routes
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });


/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Requires:
| 1. X-API-Key
| 2. Valid JWT Bearer token
|
*/

Route::prefix('dashboard')->middleware(['api.request', 'auth:api'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
});


Route::prefix('reports')->middleware(['api.request', 'auth:api'])->group(function () {
    Route::get('daily-sales', [ReportController::class, 'dailySales']);
});

Route::prefix('requisitions')->middleware(['api.request', 'auth:api'])->group(function () {
        // Requisition List
        Route::get('/', [RequisitionController::class, 'index']);   

        // Create Requisition
        Route::post('/', [RequisitionController::class, 'store']);

        // Cancel Requisition
        Route::post('/cancel', [RequisitionController::class, 'cancel']);

        // Requisition Products
        Route::get('/requisition-products', [RequisitionController::class, 'requisitionProducts']);

        // Complete Requisition
        Route::post('/complete', [RequisitionController::class, 'complete']);

        // Pending Posting
        Route::get('/pending-posting', [RequisitionController::class, 'pendingPosting']);   

        // Pending Posting History
        Route::get('/pending-posting-history', [RequisitionController::class, 'pendingPostingHistory']);    

        // Pending Posting History Report
        Route::get('/pending-posting-history-report', [RequisitionController::class, 'pendingPostingHistoryReport']);

        // Cancel Requisition Item
        Route::post('/cancel-item', [RequisitionController::class, 'cancelItem']);

        // Validate Requisition Barcode
        Route::post('/validate-requisition-barcode', [RequisitionController::class, 'validateRequisitionBarcode']);

        // Search Barcode
        Route::post('/barcode/search', [RequisitionController::class, 'searchBarcode']);
    });
// Route::get('/me-test', function (Request $request) { // return response()->json([ // 'server_authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? null, // 'request_authorization' => $request->header('Authorization'), // 'bearer_token' => $request->bearerToken(), // 'redirect_http_authorization' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null, // ]); // });
