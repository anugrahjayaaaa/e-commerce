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
    protected String $imagePath, $thumbnailPath;

    // Auto inject by Laravel for image service
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        $this->imagePath = "uploads/products/";
        $this->thumbnailPath = $this->imagePath . "thumbnails/";
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
            $image = $request->file('image');
            $imageName = time() . "_" . uniqid() . "." . $image->extension();

            $imageService->resizeAndSaveImage($image, $imageName, $this->imagePath, 507, 604);
            // thumbnails
            $imageService->resizeAndSaveImage($image, $imageName, $this->thumbnailPath, 270, 303);

            $product->image = $imageName;
        }

        // Handle images upload if present
        $gallery_arr = [];
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedfileExtion = ['jpg', 'png', 'jpeg', 'webp'];
            $files = $request->file('images');

            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedfileExtion);

                if ($gcheck) {
                    $gimageName = time() . "_" . uniqid() . "-" . $counter . "." . $gextension;

                    $imageService->resizeAndSaveImage($file, $gimageName, $this->imagePath, 570, 604);
                    // thumbnails
                    $imageService->resizeAndSaveImage($file, $gimageName, $this->thumbnailPath, 270, 303);

                    array_push($gallery_arr, $gimageName);
                    $counter = $counter + 1;
                }
            }

            $gallery_images = implode(',', $gallery_arr);
        }

        $product->images = $gallery_images;

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

        // Handle image upload if present (Main Image)
        if ($request->hasFile('image')) {
            if ($product->image) {
                @unlink(public_path('uploads/products/' . $product->image));
                @unlink(public_path('uploads/products/thumbnails/' . $product->image));
            }

            $image = $request->file('image');
            $imageName = time() . "_" . uniqid() . "." . $image->extension();

            $imageService->resizeAndSaveImage($image, $imageName, $this->imagePath, 507, 604);
            $imageService->resizeAndSaveImage($image, $imageName, $this->thumbnailPath, 270, 303);

            $product->image = $imageName;
        }

        // Optional: Handle case where user deletes the Main Image via the trash button (without uploading a new one)
        if ($request->has('deleted_main_image') && !$request->hasFile('image')) {
            if ($product->image) {
                @unlink(public_path('uploads/products/' . $product->image));
                @unlink(public_path('uploads/products/thumbnails/' . $product->image));
            }
            $product->image = null;
        }

        // ==========================================
        // HANDLE IMAGES UPLOAD (GALLERY) FOR UPDATE
        // ==========================================

        // 1. Convert current gallery images from database into an array
        $current_gallery = $product->images ? explode(',', $product->images) : [];

        // 2. PROCESS DELETIONS
        // FIXED: Changed from 'deleted_images' to 'deleted_gallery_images' to match the HTML/Blade input name
        if ($request->has('deleted_gallery_images')) {
            $deleted_images = (array) $request->input('deleted_gallery_images');

            foreach ($deleted_images as $del_img) {
                // Trim whitespace if any
                $del_img = trim($del_img);

                if (in_array($del_img, $current_gallery)) {
                    // FIXED: Added '/' before the filename so unlink can correctly locate the path
                    @unlink(public_path('uploads/products/' . $del_img));
                    @unlink(public_path('uploads/products/thumbnails/' . $del_img));

                    // Remove the file name from the current gallery array queue
                    $current_gallery = array_diff($current_gallery, [$del_img]);
                }
            }
        }

        // 3. PROCESS NEW UPLOADS
        if ($request->hasFile('images')) {
            $allowedfileExtion = ['jpg', 'png', 'jpeg', 'webp'];
            $files = $request->file('images');

            // Continue the counter from the number of remaining images + 1 to prevent filename conflicts
            $counter = count($current_gallery) + 1;

            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedfileExtion);

                if ($gcheck) {
                    // Using uniqid() ensures the filename is completely unique and won't overwrite older files with the same counter
                    $gimageName = time() . "-" . uniqid() . "-" . $counter . "." . $gextension;

                    $imageService->resizeAndSaveImage($file, $gimageName, $this->imagePath, 570, 604);
                    $imageService->resizeAndSaveImage($file, $gimageName, $this->thumbnailPath, 270, 303);

                    // Push the new filename into the updated gallery array
                    array_push($current_gallery, $gimageName);
                    $counter++;
                }
            }
        }

        // 4. Merge the array back into a comma-separated string to save into the database
        // Re-index the array using array_values after array_diff has modified it
        $current_gallery = array_values($current_gallery);
        $product->images = !empty($current_gallery) ? implode(',', $current_gallery) : null;

        $product->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // 1. Delete main image
        if ($product->image) {
            @unlink(public_path('uploads/products/' . $product->image));
            @unlink(public_path('uploads/products/thumbnails/' . $product->image));
        }

        // 2. Delete gallery images
        if ($product->images) {
            // Convert the comma-separated string of images into an array
            $gallery_images = explode(',', $product->images);

            foreach ($gallery_images as $gallery_img) {
                $gallery_img = trim($gallery_img); // Clean up any accidental spaces

                if (!empty($gallery_img)) {
                    @unlink(public_path('uploads/products/' . $gallery_img));
                    @unlink(public_path('uploads/products/thumbnails/' . $gallery_img));
                }
            }
        }

        // 3. Delete the product record from the database
        $product->delete();

        // Redirect to the index page with a success message
        // Note: Fixed the success message from 'Brand...' to 'Product deleted successfully!'
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }
}
