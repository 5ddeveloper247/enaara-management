<?php

namespace App\Http\Requests\Admin\ThirdParty;

use App\Models\Sbu;
use App\Models\ThirdParty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThirdPartyStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('third_party_name')) {
            $name = preg_replace('/\s+/', ' ', trim((string) $this->input('third_party_name')));
            $this->merge([
                'third_party_name' => $name,
            ]);
        }
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
        return [
            'organization_ids'   => [
                'required',
                'array',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $organizationIds = array_values(array_unique(array_map('intval', (array) $this->input('organization_ids', []))));
                    $sbuIds = array_values(array_unique(array_map('intval', (array) $this->input('sbu_ids', []))));
                    if ($organizationIds === [] || $sbuIds === []) {
                        return;
                    }

                    $sbuOrganizationIds = Sbu::query()
                        ->whereIn('id', $sbuIds)
                        ->pluck('organization_id')
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();

                    sort($organizationIds);
                    sort($sbuOrganizationIds);

                    if ($organizationIds !== $sbuOrganizationIds) {
                        $fail('Selected organizations must match the organizations of selected SBUs.');
                    }
                },
            ],
            'organization_ids.*' => ['integer', 'distinct', 'exists:organizations,id'],
            'sbu_ids'            => ['required', 'array', 'min:1'],
            'sbu_ids.*'          => [
                'integer',
                'distinct',
                Rule::exists('sbus', 'id')->where(function ($query) {
                    $organizationIds = array_map('intval', (array) $this->input('organization_ids', []));
                    if ($organizationIds === []) {
                        $query->whereRaw('1 = 0');
                        return;
                    }
                    $query->whereIn('organization_id', $organizationIds);
                }),
            ],
            'third_party_name'  => [
                'required',
                'string',
                'max:255',
                'regex:/[a-zA-Z]/',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $sbuIds = array_values(array_unique(array_map('intval', (array) $this->input('sbu_ids', []))));
                    if ($sbuIds === []) {
                        return;
                    }

                    $normalizedInput = $this->normalizeName((string) $value);
                    $existingNames = ThirdParty::query()
                        ->whereHas('sbus', function ($query) use ($sbuIds) {
                            $query->whereIn('sbus.id', $sbuIds);
                        })
                        ->pluck('third_party_name');

                    foreach ($existingNames as $existingName) {
                        if ($this->normalizeName((string) $existingName) === $normalizedInput) {
                            $fail('This third party already exists for one or more selected SBUs.');
                            return;
                        }
                    }
                },
            ],
            'city'              => ['nullable', 'string', 'max:255'],
            'address'           => ['nullable', 'string'],
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'is_active'         => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_ids.required' => 'At least one organization is required.',
            'organization_ids.array'    => 'Organizations must be provided as a list.',
            'organization_ids.min'      => 'Select at least one organization.',
            'organization_ids.*.exists' => 'One or more selected organizations are invalid.',
            'sbu_ids.required'          => 'At least one SBU is required.',
            'sbu_ids.array'             => 'SBUs must be provided as a list.',
            'sbu_ids.min'               => 'Select at least one SBU.',
            'sbu_ids.*.exists'          => 'One or more selected SBUs are invalid or do not belong to selected organizations.',
            'third_party_name.required'=> 'Third party name is required.',
            'third_party_name.regex'   => 'Third party name must contain at least one letter.',
            'latitude.between'         => 'Latitude must be between -90 and 90.',
            'longitude.between'        => 'Longitude must be between -180 and 180.',
            'is_active.required'       => 'Status is required.',
        ];
    }
}
