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
            'name'        => 'required|string|max:255|unique:organizations,name',
            'code'        => 'nullable|string|max:64|unique:organizations,code',
            'email'       => 'nullable|email|max:255',
            'tax_no'      => 'nullable|string|max:64|unique:organizations,tax_no',
            'description' => 'nullable|string|max:5000',
            'address'     => 'nullable|string|max:500',
            'is_active'   => 'required|boolean',
        ];
    }

    /**
     * Custom messages (optional but good practice)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Organization name is required.',
            'name.unique'   => 'This organization name is already registered.',
            'code.unique'   => 'This organization code is already taken.',
            'tax_no.unique' => 'This tax number is already registered.',
            'parent_id.exists' => 'Selected parent company does not exist.',
            'email.email'   => 'Please enter a valid email address.',
        ];
    }
}
