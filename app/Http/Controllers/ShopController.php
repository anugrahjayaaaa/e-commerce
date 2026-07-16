<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('status', true);

        $sortBy = $request->input('sort_by', 'newest');

        match ($sortBy) {
            'price_asc' => $query->orderBy('sale_price', 'asc'),
            'price_desc' => $query->orderBy('sale_price', 'desc'),
            'featured' => $query->where('featured', true),
            default => $query->latest()
        };

        $perPage = $request->input('per_page', 12);

        if ($request->filled('brand')) {
            $brandIds = $request->input('brand', '[]');
            $query->whereIn('brand_id', $brandIds);
        }

        if ($request->filled('category')) {
            $categoryIds = $request->input('category', '[]');
            $query->whereIn('category_id', $categoryIds);
        }

        if($request->filled('min_price') && $request->input('max_price')){
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $query->whereBetween('sale_price', [$minPrice, $maxPrice]);
        }

        $products = $query->paginate($perPage)->withQueryString();

        $brands = Brand::withCount('products')->orderBy('name', 'asc')->get();
        $categories = Category::withCount('products')->orderBy('name', 'asc')->get();

        return view('shop.index', compact('products', 'brands', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
