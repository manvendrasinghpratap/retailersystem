<?php

use App\Services\AccountSettingService;

if (!function_exists('account_setting')) {

    function account_setting($key = null, $default = null)
    {
        $service = app(AccountSettingService::class);

        if ($key === null) {
            return $service->all();
        }

        return $service->get($key, $default);
    }
}