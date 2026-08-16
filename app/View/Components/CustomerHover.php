<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class CustomerHover extends Component
{
    public function __construct(
        public $customer
    ) {
    }

    public function render(): View
    {
        return view('components.customer-hover');
    }
}