<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    protected ImageService $imageService;

    // Auto inject by Laravel for image service
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Category::query();

        $search = request('search');
        $status = request('status');

        if ($search) {
            $query->where('name', 'LIKE', "{$search}");
        }

        if (request()->filled('status')) {
            $query->where('status', $status);
        }

        $categories = $query->orderBy('id', 'DESC')->paginate(10)->withQueryString();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = Category::where('parent_id', null)->orderBy('name', 'ASC')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request, ImageService $imageService)
    {
        // Retrieve the validated input data
        $validatedData = $request->validated();

        $category = new Category();
        $category->parent_id = $validatedData['parent_id'];
        $category->name = $validatedData['name'];

        // Generate slug from the provided input or fallback to name
        $category->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);

        // Set status: 1 if active, 0 otherwise
        $category->status = $request->has('status') ? 1 : 0;

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();

            // Generate thumbnail using the ImageService
            $imageService->generateThumbnailImage($file, $imageName, 'uploads/categories', 124, 124);

            // Move the original image to the public storage
            $file->move(public_path('uploads/categories'), $imageName);

            $category->image = $imageName;
        }

        $category->save();

        // Redirect to the index page with a success message
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
        $parentCategories = Category::where('parent_id', null)
            ->where('id', '!=', $category->id)
            ->orderBy('name', 'ASC')->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id, ImageService $imageService)
    {
        $category = category::findOrFail($id);

        // Retrieve the validated input data
        $validatedData = $request->validated();
        $category->parent_id = $validatedData['parent_id'];
        $category->name = $validatedData['name'];

        // Generate slug from the provided input or fallback to name
        $category->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);

        // Set status: 1 if active, 0 otherwise
        $category->status = $request->has('status') ? 1 : 0;

        // Handle image upload if present
        if ($request->hasFile('image')) {

            // Delete old image files if they exist (now works because $category is loaded)
            if ($category->image) {
                @unlink(public_path('uploads/categories/' . $category->image));
                @unlink(public_path('uploads/categories/thumbnails/' . $category->image));
            }

            $file = $request->file('image');
            $imageName = time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();
            $imageService->generateThumbnailImage($file, $imageName, 'uploads/categories', 124, 124);
            $file->move(public_path('uploads/categories'), $imageName);

            $category->image = $imageName;
        }

        $category->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            @unlink(public_path('uploads/categories/' . $category->image));
            @unlink(public_path('uploads/categories/thumbnails/' . $category->image));
        }

        $category->delete();

        // Redirect to the index page with a success message
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}
