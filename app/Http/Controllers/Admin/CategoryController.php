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
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbAddNew = ['title' => __('translation.categories'), 'route1' => "categories.index", 'route1Title' => __('translation.categories').' '.__('translation.listing') , 'route2Title' => __('translation.add_new_category'), 'route2' => 'categories.create'];
        $this->breadcrumbListing = ['title' => __('translation.categories'), 'route1' => "categories.index", 'route1Title' => __('translation.add_new_category'), 'route2Title' => __('translation.categories'), 'route2' => 'categories.index', 'route3Title' => __('translation.update'), 'route3' => 'categories.update'];
      
    }
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $breadcrumb       = $this->breadcrumbAddNew;
        $categories       = Category::latest()->where('is_deleted',0);
        if (!empty(request()->get('categoryname'))) {
            $categories = $categories->where('name', 'LIKE', '%' . trim(request()->get('categoryname')) . '%');
        }
        if (request()->get('is_active') !==null) {
            $categories = $categories->where('status', request()->get('is_active'));
        }
        $categories = $categories->paginate(\Config::get('constants.pagination'));
        $status           = \Config::get('constants.accountstatus');
        return view('backend.category.index',compact("categories",'breadcrumb','status'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $breadcrumb       = $this->breadcrumbListing;
        return view('backend.category.form', ['breadcrumb' => $this->breadcrumbListing]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
            // 1️⃣ Validate incoming request
            $request->validate([
                'name'   => 'required|string|max:255',
                'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:12048',
                'status' => 'nullable|boolean',
            ]);

            try {

                // 2️⃣ Default image value
                $imagePath = null;

                // 3️⃣ Upload image if provided
                if ($request->hasFile('image')) {
                    $imagePath = Settings::uploadimage(
                        $request,      // full request
                        'image',       // input field name
                        'categories'   // folder name
                    );
                }

                // 4️⃣ Save category in database
                Category::create([
                    'name'        => $request->name,
                    'slug'        => $request->slug ?? Str::slug($request->name),
                    'image'       => $imagePath,
                    'status'      => $request->status ?? 1,
                    'is_deleted'  => 0,
                    'created_by'  => auth()->id(),
                    'description' => $request->description ?? '',
                ]);

                // 5️⃣ Redirect with success message
                return redirect()
                    ->route('categories.index')
                    ->with('success', 'Category created successfully.');

            } catch (\Exception $e) {

                // 6️⃣ Log error (for developer)
                if (app()->environment('production')) {
                    Log::error('Category Store Error: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                }

                // 7️⃣ Redirect back with error message
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Something went wrong. Please try again.');
            }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Request $request,$id,)
    {  
        $id = Settings::getDecodeCode($id);
        $category = Category::findOrFail($id);
        return view('backend.category.form', ['breadcrumb' => $this->breadcrumbListing, 'category' => $category]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request)
    {
        $id = Settings::getDecodeCode($request->category_id);
        $category = Category::findOrFail($id);
        try {
        } catch (\Exception $e) {
            // Log error (for developer)
            if (app()->environment('production')) {
                Log::error('Category Update Error: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            }

            // Redirect back with error message
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
        // Validate incoming request
        $request->validate([
            'name'   => 'required|string|max:255',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);   

        $imagePath = $category->image;
        // 3️⃣ Upload image if provided
        if ($request->hasFile('image')) {
            $imagePath = Settings::uploadimage(
                $request,      // full request
                'image',       // input field name
                'categories',   // folder name
                $category->image // existing image to delete
            );
        }
        $category->update([
                'name'        => $request->name,
                'slug'        => $request->slug ?? Str::slug($request->name),
                'image'       => $imagePath,
                'status'      => $request->status ?? 1,
                'is_deleted'  => 0,
                'description' => $request->description ?? '',
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Delete category image
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function statusUpdate(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->input('id'));
            $status = $request->input('status');
            $updated = Category::where("id", $id)->update(["status" => $status]);

            if ($updated) {
                return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Update failed or item not found.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
        }
    }

    public function softdelete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->input('id'));
            $is_deleted = 1;
            $deleted = Category::where('id', $id)->update(['is_deleted' => $is_deleted]);
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Status deleted successfully.']);
            } else {
                return response()->json(['error' => false, 'message' => 'Update failed or item not found.']);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
        }
    }
}
