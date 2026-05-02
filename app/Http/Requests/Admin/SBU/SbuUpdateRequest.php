<?php

namespace App\Http\Requests\Admin\SBU;

use App\Models\Sbu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SbuUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $openingGracePeriod = null;
        $closingGracePeriod = null;
        if (array_key_exists('grace_period', $this->all())) {
            $g = $this->input('grace_period');
            if ($g !== null && $g !== '') {
                $openingGracePeriod = $g;
                $closingGracePeriod = $g;
            }
        } else {
            $openingGracePeriod = $this->input('opening_grace_period');
            $closingGracePeriod = $this->input('closing_grace_period');
            if (($openingGracePeriod === null || $openingGracePeriod === '') && ($closingGracePeriod !== null && $closingGracePeriod !== '')) {
                $openingGracePeriod = $closingGracePeriod;
            }
            if ($openingGracePeriod !== null && $openingGracePeriod !== '') {
                $closingGracePeriod = $openingGracePeriod;
            }
        }

        $this->merge([
            'name' => $this->filled('name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('name'))) : $this->input('name'),
            'city' => $this->filled('city') ? preg_replace('/\s+/', ' ', trim((string) $this->input('city'))) : $this->input('city'),
            'address' => $this->filled('address') ? trim((string) $this->input('address')) : $this->input('address'),
            'opening_grace_period' => $openingGracePeriod,
            'closing_grace_period' => $closingGracePeriod,
        ]);
    }

    protected function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sbuId = $this->route('id');

        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],

            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sbus', 'name')
                    ->where(fn ($query) => $query->where('organization_id', $this->input('organization_id')))
                    ->ignore($sbuId),
                function (string $attribute, mixed $value, \Closure $fail) use ($sbuId) {
                    $organizationId = (int) $this->input('organization_id');
                    if ($organizationId <= 0) {
                        return;
                    }
                    $normalizedInput = $this->normalizeName((string) $value);
                    $existingNames = Sbu::query()
                        ->where('organization_id', $organizationId)
                        ->where('id', '!=', $sbuId)
                        ->pluck('name');
                    foreach ($existingNames as $existingName) {
                        if ($this->normalizeName((string) $existingName) === $normalizedInput) {
                            $fail('This SBU name already exists in the selected organization.');
                            return;
                        }
                    }
                },
            ],

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
            'grace_period' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:600'],
            'opening_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
            'closing_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],

            'schedule_mode' => [
                Rule::requiredIf(fn () => $this->filled('organization_id')),
                'nullable',
                Rule::in(['standard', 'custom']),
            ],

            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',

            'name.required' => 'SBU name is required.',
            'name.unique' => 'This SBU name already exists in the selected organization.',

            'address.max' => 'Address cannot exceed 255 characters.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'working_days.*.in' => 'Selected working day is invalid.',
            'working_start_time.date_format' => 'Working start time must be in HH:MM format.',
            'working_end_time.date_format' => 'Working end time must be in HH:MM format.',
            'working_end_time.after' => 'Working end time must be after start time.',
            'grace_period.integer' => 'Grace period must be a valid number.',
            'grace_period.min' => 'Grace period cannot be negative.',
            'grace_period.max' => 'Grace period cannot exceed 600 minutes.',
            'opening_grace_period.integer' => 'Grace period must be a valid number.',
            'opening_grace_period.min' => 'Grace period cannot be negative.',
            'opening_grace_period.max' => 'Grace period cannot exceed 600 minutes.',
            'closing_grace_period.integer' => 'Grace period must be a valid number.',
            'closing_grace_period.min' => 'Grace period cannot be negative.',
            'closing_grace_period.max' => 'Grace period cannot exceed 600 minutes.',

            'schedule_mode.required' => 'Select Standard or Custom for selection mode.',
            'schedule_mode.in' => 'Selection mode must be Standard or Custom.',

            'is_active.required' => 'Status is required.',
        ];
    }
}
