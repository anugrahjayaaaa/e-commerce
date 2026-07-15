<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->utype == "ADM";
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'featured' => $this->has('featured') ? 1 : 0,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 1. Retrieve the 'product' parameter from the URL.
        // If Route Model Binding is used, this returns the Model instance; otherwise, it returns the ID string.
        $product = $this->route('product');

        // Safeguard: Extract the ID if the route parameter returned the full Model object.
        $productId = is_object($product) ? $product->id : $product;

        return [
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:products,slug,' . $productId,
            'information'       => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'required|string',
            'regular_price'     => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0|lt:regular_price',
            'SKU'               => 'required|string|max:100|unique:products,SKU,' . $productId,
            'stock_status'      => 'required|in:instock,outofstock',
            'quantity'          => 'required|integer|min:0',
            'featured'          => 'sometimes|boolean',
            'status'            => 'sometimes|boolean',
            'category_id'       => 'nullable|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images.*'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
