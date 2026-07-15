<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;

class ProductController extends Controller
{

    protected ImageService $imageService;
    protected String $mainPath, $thumbnailPath;

    // Auto inject by Laravel for image service
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        $this->mainPath = "uploads/products/";
        $this->thumbnailPath = $this->mainPath . "thumbnails/";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('brand', 'category')->orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products.index', compact('products'));
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
    public function store(StoreProductRequest $request, ImageService $imageService)
    {
        $validatedData = $request->validated();

        $product = new Product();
        $product->name = $validatedData['name'];
        $product->slug = $validatedData['slug'];
        $product->short_description = $validatedData['short_description'];
        $product->information = $validatedData['information'];
        $product->description = $validatedData['description'];
        $product->regular_price = $validatedData['regular_price'];
        $product->sale_price = $validatedData['sale_price'];
        $product->SKU = $validatedData['SKU'];
        $product->stock_status = $validatedData['stock_status'];
        $product->featured = $validatedData['featured'];
        $product->status = $validatedData['status'];
        $product->quantity = $validatedData['quantity'];
        $product->brand_id = $validatedData['brand_id'];
        $product->category_id = $validatedData['category_id'];

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $product->image = $imageService->uploadAndProcessImage(
                $request->file('image'),
                $this->mainPath,                         // Main directory (stores resized file)
                true,                                     // Resize the main image instead of moving raw file
                ['w' => 507, 'h' => 604],                 // Main image dimensions
                $this->thumbnailPath,                     // Thumbnail directory
                ['w' => 270, 'h' => 303]                  // Thumbnail dimensions
            );
        }

        // Handle product gallery images upload if present
        if ($request->hasFile('images')) {
            $galleryFiles = $request->file('images');

            // Process the new gallery uploads via ImageService
            $galleryArray = $imageService->processGalleryImages(
                $galleryFiles,
                $this->mainPath,
                ['w' => 570, 'h' => 604],
                $this->thumbnailPath,
                ['w' => 270, 'h' => 303]
            );

            // Save comma-separated string to the database
            $product->images = !empty($galleryArray) ? implode(',', $galleryArray) : null;
        }

        $product->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
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

        $product = Product::findOrFail($id);
        $brands = Brand::select(['id', 'name'])->orderBy('name')->get();
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'brands', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id, ImageService $imageService)
    {
        $validatedData = $request->validated();

        $product = Product::findOrFail($id);

        $product->name = $validatedData['name'];
        $product->slug = $validatedData['slug'];
        $product->short_description = $validatedData['short_description'];
        $product->information = $validatedData['information'];
        $product->description = $validatedData['description'];
        $product->regular_price = $validatedData['regular_price'];
        $product->sale_price = $validatedData['sale_price'];
        $product->SKU = $validatedData['SKU'];
        $product->stock_status = $validatedData['stock_status'];
        $product->featured = $validatedData['featured'];
        $product->status = $validatedData['status'];
        $product->quantity = $validatedData['quantity'];
        $product->brand_id = $validatedData['brand_id'];
        $product->category_id = $validatedData['category_id'];

        // Handle image update if a new file is uploaded
        if ($request->hasFile('image')) {
            $product->image = $imageService->uploadAndProcessImage(
                $request->file('image'),
                $this->mainPath,                         // Main directory path
                true,                                     // Resize the main image instead of moving raw file
                ['w' => 507, 'h' => 604],                 // Main image dimensions
                $this->thumbnailPath,                     // Thumbnail directory path
                ['w' => 270, 'h' => 303],                 // Thumbnail dimensions
                $product->image                           // Pass the old image name to delete it
            );
        }

        // Optional: Handle case where user explicitly deletes the Main Image via the trash button (without uploading a new one)
        if ($request->has('deleted_main_image') && !$request->hasFile('image')) {
            if ($product->image) {
                // Remove both main and thumbnail files physically using the service
                $imageService->deleteSingleImage(
                    $product->image,
                    $this->mainPath,
                    $this->thumbnailPath
                );
            }

            // Clear the column record in the database
            $product->image = null;
        }

        // ==========================================
        // HANDLE IMAGES UPLOAD (GALLERY) FOR UPDATE
        // ==========================================

        // Convert current gallery images from database into an array format
        $currentGallery = $product->images ? explode(',', $product->images) : [];

        // Step 1: Handle requested image deletions
        if ($request->has('deleted_gallery_images')) {
            $deletedImages = (array) $request->input('deleted_gallery_images');

            // Filter to ensure we only attempt to delete images that actually exist in the gallery records
            $validDeletions = array_intersect($deletedImages, $currentGallery);

            if (!empty($validDeletions)) {
                // Remove files physically from storage using service
                $imageService->deleteGalleryImages($validDeletions, $this->mainPath, $this->thumbnailPath);

                // Remove the deleted filenames from the database tracking queue
                $currentGallery = array_diff($currentGallery, $validDeletions);
            }
        }

        // Step 2: Handle new image uploads for the gallery
        if ($request->hasFile('images')) {
            $newGalleryFiles = $request->file('images');

            // Process and append new images to the existing gallery array
            $currentGallery = $imageService->processGalleryImages(
                $newGalleryFiles,
                $this->mainPath,
                ['w' => 570, 'h' => 604],
                $this->thumbnailPath,
                ['w' => 270, 'h' => 303],
                $currentGallery // Pass the existing remaining images to maintain index tracking
            );
        }

        // Step 3: Sync and update database record
        $currentGallery = array_values($currentGallery); // Re-index array keys cleanly
        $product->images = !empty($currentGallery) ? implode(',', $currentGallery) : null;

        $product->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, ImageService $imageService)
    {
        $product = Product::findOrFail($id);

        // 1. Delete the main product image and its thumbnail
        if ($product->image) {
            $imageService->deleteSingleImage(
                $product->image,
                $this->mainPath,
                $this->thumbnailPath
            );
        }

        // 2. Delete all product gallery images and their thumbnails
        if ($product->images) {
            // Convert the comma-separated string from database into an array
            $galleryImages = explode(',', $product->images);

            // Pass the array straight to the service for bulk deletion
            $imageService->deleteGalleryImages(
                $galleryImages,
                $this->mainPath,
                $this->thumbnailPath
            );
        }

        // 3. Delete the product record from the database
        $product->delete();

        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }
}
