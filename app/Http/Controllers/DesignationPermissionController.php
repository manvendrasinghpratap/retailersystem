<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Module;
use App\Helpers\Settings;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignationPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:designation_permission.edit')->only(['edit']);
        $this->middleware('permission:designation_permission.update')->only(['update']);
    }
    /**
     * Display permissions for a designation.
     */
    public function edit($designationId)
    {
        $designationId = Settings::getDecodeCode($designationId);
        $accountId = auth()->user()->account_id;

        // Fetch designation belonging to current account
        $designation = Designation::ofAccount()->findOrFail($designationId);
        // Load modules -> menus -> permissions with active scopes
        $modules = Module::ofAccount()
            ->notDeleted()
            ->active()
            ->with([
                'menus' => function ($query) {
                    $query->active()->orderBy('sort_order');
                },
                'menus.permissions' => function ($query) {
                    $query->active();
                }
            ])
            ->orderBy('sort_order')
            ->get();

        // Get currently assigned permission IDs for this designation
        $assignedPermissionIds = $designation->permissions()
            ->where('permissions.account_id', $accountId)
            ->pluck('permissions.id')
            ->toArray();

        return view('backend.designations.permissions', compact('designation', 'modules', 'assignedPermissionIds'));
    }

    /**
     * Save designation permissions.
     */
    public function update(Request $request, $designationId)
    {
        $accountId = auth()->user()->account_id;

        $designation = Designation::ofAccount()->findOrFail($designationId);

        $permissionIds = $request->input('permissions', []);

        DB::transaction(function () use ($designation, $permissionIds, $accountId) {
            // Validate that assigned permissions belong to current account
            $validPermissions = Permission::ofAccount()
                ->whereIn('id', $permissionIds)
                ->pluck('id')
                ->toArray();

            // Sync permissions with multi-tenant account_id in pivot
            $designation->permissions()->syncWithPivotValues(
                $validPermissions,
                ['account_id' => $accountId]
            );
        });

        return back()->with('success', 'Permissions updated successfully.');
    }
}