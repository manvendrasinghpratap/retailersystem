<?php
use App\Http\Controllers\SessionTimeoutController;

Route::middleware('auth')->group(function () {

    Route::post('/session/keep-alive', [
        SessionTimeoutController::class,
        'keepAlive'
    ])->name('session.keepalive');

    Route::post('/session/logout', [
        SessionTimeoutController::class,
        'logout'
    ])->name('session.logout');

});