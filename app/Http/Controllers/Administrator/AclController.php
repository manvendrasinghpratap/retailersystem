<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\Route as RouteModel;
use App\Models\ACL;
use DB;
use Config;
class AclController extends Controller
{
    protected $breadcrumbSync;
    protected $breadcrumbList;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbSync = ['title' => __('translation.accounts'), 'route1' => 'administrator.account.add', 'route1Title' => __('translation.add_new_account'), 'route2' => 'administrator.account.add', 'route2Title' => __('translation.add_new_account'), 'reset_route' => 'administrator.accounts', 'reset_route_title' => __('translation.cancel')];
        $this->breadcrumbList = ['title' => __('translation.access_control_list'), 'route1' => 'administrator.acl.sync', 'route1Title' => __('translation.access_control_list'), 'route2' => 'administrator.acl', 'route2Title' => __('translation.access_control_list'), 'reset_route' => 'administrator.acl', 'reset_route_title' => __('translation.cancel')];
    }

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbList;

        $designations = Designation::getSelectable();
        $routes = RouteModel::getSelectable();

        $query = ACL::query();

        // 🔍 Filter by designation
        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        // 🔍 Filter by route
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // 🔥 Pagination with query string (IMPORTANT)
        $accessControlList = $query->paginate(Config::get('constants.pagination'))
            ->appends($request->all());

        return view('backend.administrator.acl.index', compact(
            'designations',
            'routes',
            'accessControlList',
            'breadcrumb'
        ));
    }

    public function sync()
    {
        DB::transaction(function () {

            $designations = Designation::getSelectable(); // [id => name]
            $routes = RouteModel::getSelectable();        // [id => name]

            // ✅ Get existing ACL combinations
            $existing = ACL::select('designation_id', 'route_id')
                ->get()
                ->map(function ($item) {
                    return $item->designation_id . '-' . $item->route_id;
                })
                ->toArray();

            $insertData = [];

            foreach ($designations as $designationId => $designationName) {
                foreach ($routes as $routeId => $routeName) {

                    $key = $designationId . '-' . $routeId;

                    if (!in_array($key, $existing)) {
                        $insertData[] = [
                            'designation_id' => $designationId,
                            'route_id' => $routeId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // ✅ Bulk insert (much faster 🚀)
            if (!empty($insertData)) {
                ACL::insert($insertData);
            }
        });
        return redirect()->route('administrator.acl')->with('success', __('translation.sync_acl_success'));
    }
    public function update(Request $request)
    {
        $request->validate([
            'designationid' => 'required|integer',
            'routeid' => 'required|integer',
            'is_allowed' => 'required|boolean',
        ]);
        ACL::where('designation_id', $request->designationid)->where('route_id', $request->routeid)->update([
            'is_allowed' => $request->is_allowed,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ACL updated successfully'
        ]);
    }



}
