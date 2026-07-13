<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->utype == 'ADM';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 1. Retrieve the 'brand' parameter from the URL.
        // If Route Model Binding is used, this returns the Model instance; otherwise, it returns the ID string.
        $brand = $this->route('brand');

        // Safeguard: Extract the ID if the route parameter returned the full Model object.
        $brandId = is_object($brand) ? $brand->id : $brand;

        return [
            'name' => 'required|string|max:255',
            // 'unique' rule will throw an error if the slug exists
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brandId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable', // Boolean input from forms often comes as string 'on'
        ];
    }
}
