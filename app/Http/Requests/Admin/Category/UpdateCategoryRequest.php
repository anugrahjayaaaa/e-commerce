<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->utype == "ADM";
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 1. Retrieve the 'category' parameter from the URL.
        // If Route Model Binding is used, this returns the Model instance; otherwise, it returns the ID string.
        $category = $this->route('category');

        // Safeguard: Extract the ID if the route parameter returned the full Model object.
        $categoryId = is_object($category) ? $category->id : $category;

        return [
            'parent_id' => [
                'nullable',
                'integer',
                // Verify that the parent_id exists in the categories table
                'exists:categories,id',
                // Prevent the category from being its own parent
                Rule::notIn($categoryId),
            ],
            'name' => 'required|string|max:255',
            // 'unique' rule will throw an error if the slug exists
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $categoryId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable', // Boolean input from forms often comes as string 'on'
        ];
    }
}
