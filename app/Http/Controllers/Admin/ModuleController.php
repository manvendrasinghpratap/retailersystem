<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Helpers\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
class ModuleController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.modules'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.modules.index',
                    'title' => __('translation.modules')
                ],
                [
                    'route' => 'admin.modules.create',
                    'title' => __('translation.add_new_module')
                ]
            ],
            'route1' => 'admin.modules.create',
            'route1Title' => __('translation.add_new_module'),
            'route2Title' => __('translation.add_new_module'),
            'route2' => 'admin.modules.index',
            'reset_route' => 'admin.modules.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.modules'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.modules.index',
                    'title' => __('translation.modules')
                ],
                [
                    'route' => 'admin.modules.create',
                    'title' => __('translation.add_new_module')
                ]
            ],
            'route1' => 'admin.modules.index',
            'route1Title' => __('translation.modules'),
            'route2Title' => __('translation.add_new_module'),
            'route2' => 'admin.modules.create',
            'route3Title' => __('translation.update_module'),
            'route3' => 'admin.modules.edit',
            'reset_route' => 'admin.modules.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $modules = Module::ofAccount()->notDeleted();

        if ($request->filled('name')) {
            $modules->where('name', 'LIKE', '%' . trim($request->name) . '%');
        }
        if ($request->filled('slug')) {
            $modules->where('slug', 'LIKE', '%' . trim($request->slug) . '%');
        }
        if ($request->status !== null && $request->status !== '') {
            $modules->where('status', $request->status);
        }

        // PDF Export
        if ($request->has('pdf')) {
            $modules = $modules->orderBy('sort_order')->get();
            $pdfHeaderdata = \Config::get('constants.moduleListpdf');
            $pdf = PDF::loadView(
                'backend.pdf.modules.moduleListpdf',
                compact('modules', 'pdfHeaderdata', 'breadcrumb')
            );
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        // CSV Export
        if ($request->has('csv')) {
            $modules = $modules->orderBy('sort_order')->get();
            $csvHeaderdata = \Config::get('constants.moduleListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = 0;
            $data[$ii] = [
                '#',
                __('translation.module'),
                __('translation.slug'),
                __('translation.status'),
                __('translation.createdat'),
            ];
            foreach ($modules as $module) {
                $data[++$ii] = [
                    $ii,
                    $module->name,
                    $module->slug,
                    $module->icon,
                    $module->sort_order,
                    $module->status ? 'Active' : 'Inactive',
                    !empty($module->created_at)
                    ? "\t" . Settings::getFormattedDatetime($module->created_at)
                    : '-',
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $modules = $modules
            ->orderBy('sort_order')
            ->paginate(account_setting('general.pagination'));

        return view('backend.admin.module.index', compact(
            'modules',
            'breadcrumb'
        ));
    }
    public function exportPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->index($request);
    }

    public function exportCsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->index($request);
    }
    public function create()
    {
        return view('backend.admin.module.form', ['breadcrumb' => $this->breadcrumbListing]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'slug' => Str::slug($request->slug ?: $request->name, '_'),
        ]);
        $request->validate([
            'name' => 'required|max:100',
            'slug' => 'required|max:100|unique:modules,slug,NULL,id,account_id,' . auth()->user()->account_id,
            'icon' => 'nullable|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        try {

            Module::create([
                'account_id' => auth()->user()->account_id,
                'name' => ucwords(trim($request->name)),
                'slug' => strtolower($request->slug),
                'icon' => $request->icon,
                'sort_order' => $request->sort_order ?? 0,
                'status' => $request->status,
            ]);

            return Settings::roleRedirect(
                'modules.index',
                __('translation.module_added_successfully')
            );

        } catch (\Exception $e) {

            return Settings::roleRedirect(
                'modules.index',
                __('translation.something_went_wrong'),
                'error'
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute(
            $this->breadcrumbListing,
            ['route3', 'route3Title'],
            ['admin.modules.update', __('translation.update_module')]
        );

        $id = Settings::getDecodeCode($id);

        $module = Module::notDeleted()->ofAccount()->findOrFail($id);

        return view('backend.admin.module.form', [
            'breadcrumb' => $breadcrumb,
            'module' => $module
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->module_id);
            $module = Module::notDeleted()->ofAccount()->findOrFail($id);

            $request->merge([
                'slug' => Str::slug($request->slug ?: $request->name, '_'),
            ]);

            $request->validate([
                'name' => 'required|max:100',
                'slug' => 'required|max:100|unique:modules,slug,' . $module->id . ',id,account_id,' . auth()->user()->account_id,
                'icon' => 'nullable|max:100',
                'sort_order' => 'nullable|integer|min:0',
                'status' => 'required|in:0,1',
            ]);

            $module->update([
                'name' => ucwords(trim($request->name)),
                'slug' => strtolower($request->slug),
                'icon' => $request->icon,
                'sort_order' => $request->sort_order ?? 0,
                'status' => $request->status,
            ]);

            return Settings::roleRedirect(
                'modules.index',
                __('translation.module_updated_successfully')
            );

        } catch (\Exception $e) {

            return Settings::roleRedirect(
                'modules.index',
                __('translation.something_went_wrong'),
                'error'
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function softdelete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);
            $deleted = Module::notDeleted()->ofAccount()->findOrFail($id)->update([
                'is_deleted' => 1
            ]);
            return response()->json([
                'success' => $deleted ? true : false,
                'message' => $deleted
                    ? __('translation.deleted_successfully')
                    : __('translation.delete_failed')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.something_went_wrong')
            ], 500);
        }
    }
}
