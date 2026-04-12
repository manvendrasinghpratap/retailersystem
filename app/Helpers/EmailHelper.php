<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

if (!function_exists('sendCustomerEmail')) {

    function sendCustomerEmail($to, $subject, $view, $data = [])
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Email Error: ' . $e->getMessage());
            return false;
        }
    }
}