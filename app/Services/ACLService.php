<?php

namespace App\Services;

use App\Models\ACL;
use App\Models\Designation;
use App\Models\RouteModel;
use Illuminate\Support\Facades\DB;

class ACLService
{
    public function sync(): void
    {
        DB::transaction(function () {
            $designations = Designation::getSelectable();
            $designations[1] = 'Superadmin';
            $routes = RouteModel::getSelectable();

            $existing = ACL::select('designation_id', 'route_id')
                ->get()
                ->map(fn($item) => $item->designation_id . '-' . $item->route_id)
                ->toArray();

            $insertData = [];

            foreach ($designations as $designationId => $designationName) {
                foreach ($routes as $routeId => $routeName) {
                    $key = $designationId . '-' . $routeId;

                    if (!in_array($key, $existing)) {
                        $insertData[] = [
                            'designation_id' => $designationId,
                            'route_id'       => $routeId,
                            'account_id'     => auth()->user()->account_id,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                ACL::insert($insertData);
            }
        });
    }
}