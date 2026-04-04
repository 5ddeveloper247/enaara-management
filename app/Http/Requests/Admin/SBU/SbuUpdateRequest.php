<?php

namespace App\Http\Requests\Admin\SBU;

use Illuminate\Foundation\Http\FormRequest;

class SbuUpdateRequest extends FormRequest
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
        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],

            'name' => ['required', 'string', 'max:255'],

            'city' => ['nullable', 'string', 'max:255'],

            'address' => ['nullable', 'string', 'max:500'],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90'
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180'
            ],

            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Custom messages (optional)
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',

            'name.required' => 'SBU name is required.',

            'address.max' => 'Address cannot exceed 500 characters.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',

            'is_active.required' => 'Status is required.',
        ];
    }
}
