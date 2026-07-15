<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Http\Requests\Admin\GeneralBulkDeleteRequest;
use App\Models\Brand;
use App\Services\ImageService;
use App\Traits\HandlesModelImages; // Use the general trait
use Illuminate\Support\Str;

class BrandController extends Controller
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
        $this->mainPath = "uploads/brands/";
        $this->thumbnailPath = $this->mainPath . "thumbnails/";
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
            $query->where('name', 'LIKE', "%{$search}%");
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
    public function store(StoreBrandRequest $request)
    {
        // Retrieve the validated input data
        $validatedData = $request->validated();

        $brand = new Brand();
        $brand->name = $validatedData['name'];

        // Generate slug from the provided input or fallback to the brand name
        $brand->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);

        // Set status: 1 if active, 0 otherwise
        $brand->status = $request->has('status') ? 1 : 0;

        // Process image upload via Trait wrapper. 
        // We pass 'false' for the resize parameter to keep the original main image size.
        $brand->image = $this->handleSingleImageUpload($request, $brand, 'image', false);

        $brand->save();

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
    public function update(UpdateBrandRequest $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        // Retrieve the validated input data
        $validatedData = $request->validated();

        $brand->name = $validatedData['name'];
        $brand->slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($validatedData['name']);
        $brand->status = $request->has('status') ? 1 : 0;

        // Process image upload/update via Trait wrapper.
        // We pass 'false' for the resize parameter to keep the original main image size.
        $brand->image = $this->handleSingleImageUpload($request, $brand, 'image', false);

        $brand->save();

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);

        // Safely wipe the physical files linked to the model (Main & Thumbnail)
        $this->deleteModelImages($brand);

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully!');
    }

    /**
     * Remove multiple specified resources from storage at once.
     */
    public function bulkDestroy(GeneralBulkDeleteRequest $request)
    {
        $validatedData = $request->validated();
        $brands = Brand::whereIn('id', $validatedData['ids'])->get();

        foreach ($brands as $brand) {
            $this->deleteModelImages($brand);
            $brand->delete();
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brands deleted successfully!');
    }
}
