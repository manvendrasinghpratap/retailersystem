<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterItem;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use Illuminate\Database\QueryException;

class MasterItemController extends Controller
{
    protected $breadcrumb;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumb = [
            'title' => 'Master Items',
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => 'Dashboard'],
                ['route' => 'admin.master_items.index', 'title' => __('translation.master_items')],
                ['route' => 'admin.master_items.create', 'title' => __('translation.add_master_item')],
            ],
            'route1' => 'admin.master_items.create',
            'route1Title' => __('translation.add_master_item'),
            'route2' => 'admin.master_items.index',
            'route2Title' => __('translation.master_item_list'),
            'reset_route' => 'admin.master_items.index',
            'reset_route_title' => 'Cancel',
            'route3Title' => 'Edit Master Item',
        ];
    }

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumb;
        $items = MasterItem::account()->where('is_deleted', 0)
            ->when($request->filled('item_name'), function ($q) use ($request) {
                $search = trim($request->item_name);
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%$search%")
                        ->orWhere('code', 'LIKE', "%$search%")
                        ->orWhere('description', 'LIKE', "%$search%");
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest()
            ->paginate(config('constants.pagination'));

        return view('backend.admin.master_items.index', compact('items', 'breadcrumb'));

    }

    public function create()
    {
        return view('backend.admin.master_items.form', ['breadcrumb' => $this->breadcrumb]);
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $item = MasterItem::account()->where('name', $request->name)->first();
        if ($item) {
            return response()->json([
                'success' => false,
                'message' => 'Item name already exists!'
            ]);
        }
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = Settings::uploadimage($request, 'image', 'master_item');
        }
        $item = MasterItem::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->status,
            'image' => $imageName,
            'account_id' => auth()->user()->account_id,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item added successfully',
            'data' => $item
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            $imageName = null;
            // Upload Image
            if ($request->hasFile('image')) {
                $imageName = Settings::uploadimage($request, 'image', 'master_item');
            }

            MasterItem::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'image' => $imageName,
                'status' => $request->status,
                'account_id' => auth()->user()->account_id,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.master_items.index')
                ->with('success', 'Item added successfully');

        } catch (QueryException $e) {

            // 🔥 MYSQL DUPLICATE ERROR CODE = 1062
            if ($e->errorInfo[1] == 1062) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Item name already exists!');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong!');
        }
    }

    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);

        $item = MasterItem::account()->findOrFail($id);

        return view('backend.admin.master_items.form', [
            'breadcrumb' => $this->breadcrumb,
            'item' => $item
        ]);
    }

    public function update(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);
        $item = MasterItem::account()->findOrFail($id);
        $request->validate([
            'name' => 'required'
        ]);
        $imagePath = $item->image;
        if ($request->hasFile('image')) {
            $imagePath = Settings::uploadimage(
                $request,
                'image',
                'master_item',
                $item->image
            );
        }
        $item->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'image' => $imagePath
        ]);

        return Settings::roleRedirect('master_items.index', 'Item Updated Successfully');
    }

    public function delete(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);

        $item = MasterItem::account()
            ->where('id', $id)
            ->first();

        $deleted = $item ? $item->delete() : false;

        return response()->json([
            'success' => $deleted ? true : false
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->q;

        $products = MasterItem::account()->where('is_deleted', 0)->orderBy('name', 'asc')
            ->where(function ($q2) use ($query) {
                $q2->where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('description', 'LIKE', "%$query%");
            })
            ->limit(20)
            ->get();

        return response()->json(
            $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'text' => $p->name
                ];
            })
        );
    }
}