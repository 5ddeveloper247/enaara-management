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
                'max:50',
                Rule::unique('organizations', 'name')->ignore($organizationId),
            ],

            'code' => [
                'nullable',
                'string',
                'max:10',
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
                'max:10',
                Rule::unique('organizations', 'tax_no')->ignore($organizationId),
            ],

            'description' => 'nullable|string|max:255',
            'address'     => 'nullable|string|max:255',
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'working_start_time' => ['nullable', 'date_format:H:i'],
            'working_end_time' => ['nullable', 'date_format:H:i', 'after:working_start_time'],
            'opening_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
            'closing_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],

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
            'description.max' => 'Description must not exceed 255 characters.',
            'address.max' => 'Address must not exceed 255 characters.',
            'working_days.*.in' => 'Selected working day is invalid.',
            'working_start_time.date_format' => 'Working start time must be in HH:MM format.',
            'working_end_time.date_format' => 'Working end time must be in HH:MM format.',
            'working_end_time.after' => 'Working end time must be after start time.',
            'opening_grace_period.integer' => 'Opening grace period must be a valid number.',
            'opening_grace_period.min' => 'Opening grace period cannot be negative.',
            'opening_grace_period.max' => 'Opening grace period cannot exceed 600 minutes.',
            'closing_grace_period.integer' => 'Closing grace period must be a valid number.',
            'closing_grace_period.min' => 'Closing grace period cannot be negative.',
            'closing_grace_period.max' => 'Closing grace period cannot exceed 600 minutes.',
        ];
    }
}
