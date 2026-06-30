<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Account;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Http\Request;

class StoreService
{
    /**
     * Create a store for an account.
     *
     * @param  Account  $account
     * @param  User     $manager
     * @param  Request  $request
     * @return Store
     */
    public function create(Account $account, User $manager, Request $request): Store
    {
        $lastStore = Store::latest('id')->first();

        $nextNumber = $lastStore ? ($lastStore->id + 1) : 1;

        $storeCode = 'STR' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $store = Store::create([
            'account_id' => $account->id,
            'name' => $request->first_name . ' ' . $request->last_name,
            'code' => $storeCode,
            'email' => $request->email,
            'phone' => $request->office_phone,
            'alternate_phone' => $request->cell_phone,
            'gst_number' => $request->gst_number,
            'address' => $request->street_address,
            'city' => $request->local_government,
            'state' => $request->state_of_origin,
            'country' => $request->country_of_origin,
            'manager_id' => $manager->id,
            'status' => $request->is_active,
            'logo' => '',
            'created_by' => auth()->id(),
        ]);

        $manager->update([
            'store_id' => $store->id,
        ]);

        return $store;
    }

}