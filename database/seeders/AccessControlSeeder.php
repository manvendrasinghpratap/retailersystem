<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        |
        | For now, use the account ID for which the default ACL data
        | should be created.
        |
        */

        $accountId = 3;

        /*
        |--------------------------------------------------------------------------
        | Procurement Module
        |--------------------------------------------------------------------------
        */

        $procurement = Module::updateOrCreate(
            [
                'account_id' => $accountId,
                'slug' => 'procurement',
            ],
            [
                'name' => 'Procurement',
                'icon' => 'ti ti-shopping-cart',
                'sort_order' => 10,
                'status' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Stock Returns Menu
        |--------------------------------------------------------------------------
        */

        $stockReturns = Menu::updateOrCreate(
            [
                'account_id' => $accountId,
                'module_id' => $procurement->id,
                'slug' => 'stock_returns',
            ],
            [
                'name' => 'Stock Returns',
                'route_name' => 'stock_returns.index',
                'icon' => 'ti ti-arrow-back-up',
                'sort_order' => 30,
                'status' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Stock Returns Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            [
                'name' => 'View Stock Returns',
                'slug' => 'stock_return.view',
                'description' => 'View stock return records',
            ],
            [
                'name' => 'Create Stock Returns',
                'slug' => 'stock_return.create',
                'description' => 'Create a new stock return',
            ],
            [
                'name' => 'Edit Stock Returns',
                'slug' => 'stock_return.edit',
                'description' => 'Edit stock return records',
            ],
            [
                'name' => 'Delete Stock Returns',
                'slug' => 'stock_return.delete',
                'description' => 'Delete stock return records',
            ],
            [
                'name' => 'Cancel Stock Returns',
                'slug' => 'stock_return.cancel',
                'description' => 'Cancel a stock return',
            ],
            [
                'name' => 'Export Stock Returns',
                'slug' => 'stock_return.export',
                'description' => 'Export stock return records',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'account_id' => $accountId,
                    'slug' => $permission['slug'],
                ],
                [
                    'module_id' => $procurement->id,
                    'menu_id' => $stockReturns->id,
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'status' => true,
                ]
            );
        }
    }
}