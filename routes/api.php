<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public login route
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes protected by JWT
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // Example protected retail routes
    Route::middleware('role:cashier,manager')->group(function () {
        // Route::post('/checkout', [SaleController::class, 'processCheckout']);
    });

    Route::middleware('role:manager')->group(function () {
        // Route::post('/sales/{id}/void', [SaleController::class, 'voidSale']);
    });
});