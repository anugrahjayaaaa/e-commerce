<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Admin\Product\ProductExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralBulkDeleteRequest;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use App\Traits\HandlesModelImages;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    use HandlesModelImages; // Implement the general trait

    // Required properties for the HandlesModelImages trait contract
    protected ImageService $imageService;
    protected string $mainPath;

    // Optional properties used by the trait dynamically
    protected string $thumbnailPath;
    protected array $mainDimensions = ['w' => 507, 'h' => 604];
    protected array $galleryDimensions = ['w' => 570, 'h' => 604];
    protected array $thumbDimensions = ['w' => 270, 'h' => 303];

    /**
     * Constructor: Auto-inject ImageService and set upload paths.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        $this->mainPath = "uploads/products/";
        $this->thumbnailPath = $this->mainPath . "thumbnails/";
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start with a base query and eager load relationships
        $query = Product::with(['brand', 'category']);

        // Get input parameters
        $search = $request->input('search');
        $category_id = $request->input('category');
        $brand_id = $request->input('brand');
        $status = $request->input('status');
        $sortBy = $request->input('sort_by', 'created_at'); // Default sort
        $sortOrder = $request->input('sort_order', 'desc'); // Default order

        // Apply Search Filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('SKU', 'LIKE', "%{$search}%");
            });
        }

        // Apply Category Filter
        if ($request->filled('category')) {
            $query->where('category_id', $category_id);
        }

        // Apply Brand Filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $brand_id);
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        // Apply Sorting Logic
        // We use leftJoin for relations to allow sorting by related names
        if ($sortBy === 'brand') {
            $query->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->select('products.*', 'brands.name as brand_name')
                ->orderBy('brands.name', $sortOrder);
        } elseif ($sortBy === 'category') {
            $query->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.*', 'categories.name as category_name')
                ->orderBy('categories.name', $sortOrder);
        } else {
            // Sort by direct columns
            // Validate column to prevent SQL injection (optional but recommended)
            $allowedSortColumns = ['name', 'regular_price', 'stock_status', 'status', 'created_at'];
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
            $query->orderBy($sortBy, $sortOrder);
        }

        // Execute pagination
        $products = $query->paginate(10)->withQueryString();

        $brands = Brand::select(['id', 'name'])->orderBy('name')->get();
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();

        return view('admin.products.index', compact('brands', 'categories', 'products'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::select(['id', 'name'])->orderBy('name')->get();
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();

        return view('admin.products.create', compact('brands', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();

        // Map validated request data into the Product model instance
        $product = new Product($validatedData);

        // Process images dynamically using the trait. 
        // We pass 'true' to ensure the main image is resized.
        $product->image = $this->handleSingleImageUpload($request, $product, 'image', true);
        $product->images = $this->handleGalleryImageUpload($request, $product, 'images');

        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $brands = Brand::select(['id', 'name'])->orderBy('name')->get();
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'brands', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $product = Product::findOrFail($id);

        // Update textual data
        $product->fill($validatedData);

        // Handle explicit removal of the main image via the UI trash button
        if ($request->has('deleted_main_image') && !$request->hasFile('image')) {
            $this->deleteSingleModelImage($product);
            $product->image = null;
        } else {
            // Handle normal single image upload/update
            $product->image = $this->handleSingleImageUpload($request, $product, 'image', true);
        }

        // Handle gallery synchronization (removals and new additions)
        $product->images = $this->handleGalleryImageUpload($request, $product, 'images');

        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Wipe all associated physical files safely
        $this->deleteModelImages($product);

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Remove multiple specified resources from storage at once.
     */
    public function bulkDestroy(GeneralBulkDeleteRequest $request)
    {
        $validatedData = $request->validated();
        $products = Product::whereIn('id', $validatedData['ids'])->get();

        foreach ($products as $product) {
            $this->deleteModelImages($product);
            $product->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Products deleted successfully!');
    }

    public function export()
    {
        // Generate the filename with the current date (Format: YYYY-MM-DD)
        $fileName = date('Y-m-d') . '-products.xlsx';

        return Excel::download(new ProductExport, $fileName);
    }
}
