<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $organizationId = $this->route('id');

        return [
            'parent_id' => [
                'nullable',
                'exists:organizations,id',
                'different:id',
                Rule::notIn([$organizationId]),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organizations', 'name')->ignore($organizationId),
            ],

            'code' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('organizations', 'code')->ignore($organizationId),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'tax_no' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('organizations', 'tax_no')->ignore($organizationId),
            ],

            'description' => 'nullable|string|max:5000',
            'address'     => 'nullable|string|max:50',

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => 'Selected parent organization does not exist.',
            'parent_id.different' => 'Organization cannot be its own parent.',
            'name.required' => 'Organization name is required.',
            'name.unique' => 'This organization name is already registered.',
            'code.unique' => 'This organization code is already taken.',
            'tax_no.unique' => 'This tax number is already registered.',
            'email.email' => 'Please enter a valid email address.',
            'is_active.required' => 'Status is required.',
            'is_active.boolean' => 'Status must be active or inactive.',
        ];
    }
}
