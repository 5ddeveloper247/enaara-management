<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'   => ['nullable', 'exists:organizations,id'],
            'name'        => ['required', 'string', 'max:50', 'unique:organizations,name'],
            'code'        => ['nullable', 'string', 'max:10', 'unique:organizations,code'],
            'email'       => ['nullable', 'email', 'max:255'],
            'tax_no'      => ['nullable', 'string', 'max:10', 'unique:organizations,tax_no'],
            'description' => ['nullable', 'string', 'max:255'], // ✅ limit applied
            'address'     => ['nullable', 'string', 'max:255'],
            'is_active'   => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Organization name is required.',
            'name.unique'   => 'This organization name is already registered.',
            'code.unique'   => 'This organization code is already taken.',
            'tax_no.unique' => 'This tax number is already registered.',
            'parent_id.exists' => 'Selected parent company does not exist.',
            'email.email'   => 'Please enter a valid email address.',

            // ✅ NEW MESSAGE
            'description.max' => 'Description must not exceed 255 characters.',
        ];
    }
}