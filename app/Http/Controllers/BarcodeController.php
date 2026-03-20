<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Optional: import the facades if you plan to use them directly in the controller
// use Milon\Barcode\Facades\DNS1D; 

class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcode');
    }
}
