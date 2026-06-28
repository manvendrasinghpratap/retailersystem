<?php

namespace App\Services;

use App\Models\AccountSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AccountSettingService
{
    /**
     * Store settings in memory for current request.
     */
    protected static array $loadedSettings = [];

    /**
     * Get current account id.
     */
    protected function accountId(): int
    {
        return Auth::user()->account_id;
    }

    /**
     * Load all settings.
     */
    protected function load(): array
    {
        $accountId = $this->accountId();

        // Already loaded during this request
        if (isset(self::$loadedSettings[$accountId])) {
            return self::$loadedSettings[$accountId];
        }

        // Cache
        self::$loadedSettings[$accountId] = Cache::rememberForever(
            "account_settings_{$accountId}",
            function () use ($accountId) {

                return AccountSetting::where('account_id', $accountId)
                    ->get()
                    ->mapWithKeys(function ($row) {
                        return [
                            $row->module => $row->settings,
                        ];
                    })
                    ->toArray();
            }
        );

        return self::$loadedSettings[$accountId];
    }

    /**
     * Get one setting.
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->load(), $key, $default);
    }

    /**
     * Get all settings.
     */
    public function all(): array
    {
        return $this->load();
    }

    /**
     * Clear cache.
     */
    public function clearCache(?int $accountId = null): void
    {
        $accountId ??= $this->accountId();

        Cache::forget("account_settings_{$accountId}");

        unset(self::$loadedSettings[$accountId]);
    }
}