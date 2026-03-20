<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => 'Categories',
            'route1' => "admin.categories.create",
            'route1Title' => 'Add Category',
            'route2Title' => 'Add Category',
            'route2' => 'admin.categories',
            'reset_route' => 'admin.categories', 'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => 'Categories',
            'route1' => "admin.categories",
            'route1Title' => 'Category Listing',
            'route2Title' => 'Add / Edit Category',
            'route2' => 'admin.categories.create',
            'route3Title' => 'Add / Edit Category',
            'route3' => 'admin.categories.edit',
            'reset_route' => 'admin.categories', 'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Category Listing
     */
    public function index()
    {
        $breadcrumb = $this->breadcrumbAddNew;

        $categories = Category::where('account_id', auth()->user()->account_id)
            ->where('is_deleted', 0)
            ->latest();

        // Filters
        if (request('categoryname')) {
            $categories->where('name', 'LIKE', '%' . trim(request('categoryname')) . '%');
            $categories->orWhere('slug', 'LIKE', '%' . trim(request('categoryname')) . '%');
            $categories->orWhere('description', 'LIKE', '%' . trim(request('categoryname')) . '%');
        }

        if (request('is_active') !== null) {
            $categories->where('status', request('is_active'));
        }

        $categories = $categories->paginate(config('constants.pagination'));
        $status = config('constants.accountstatus');

        return view('backend.admin.category.index', compact('categories', 'breadcrumb', 'status'));
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('backend.admin.category.form', ['breadcrumb' => $this->breadcrumbListing]);
    }

    /**
     * Store Category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:12048',
            'status' => 'nullable|boolean',
        ]);

        try {
            $imagePath = null;
            // Upload Image
            if ($request->hasFile('image')) {
                $imagePath = Settings::uploadimage($request, 'image', 'categories');
            }
            Category::createCategory($request, $imagePath);
            return Settings::roleRedirect('categories','Category Added Successfully.');

        } catch (\Exception $e) {
            return Settings::roleRedirect('categories','Something went wrong!','error');
        }
    }

    /**
     * Edit Category
     */
    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);

        $category = Category::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.category.form', [
            'breadcrumb' => $this->breadcrumbListing,
            'category'   => $category
        ]);
    }

    /**
     * Update Category
     */
    public function update(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->category_id);
            $category = Category::where('account_id', auth()->user()->account_id)->findOrFail($id);
            $request->validate([
                'name'   => 'required|string|max:255',
                'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status' => 'nullable|boolean',
            ]);

            $imagePath = $category->image;

            if ($request->hasFile('image')) {
                $imagePath = Settings::uploadimage(
                    $request,
                    'image',
                    'categories',
                    $category->image
                );
            }
            Category::updateCategory($category,$request, $imagePath);
            return Settings::roleRedirect('categories','Category Updated Successfully.');

        } catch (\Exception $e) {
            return Settings::roleRedirect('categories','Something went wrong!','error');
        }
    }

    /**
     * Soft Delete
     */
    public function softdelete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->input('id'));

            $deleted = Category::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['is_deleted' => 1]);

            return response()->json([
                'success' => $deleted ? true : false,
                'message' => $deleted ? 'Deleted successfully' : 'Delete failed'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Status Update (AJAX)
     */
    public function statusUpdate(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);

            $updated = Category::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => $updated ? true : false,
                'message' => $updated ? 'Status updated' : 'Update failed'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Hard Delete (Optional)
     */
    public function destroy($id)
    {
        $id = Settings::getDecodeCode($id);

        $category = Category::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category permanently deleted.');
    }
}