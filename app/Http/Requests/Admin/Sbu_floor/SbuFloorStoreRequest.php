<?php

namespace App\Http\Requests\Admin\Sbu_floor;

use Illuminate\Foundation\Http\FormRequest;

class SbuFloorStoreRequest extends FormRequest
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

            'name' => ['required', 'string', 'max:255'],

            'floor_number' => ['nullable', 'integer', 'min:0'],

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

            'floor_number.integer' => 'Floor number must be a valid number.',

            'floor_type.required' => 'Floor type is required.',
            'floor_type.in' => 'Invalid floor type selected.',

            'is_restricted.required' => 'Restricted field is required.',

            'is_active.required' => 'Status is required.',
        ];
    }
}