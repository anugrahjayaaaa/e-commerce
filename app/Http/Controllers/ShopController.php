<?php

namespace App\Http\Controllers;

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


        $products = $query->paginate($perPage)->withQueryString();

        return view('shop.index', compact('products'));
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
