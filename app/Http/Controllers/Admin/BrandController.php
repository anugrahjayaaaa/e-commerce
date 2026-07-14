<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\ImageService;
use Illuminate\Support\Str;

class BrandController extends Controller
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
        $query = Brand::query();

        $search = request('search');
        $status = request('status');

        if ($search) {
            $query->where('name', 'LIKE', "{$search}");
        }

        if (request()->filled('status')) {
            $query->where('status', $status);
        }

        $brands = $query->orderBy('id', 'DESC')->paginate(10)->withQueryString();
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request, ImageService $imageService)
    {
        // Retrieve the validated input data
        $validatedData = $request->validated();

        $brand = new Brand();
        $brand->name = $validatedData['name'];

        // Generate slug from the provided input or fallback to name
        $brand->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);

        // Set status: 1 if active, 0 otherwise
        $brand->status = $request->has('status') ? 1 : 0;

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();

            // Generate thumbnail using the ImageService
            $imageService->generateThumbnailImage($file, $imageName, 'uploads/brands', 124, 124);

            // Move the original image to the public storage
            $file->move(public_path('uploads/brands'), $imageName);

            $brand->image = $imageName;
        }

        $brand->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully!');
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
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, string $id, ImageService $imageService)
    {
        $brand = Brand::findOrFail($id);

        // Retrieve the validated input data
        $validatedData = $request->validated();
        $brand->name = $validatedData['name'];

        // Generate slug from the provided input or fallback to name
        $brand->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);

        // Set status: 1 if active, 0 otherwise
        $brand->status = $request->has('status') ? 1 : 0;

        // Handle image upload if present
        if ($request->hasFile('image')) {

            // Delete old image files if they exist (now works because $brand is loaded)
            if ($brand->image) {
                @unlink(public_path('uploads/brands/' . $brand->image));
                @unlink(public_path('uploads/brands/thumbnails/' . $brand->image));
            }

            $file = $request->file('image');
            $imageName = time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();
            $imageService->generateThumbnailImage($file, $imageName, 'uploads/brands', 124, 124);
            $file->move(public_path('uploads/brands'), $imageName);

            $brand->image = $imageName;
        }

        $brand->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->image) {
            @unlink(public_path('uploads/brands/' . $brand->image));
            @unlink(public_path('uploads/brands/thumbnails/' . $brand->image));
        }

        $brand->delete();

        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully!');
    }
}
