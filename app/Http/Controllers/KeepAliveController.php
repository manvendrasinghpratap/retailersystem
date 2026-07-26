<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KeepAliveController extends Controller
{
    public function __invoke(): JsonResponse
    {
        session()->put('keep_alive', now());

        return response()->json([
            'success' => true,
            'time' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
