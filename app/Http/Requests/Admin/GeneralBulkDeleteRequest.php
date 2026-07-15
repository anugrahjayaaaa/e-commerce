<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GeneralBulkDeleteRequest extends FormRequest
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
        /**
         * Since the URLs follow the pattern /admin/{table}/bulk,
         * $this->segment(1) returns 'admin'
         * $this->segment(2) returns the table name (e.g., 'brands', 'categories', 'products')
         */
        $tableName = $this->segment(2);

        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'required',
                'integer',
                "exists:{$tableName},id" // Dynamically validates against the targeted database table
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Please select at least one item to delete.',
            'ids.*.exists' => 'One or more selected items could not be found.',
        ];
    }
}
