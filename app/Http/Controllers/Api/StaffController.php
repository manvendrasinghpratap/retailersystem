<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Store;
use App\Models\User;
use App\Models\UserDetail;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class StaffController extends Controller
{
    /**
     * User service.
     */
    protected UserService $userService;

    /**
     * Initialize controller.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $this->middleware('permission:staff.index')->only(['index', 'show', 'list']);
        $this->middleware('permission:staff.create')->only(['store']);
        $this->middleware('permission:staff.edit')->only(['update', 'statusUpdate', 'updatePassword']);
        $this->middleware('permission:staff.destroy')->only(['destroy']);
        $this->middleware('permission:staff.export')->only(['export']);
    }

    /**
     * Display a paginated list of staff.
     *
     * GET /api/staff
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::where('is_deleted', 0)
                ->where('is_staff', 1)
                ->where('designation_id', '>', 1)
                ->ofAccount();

            /*
            |--------------------------------------------------------------------------
            | Staff Name
            |--------------------------------------------------------------------------
            */

            if ($request->filled('staff_name')) {
                $query->where(
                    'name',
                    'LIKE',
                    '%' . trim($request->input('staff_name')) . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Designation
            |--------------------------------------------------------------------------
            */

            if ($request->filled('designation_id')) {
                $query->where(
                    'designation_id',
                    $request->input('designation_id')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Store
            |--------------------------------------------------------------------------
            */

            if ($request->filled('store_id')) {
                $query->where(
                    'store_id',
                    $request->input('store_id')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Active Status
            |--------------------------------------------------------------------------
            */

            if ($request->filled('is_active')) {
                $query->where(
                    'is_active',
                    $request->input('is_active')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Hire Date
            |--------------------------------------------------------------------------
            */

            if ($request->filled('hired_date')) {
                $hiredDate = Settings::formatDate(
                    $request->input('hired_date'),
                    'Y-m-d'
                );

                $query->whereHas('detail', function ($q) use ($hiredDate) {
                    $q->whereDate('hire_date', $hiredDate);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Eager Loading
            |--------------------------------------------------------------------------
            */

            $query->with([
                'designation',
                'detail',
                'store',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            $perPage = min(
                max((int) $request->input('per_page', 20), 1),
                100
            );

            $staff = $query
                ->orderByDesc('id')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Staff retrieved successfully.',
                'data' => $staff,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve staff.',
            ], 500);
        }
    }

    /**
     * Get a single staff member.
     *
     * GET /api/staff/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $staff = User::where('id', $id)
                ->where('is_deleted', 0)
                ->where('is_staff', 1)
                ->where('designation_id', '>', 1)
                ->ofAccount()
                ->with([
                    'designation',
                    'detail',
                    'store',
                ])
                ->first();

            if (!$staff) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staff member not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Staff retrieved successfully.',
                'data' => [
                    'staff' => $staff,
                ],
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve staff.',
            ], 500);
        }
    }

    /**
     * Create a new staff member.
     *
     * POST /api/staff
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'designation_id' => [
                'required',
                'integer',
                'exists:designations,id',
            ],

            'store_id' => [
                'nullable',
                'integer',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Validate Designation
            |--------------------------------------------------------------------------
            */

            $designation = Designation::find(
                $validated['designation_id']
            );

            if (!$designation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid designation.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Process Avatar
            |--------------------------------------------------------------------------
            */

            $filename = 'default.png';

            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage(
                    $request,
                    'avatar',
                    'staff'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Staff
            |--------------------------------------------------------------------------
            */

            $user = $this->userService->createStaff(
                $request,
                $filename
            );

            /*
            |--------------------------------------------------------------------------
            | Staff Details
            |--------------------------------------------------------------------------
            */

            UserDetail::updateOrCreateDetail(
                $user->id,
                $request->all()
            );

            /*
            |--------------------------------------------------------------------------
            | Reload Relationships
            |--------------------------------------------------------------------------
            */

            $user->load([
                'designation',
                'detail',
                'store',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Staff added successfully.',
                'data' => [
                    'staff' => $user,
                ],
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to add staff.',
            ], 500);
        }
    }

    /**
     * Update staff member.
     *
     * PUT /api/staff/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $id,
            ],

            'designation_id' => [
                'required',
                'integer',
                'exists:designations,id',
            ],

            'store_id' => [
                'nullable',
                'integer',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Get Staff Within Current Account
            |--------------------------------------------------------------------------
            */

            $user = User::where('id', $id)
                ->where('is_deleted', 0)
                ->where('is_staff', 1)
                ->ofAccount()
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staff member not found.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Avatar
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage(
                    $request,
                    'avatar',
                    'staff'
                );

                $user->avatar = $filename;
                $user->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Update Staff
            |--------------------------------------------------------------------------
            */

            $user = $this->userService->updateStaffBasic(
                $user,
                $request
            );

            /*
            |--------------------------------------------------------------------------
            | Update Staff Details
            |--------------------------------------------------------------------------
            */

            UserDetail::updateOrCreateDetail(
                $user->id,
                $request->all()
            );

            /*
            |--------------------------------------------------------------------------
            | Reload Relationships
            |--------------------------------------------------------------------------
            */

            $user->load([
                'designation',
                'detail',
                'store',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Staff updated successfully.',
                'data' => [
                    'staff' => $user,
                ],
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to update staff.',
            ], 500);
        }
    }

    /**
     * Delete staff member.
     *
     * DELETE /api/staff/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::where('id', $id)
                ->where('is_deleted', 0)
                ->where('is_staff', 1)
                ->ofAccount()
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staff member not found.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Soft Delete
            |--------------------------------------------------------------------------
            */

            $user->update([
                'is_deleted' => 1,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Staff deleted successfully.',
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to delete staff.',
            ], 500);
        }
    }

    /**
     * Update staff active status.
     *
     * PATCH /api/staff/{id}/status
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        try {
            $user = User::where('id', $id)
                ->where('is_deleted', 0)
                ->where('is_staff', 1)
                ->ofAccount()
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staff member not found.',
                ], 404);
            }

            $user->update([
                'is_active' => $validated['is_active'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Staff status updated successfully.',
                'data' => [
                    'id' => $user->id,
                    'is_active' => (bool) $user->is_active,
                ],
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to update staff status.',
            ], 500);
        }
    }

    /**
     * Update staff password.
     *
     * PATCH /api/staff/{id}/password
     */
    public function updatePassword(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        try {
            $user = User::where('id', $id)
                ->where('is_deleted', 0)
                ->where('is_staff', 1)
                ->ofAccount()
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staff member not found.',
                ], 404);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Staff password updated successfully.',
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to update staff password.',
            ], 500);
        }
    }

    /**
     * Get active staff list for dropdowns.
     *
     * GET /api/staff/list
     */
    public function list(): JsonResponse
    {
        try {
            $staff = User::select([
                    'id',
                    'name',
                ])
                ->where('is_deleted', 0)
                ->where('is_staff', 2)
                ->where('designation_id', '>', 1)
                ->ofAccount()
                ->where('is_active', 1)
                ->where('is_parent', 0)
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Staff list retrieved successfully.',
                'data' => $staff,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve staff list.',
            ], 500);
        }
    }
}
