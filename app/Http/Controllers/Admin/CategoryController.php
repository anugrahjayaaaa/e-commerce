<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Http\Requests\Admin\GeneralBulkDeleteRequest;
use App\Models\Category;
use App\Services\ImageService;
use App\Traits\HandlesModelImages; // Use the general trait
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use HandlesModelImages; // Implement the general trait

    // Required properties for the HandlesModelImages trait contract
    protected ImageService $imageService;
    protected string $mainPath;

    // Optional properties used by the trait dynamically
    protected string $thumbnailPath;
    protected array $thumbDimensions = ['w' => 124, 'h' => 124];

    /**
     * Constructor: Auto-inject ImageService and set upload paths.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        $this->mainPath = "uploads/categories/";
        $this->thumbnailPath = $this->mainPath . "thumbnails/";
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query with products_count to allow sorting by product count    
        $query = Category::query();

        $search = $request->input('search');
        $status = $request->input('status');

        // Get sorting parameters
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Apply Search Filter
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        // Apply Sorting Logic
        // Define allowed columns to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'slug', 'parent_id', 'status', 'products_count'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'id';
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name', 'ASC')->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        // Retrieve the validated input data
        $validatedData = $request->validated();

        $category = new Category();
        $category->parent_id = $validatedData['parent_id'] ?? null;
        $category->name = $validatedData['name'];

        // Generate slug from the provided input or fallback to the category name
        $category->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);

        // Set status: 1 if active, 0 otherwise
        $category->status = $request->has('status') ? 1 : 0;

        // Process image upload via Trait wrapper. 
        // We pass 'false' to keep the original main image size.
        $category->image = $this->handleSingleImageUpload($request, $category, 'image', false);

        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
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
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        // Exclude the current category from being its own parent
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name', 'ASC')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        $category = Category::findOrFail($id);

        // Retrieve the validated input data
        $validatedData = $request->validated();

        $category->parent_id = $validatedData['parent_id'] ?? null;
        $category->name = $validatedData['name'];
        $category->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);
        $category->status = $request->has('status') ? 1 : 0;

        // Process image update via Trait wrapper.
        // We pass 'false' to keep the original main image size.
        $category->image = $this->handleSingleImageUpload($request, $category, 'image', false);

        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        // Safely wipe the physical files linked to the model (Main & Thumbnail)
        $this->deleteModelImages($category);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }

    /**
     * Remove multiple specified resources from storage at once.
     */
    public function bulkDestroy(GeneralBulkDeleteRequest $request)
    {
        $validatedData = $request->validated();
        $categories = Category::findMany($validatedData['ids']);

        foreach ($categories as $category) {
            $this->deleteModelImages($category);
            $category->delete();
        }

        return redirect()->route('admin.categories.index')->with('success', 'Categories deleted successfully!');
    }
}
