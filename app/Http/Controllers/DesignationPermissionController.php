<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignationPermissionController extends Controller
{
    /**
     * Display permissions for a designation.
     */
    public function edit($designationId)
    {
        // dd(auth()->user()->hasPermission('stock_return.create'));
        // dd(
        //     auth()->user()->account_id,
        //     Module::where('account_id', auth()->user()->account_id)->get()
        // );


        $designation = Designation::findOrFail($designationId);

        $modules = Module::with([
            'menus.permissions' => function ($query) {
                $query->where('status', true);
            },
        ])
            ->where('account_id', auth()->user()->account_id)
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $assignedPermissionIds = $designation
            ->permissions()
            ->where('permissions.account_id', auth()->user()->account_id)
            ->pluck('permissions.id')
            ->toArray();

        return view(
            'backend.designations.permissions',
            compact(
                'designation',
                'modules',
                'assignedPermissionIds'
            )
        );
    }

    /**
     * Save designation permissions.
     */
    public function update(
        Request $request,
        $designationId
    ) {
        $designation = Designation::findOrFail($designationId);

        $accountId = auth()->user()->account_id;

        $permissionIds = $request->input(
            'permissions',
            []
        );

        DB::transaction(function () use ($designation, $permissionIds, $accountId) {
            $permissions = \App\Models\Permission::where(
                'account_id',
                $accountId
            )
                ->whereIn('id', $permissionIds)
                ->pluck('id')
                ->toArray();

            $designation->permissions()->syncWithPivotValues(
                $permissions,
                [
                    'account_id' => $accountId,
                ]
            );
        });

        return back()->with(
            'success',
            'Permissions updated successfully.'
        );
    }
}