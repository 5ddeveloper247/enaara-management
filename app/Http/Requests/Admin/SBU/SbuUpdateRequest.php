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

            'name' => ['required', 'string', 'max:50'],

            'city' => ['nullable', 'string', 'max:50'],

            'address' => ['nullable', 'string', 'max:255'],

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
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'working_start_time' => ['nullable', 'date_format:H:i'],
            'working_end_time' => ['nullable', 'date_format:H:i', 'after:working_start_time'],
            'opening_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
            'closing_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],

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

            'is_active.required' => 'Status is required.',
        ];
    }
}
