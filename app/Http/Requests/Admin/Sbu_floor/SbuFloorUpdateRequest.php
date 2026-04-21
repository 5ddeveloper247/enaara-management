<?php

namespace App\Http\Requests\Admin\Sbu_floor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $floorId = (int) $this->route('id');

        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'sbu_id' => [
                'required',
                'integer',
                Rule::exists('sbus', 'id')->where(function ($query) {
                    $organizationId = (int) $this->input('organization_id');
                    if (! $organizationId) {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $query->where('organization_id', $organizationId);
                }),
            ],

            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sbu_floors', 'name')
                    ->where(fn ($query) => $query->where('sbu_id', (int) $this->input('sbu_id')))
                    ->ignore($floorId),
            ],

            'floor_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\-]+$/'],

            'floor_type' => [
                'required',
                'in:corporate,operational,mixed',
            ],

            'is_restricted' => ['required', 'boolean'],

            'is_active' => ['required', 'boolean'],

            'biometric_device_ids' => ['nullable', 'array'],
            'biometric_device_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('biometric_devices', 'id')->where(function ($query) {
                    $sbuId = (int) $this->input('sbu_id');
                    if (! $sbuId) {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $query->where('sbu_id', $sbuId);
                }),
            ],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',
            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU is invalid or does not belong to selected organization.',

            'name.required' => 'Floor name is required.',
            'name.unique' => 'This floor name already exists for the selected SBU.',

            'floor_number.string' => 'Floor number must be valid text.',
            'floor_number.max' => 'Floor number may not exceed 50 characters.',
            'floor_number.regex' => 'Floor number may only contain letters, numbers, spaces, and hyphens.',

            'floor_type.required' => 'Floor type is required.',
            'floor_type.in' => 'Invalid floor type selected.',

            'is_restricted.required' => 'Restricted field is required.',

            'is_active.required' => 'Status is required.',

            'biometric_device_ids.array' => 'Biometric device selection must be a valid list.',
            'biometric_device_ids.*.exists' => 'One or more selected biometric devices are invalid for this SBU.',
            'biometric_device_ids.*.integer' => 'Each biometric device id must be valid.',
            'biometric_device_ids.*.distinct' => 'Duplicate biometric device selection is not allowed.',
        ];
    }

    /**
     * Optional: Normalize boolean values
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => $this->filled('name') ? trim((string) $this->input('name')) : $this->input('name'),
            'is_restricted' => filter_var($this->is_restricted, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            'biometric_device_ids' => array_values(array_unique(array_filter(array_map('intval', (array) $this->input('biometric_device_ids', []))))),
        ]);
    }
}
