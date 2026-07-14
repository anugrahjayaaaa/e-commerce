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
    protected String $imagePath, $thumbnailPath;

    // Auto inject by Laravel for image service
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        $this->imagePath = "uploads/brands/";
        $this->thumbnailPath = $this->imagePath . "thumbnails/";
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
            $brand->image = $imageService->uploadAndProcessImage(
                $request->file('image'),
                public_path('uploads/brands'),            // Main directory (stores raw file)
                false,                                    // Do not resize the main image
                null,                                     // Dimensions not required
                $this->thumbnailPath,                     // Thumbnail directory
                ['w' => 124, 'h' => 124]                  // Thumbnail dimensions
            );
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

        // Handle image update if a new file is uploaded
        if ($request->hasFile('image')) {
            $brand->image = $imageService->uploadAndProcessImage(
                $request->file('image'),
                'uploads/brands',                         // Main directory path
                false,                                    // Do not resize the main image
                null,                                     // Dimensions not required
                $this->thumbnailPath,                     // Thumbnail directory path
                ['w' => 124, 'h' => 124],                 // Thumbnail dimensions
                $brand->image                             // Pass the old image name to delete it
            );
        }

        $brand->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, ImageService $imageService)
    {
        $brand = Brand::findOrFail($id);

        // Delete the main image and its thumbnail if they exist
        if ($brand->image) {
            $imageService->deleteSingleImage(
                $brand->image,
                $this->imagePath,
                $this->thumbnailPath
            );
        }

        $brand->delete();

        // Redirect to the index page with a success message
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully!');
    }
}
