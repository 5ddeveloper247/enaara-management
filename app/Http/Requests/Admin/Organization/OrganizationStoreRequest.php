<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // IMPORTANT: allow request
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'parent_id'   => 'nullable|exists:organizations,id',
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:64|unique:organizations,code',
            'email'       => 'nullable|email|max:255',
            'tax_no'      => 'nullable|string|max:64',
            'description' => 'nullable|string',
            'address'     => 'nullable|string',
            'is_active'   => 'required|boolean',
        ];
    }

    /**
     * Custom messages (optional but good practice)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Company name is required.',
            'code.unique'   => 'This company code is already taken.',
            'parent_id.exists' => 'Selected parent company does not exist.',
        ];
    }
}
