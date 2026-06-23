<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\AccountSubscription;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark expired subscriptions';

    public function handle()
    {
        $subscriptions = AccountSubscription::where('is_expired', 0)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->get();

        $count = 0;

        foreach ($subscriptions as $subscription) {

            $subscription->update([
                'is_expired' => 1,
                'status' => 0,
            ]);

            Account::where('id', $subscription->account_id)
                ->update([
                    'status' => 0,
                ]);

            $count++;
        }

        $this->info("{$count} subscriptions expired successfully.");

        return self::SUCCESS;
    }
}