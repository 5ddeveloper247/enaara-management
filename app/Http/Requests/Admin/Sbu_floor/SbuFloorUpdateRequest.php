<?php

namespace App\Http\Requests\Admin\Sbu_floor;

use Illuminate\Foundation\Http\FormRequest;

class SbuFloorUpdateRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'sbu_id' => ['required', 'integer', 'exists:sbus,id'],

            'name' => ['required', 'string', 'max:50'],

            'floor_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\-]+$/'],

            'floor_type' => [
                'required',
                'in:corporate,operational,mixed'
            ],

            'is_restricted' => ['required', 'boolean'],

            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [
            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU is invalid.',

            'name.required' => 'Floor name is required.',

            'floor_number.string' => 'Floor number must be valid text.',
            'floor_number.max' => 'Floor number may not exceed 50 characters.',
            'floor_number.regex' => 'Floor number may only contain letters, numbers, spaces, and hyphens.',

            'floor_type.required' => 'Floor type is required.',
            'floor_type.in' => 'Invalid floor type selected.',

            'is_restricted.required' => 'Restricted field is required.',

            'is_active.required' => 'Status is required.',
        ];
    }

    /**
     * Optional: Normalize boolean values
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_restricted' => filter_var($this->is_restricted, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
