<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;


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




// Route::get('/me-test', function (Request $request) { // return response()->json([ // 'server_authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? null, // 'request_authorization' => $request->header('Authorization'), // 'bearer_token' => $request->bearerToken(), // 'redirect_http_authorization' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null, // ]); // });
