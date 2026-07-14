<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    protected $imageService;

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
        $products = Product::with('brand', 'category')->orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();

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

        $current_time = Carbon::now()->timespan();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_time . "." .$image->extension();

            $imageService->resizeAndSaveImage($image, $imageName, 'uploads/products', 507, 604);
            $imageService->resizeAndSaveImage($image, $imageName, 'uploads/products/thumbnails', 270, 303);

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
                    $gimageName = $current_time . "-" . $counter . "." . $gextension;

                    $imageService->resizeAndSaveImage($file, $gimageName, 'uploads/products', 570, 604);
                    $imageService->resizeAndSaveImage($file, $gimageName, 'uploads/products/thumbnails', 270, 303);

                    array_push($gallery_arr, $gimageName);
                    $counter = $counter + 1;
                }
            }

            $gallery_images = implode(',', $gallery_arr);
        }

        $product->images = $gallery_images;

        $product->save();

        // Redirect to the index page with a success message
        return redirect()->route('admin.products.index')->with('success', 'Category created successfully!');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
