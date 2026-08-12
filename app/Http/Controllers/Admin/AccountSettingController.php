<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\Settings;
use App\Models\AccountModule;
use App\Services\AccountSettingService;

class AccountSettingController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        /*
        |--------------------------------------------------------------------------
        | Add / Edit Breadcrumb
        |--------------------------------------------------------------------------
        */

        $this->breadcrumbAddNew = [
            'title' => __('translation.account_settings'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard'),
                ],
                [
                    'route' => 'admin.account-settings.index',
                    'title' => __('translation.account_settings'),
                ],
                // [
                //     'route' => 'admin.account-settings.create',
                //     'title' => __('translation.add_account_setting'),
                // ],
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Listing Breadcrumb
        |--------------------------------------------------------------------------
        */

        $this->breadcrumbListing = [
            'title' => __('translation.account_settings'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard'),
                ],
                [
                    'route' => 'admin.account-settings.index',
                    'title' => __('translation.account_settings'),
                ],
                // [
                //     'route' => 'admin.account-settings.create',
                //     'title' => __('translation.add_account_setting'),
                // ],
            ],
        ];
    }

    /**
     * Display Listing
     */
    public function index()
    {
        $modules = AccountModule::getSelectable();
        $breadcrumb = $this->breadcrumbListing;
        $accountSettings = AccountSetting::ofAccount()->where('module', '=', 'general')->orderBy('module')->get();
        return view('backend.admin.account-settings.index', compact('breadcrumb', 'accountSettings', 'modules'));
    }

    /**
     * Create
     */
    public function create()
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $modules = AccountModule::getSelectable();
        // $availableModules = Settings::getAvailableModules();
        $availableModules = AccountModule::getSelectable();

        return view('backend.admin.account-settings.selectedform', compact('breadcrumb', 'availableModules'));
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $settings = Settings::buildSettings($request);
            $accountSetting = AccountSetting::where('account_id', Auth::user()->account_id)
                ->where('module', $request->module)
                ->first();
            if ($accountSetting) {
                $accountSetting->update([
                    'settings' => array_merge(
                        $accountSetting->settings ?? [],
                        $settings
                    ),
                ]);
            } else {
                AccountSetting::create([
                    'account_id' => Auth::user()->account_id,
                    'module' => $request->module,
                    'settings' => $settings,
                ]);
            }
            DB::commit();
            return redirect()->route('admin.account-settings.index')->with('success', __('translation.record_created_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        try {
            $accountSetting = AccountSetting::ofAccount()->findOrFail($id);
            $breadcrumb = [
                'title' => __('translation.edit_account_setting'),
                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard'),
                    ],
                    [
                        'route' => 'admin.account-settings.index',
                        'title' => __('translation.account_settings'),
                    ],
                    // [
                    //     'route' => 'admin.account-settings.create',
                    //     'title' => __('translation.add_account_setting'),
                    // ],
                ],
            ];
            return view('backend.admin.account-settings.selectedform', compact('accountSetting', 'breadcrumb'));
        } catch (\Exception $e) {
            return redirect()->route('admin.account-settings.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Update
     */
    public function update_working_for_all(Request $request, AccountSettingService $accountSettingService, $id)
    {
        $validator = $this->validateRequest($request, $id);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $accountSetting = AccountSetting::ofAccount()->findOrFail($id);
            $accountSetting->update(['settings' => Settings::buildSettings($request),]);
            DB::commit();
            // Clear account settings cache after successful update
            $accountSettingService->clearCache($accountSetting->account_id);
            return redirect()->route('admin.account-settings.index')->with('success', __('translation.record_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, AccountSettingService $accountSettingService, $id)
    {
        $validator = $this->validateRequest($request, $id);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $accountSetting = AccountSetting::ofAccount()->findOrFail($id);

            // Get existing JSON settings
            $settings = $accountSetting->settings ?? [];

            // Only these settings can be updated
            $allowedSettings = [
                'tax',
                'session_timeout',
                'warning_before',
                'pagination',
            ];

            $keys = $request->input('keys', []);
            $values = $request->input('values', []);

            foreach ($keys as $index => $key) {

                // Ignore any key that is not allowed
                if (!in_array($key, $allowedSettings, true)) {
                    continue;
                }

                // Update only the allowed setting
                $settings[$key] = $values[$index] ?? null;
            }

            // Save complete settings JSON
            // Existing values remain unchanged
            $accountSetting->update([
                'settings' => $settings,
            ]);

            DB::commit();

            // Clear cached settings
            $accountSettingService->clearCache($accountSetting->account_id);

            return redirect()
                ->route('admin.account-settings.index')
                ->with(
                    'success',
                    __('translation.record_updated_successfully')
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function update_old(Request $request, AccountSettingService $accountSettingService, $id)
    {
        $validator = $this->validateRequest($request, $id);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $accountSetting = AccountSetting::ofAccount()->findOrFail($id);
            $accountSetting->update(['settings' => Settings::buildSettings($request)]);
            DB::commit();
            return redirect()->route('admin.account-settings.index')->with('success', __('translation.record_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Validate Request
     */
    private function validateRequest(Request $request, $id = null)
    {
        $rules = [
            'module' => [
                'required',
                'max:50',
            ],
            'keys' => 'required|array|min:1',
            'keys.*' => [
                'required',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
            ],
            'values' => 'required|array',
        ];
        // Only validate module uniqueness when updating and allowing module edits.
        // If your edit form keeps module readonly, this block can be omitted.
        if ($id) {
            $rules['module'][] = Rule::unique('account_settings')
                ->ignore($id)
                ->where(function ($query) {
                    return $query->where(
                        'account_id',
                        Auth::user()->account_id
                    );
                });
        }
        $validator = Validator::make($request->all(), $rules);
        $keys = array_filter($request->keys ?? []);
        if (count($keys) !== count(array_unique($keys))) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'keys',
                    __('translation.setting_keys_must_be_unique')
                );
            });
        }
        return $validator;
    }

    private function validateRequest_old(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'module' => [
                Rule::requiredIf($id === null),
                'nullable',
                'max:50',
                Rule::unique('account_settings')
                    ->ignore($id)
                    ->where(function ($query) {
                        return $query->where(
                            'account_id',
                            Auth::user()->account_id
                        );
                    }),
            ],
            'keys' => 'required|array|min:1',
            'keys.*' => [
                'required',
                'max:100',
                'regex:/^[a-z0-9_]+$/'
            ],
            'values' => 'required|array',
        ]);

        $keys = array_filter($request->keys ?? []);
        if (count($keys) != count(array_unique($keys))) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'keys',
                    __('translation.setting_keys_must_be_unique')
                );
            });
        }
        return $validator;
    }
}
