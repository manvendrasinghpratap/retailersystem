<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException; // Import this
use Illuminate\Http\Request; // Import this

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle the 419 Page Expired error
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return redirect()->route('login')
                ->with('status', 'Your session expired due to inactivity. Please log in again.');
        });
    })->create();