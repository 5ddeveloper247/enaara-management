<?php

namespace App\Http\Requests\Admin\ThirdParty;

use App\Models\Sbu;
use App\Models\ThirdParty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThirdPartyUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->filled('third_party_name')) {
            $normalized['third_party_name'] = preg_replace('/\s+/', ' ', trim((string) $this->input('third_party_name')));
        }
        if ($this->filled('contact_person_name')) {
            $normalized['contact_person_name'] = preg_replace('/\s+/', ' ', trim((string) $this->input('contact_person_name')));
        }
        if ($this->filled('supervisor_name')) {
            $normalized['supervisor_name'] = preg_replace('/\s+/', ' ', trim((string) $this->input('supervisor_name')));
        }
        if ($this->filled('scope_of_work')) {
            $normalized['scope_of_work'] = preg_replace('/\s+/', ' ', trim((string) $this->input('scope_of_work')));
        }
        if ($this->filled('remarks')) {
            $normalized['remarks'] = preg_replace('/\s+/', ' ', trim((string) $this->input('remarks')));
        }
        if ($this->filled('specify_service_type')) {
            $normalized['specify_service_type'] = preg_replace('/\s+/', ' ', trim((string) $this->input('specify_service_type')));
        }
        if ($this->input('service_type') !== 'Other') {
            $normalized['specify_service_type'] = null;
        }
        $normalized['is_individual_contractor'] = $this->boolean('is_individual_contractor');
        $isIndividual = $normalized['is_individual_contractor'];
        $ntnDigits = preg_replace('/\D/', '', (string) $this->input('ntn', ''));
        $contractorDigits = preg_replace('/\D/', '', (string) $this->input('contractor_cnic', ''));
        if ($isIndividual) {
            $normalized['ntn'] = null;
            $normalized['contractor_cnic'] = $contractorDigits !== '' ? $contractorDigits : null;
        } else {
            $normalized['contractor_cnic'] = null;
            $normalized['ntn'] = $ntnDigits !== '' ? $ntnDigits : null;
        }
        if ($this->filled('mobile_number')) {
            $normalized['mobile_number'] = preg_replace('/\D/', '', (string) $this->input('mobile_number'));
        }
        if ($this->filled('supervisor_mobile_number')) {
            $normalized['supervisor_mobile_number'] = preg_replace('/\D/', '', (string) $this->input('supervisor_mobile_number'));
        }
        if ($this->filled('supervisor_cnic')) {
            $normalized['supervisor_cnic'] = preg_replace('/\D/', '', (string) $this->input('supervisor_cnic'));
        }
        if ($this->has('is_active')) {
            $normalized['is_active'] = $this->boolean('is_active') ? 1 : 0;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    protected function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    public function authorize(): bool
    {
        return (bool) validatePermissions('admin/third-party/edit');
    }

    public function rules(): array
    {
        $thirdPartyId = (int) $this->route('id');

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
                'min:2',
                'max:150',
                'regex:/^(?!.*[<>])(?=.*[A-Za-z])[\p{L}\p{M}\p{N}\p{Zs}\.\-\'",&()\/#]+$/u',
                function (string $attribute, mixed $value, \Closure $fail) use ($thirdPartyId) {
                    $sbuIds = array_values(array_unique(array_map('intval', (array) $this->input('sbu_ids', []))));
                    if ($sbuIds === []) {
                        return;
                    }

                    $normalizedInput = $this->normalizeName((string) $value);
                    $existingNames = ThirdParty::query()
                        ->whereKeyNot($thirdPartyId)
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
            'service_type'      => ['required', 'string', Rule::in(['Security', 'Housekeeping', 'Construction', 'MEP', 'Other'])],
            'specify_service_type' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('service_type') === 'Other'),
                'string',
                'min:3',
                'max:150',
                'regex:/^(?!.*[<>])(?=.*[A-Za-z])[\p{L}\p{M}\p{N}\p{Zs}\.\-\'",&()\/#]+$/u',
            ],
            'is_individual_contractor' => ['required', 'boolean'],
            'ntn'               => [
                Rule::requiredIf(fn () => ! $this->boolean('is_individual_contractor')),
                'nullable',
                'string',
                'regex:/^[0-9]{5,13}$/',
            ],
            'contractor_cnic'   => [
                Rule::requiredIf(fn () => $this->boolean('is_individual_contractor')),
                'nullable',
                'string',
                'regex:/^[0-9]{13,15}$/',
            ],
            'contact_person_name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^(?!.*[<>])(?=.*[A-Za-z])[\p{L}\p{M}\p{N}\p{Zs}\.\-\'"]+$/u'],
            'mobile_number'     => ['required', 'string', 'regex:/^[0-9]{11,15}$/'],
            'email'             => ['required', 'email:rfc,dns', 'max:150'],
            'supervisor_name'   => ['required', 'string', 'min:3', 'max:120', 'regex:/^(?!.*[<>])(?=.*[A-Za-z])[\p{L}\p{M}\p{N}\p{Zs}\.\-\'"]+$/u'],
            'supervisor_cnic'   => ['required', 'string', 'regex:/^[0-9]{13,15}$/'],
            'supervisor_mobile_number' => ['required', 'string', 'regex:/^[0-9]{11,15}$/'],
            'contract_start_date' => ['required', 'date'],
            'contract_end_date' => ['required', 'date', 'after_or_equal:contract_start_date'],
            'scope_of_work'     => ['required', 'string', 'min:5', 'max:500', 'regex:/^(?!.*[<>]).+$/'],
            'estimated_staff_count' => ['required', 'integer', 'min:1', 'max:100000'],
            'company_registration_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'contract_copy'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'remarks'           => ['nullable', 'string', 'max:500', 'regex:/^(?!.*[<>]).*$/'],
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
            'third_party_name.required' => 'Company name is required.',
            'third_party_name.min'      => 'Company name must be at least 2 characters.',
            'third_party_name.max'      => 'Company name cannot exceed 150 characters.',
            'third_party_name.regex'    => 'Company name must contain valid text and cannot include script tags or invalid symbols.',
            'service_type.required'     => 'Service type is required.',
            'service_type.in'           => 'Service type must be one of Security, Housekeeping, Construction, MEP, or Other.',
            'specify_service_type.required' => 'Please specify the service type.',
            'specify_service_type.min' => 'Specified service type must be at least 3 characters.',
            'specify_service_type.max' => 'Specified service type cannot exceed 150 characters.',
            'specify_service_type.regex' => 'Specified service type must contain letters and cannot include script tags or invalid symbols.',
            'is_individual_contractor.required' => 'Vendor type is required.',
            'ntn.required'              => 'NTN number is required for a registered company.',
            'ntn.regex'                 => 'NTN must be 5 to 13 digits only.',
            'contractor_cnic.required'  => 'Contractor CNIC is required for an individual contractor.',
            'contractor_cnic.regex'     => 'Contractor CNIC must be 13 to 15 digits.',
            'contact_person_name.required' => 'Primary contact person name is required.',
            'contact_person_name.regex' => 'Contact person name must contain valid text and cannot include script tags or invalid symbols.',
            'mobile_number.required'    => 'Primary mobile number is required.',
            'mobile_number.regex'       => 'Primary mobile number must be digits only and between 11 to 15 digits.',
            'email.required'            => 'Email address is required.',
            'email.email'               => 'Email address format is invalid.',
            'supervisor_name.required'  => 'Supervisor name is required.',
            'supervisor_name.regex'     => 'Supervisor name must contain valid text and cannot include script tags or invalid symbols.',
            'supervisor_cnic.required'  => 'Supervisor CNIC is required.',
            'supervisor_cnic.regex'     => 'Supervisor CNIC must be 13 to 15 digits.',
            'supervisor_mobile_number.required' => 'Supervisor mobile number is required.',
            'supervisor_mobile_number.regex' => 'Supervisor mobile number must be digits only and between 11 to 15 digits.',
            'contract_start_date.required' => 'Contract start date is required.',
            'contract_end_date.required' => 'Contract end date is required.',
            'contract_end_date.after_or_equal' => 'Contract end date must be same or after contract start date.',
            'scope_of_work.required'    => 'Scope of work is required.',
            'scope_of_work.min'         => 'Scope of work must be at least 5 characters.',
            'scope_of_work.max'         => 'Scope of work cannot exceed 500 characters.',
            'scope_of_work.regex'       => 'Scope of work cannot include script tags.',
            'estimated_staff_count.required' => 'Estimated staff count is required.',
            'estimated_staff_count.integer' => 'Estimated staff count must be a whole number.',
            'estimated_staff_count.min' => 'Estimated staff count must be at least 1.',
            'company_registration_document.mimes' => 'Company registration document must be PDF, JPG, JPEG, PNG, or WEBP.',
            'company_registration_document.max' => 'Company registration document size must not exceed 5MB.',
            'contract_copy.mimes'       => 'Contract copy must be PDF, JPG, JPEG, PNG, or WEBP.',
            'contract_copy.max'         => 'Contract copy size must not exceed 5MB.',
            'remarks.max'               => 'Remarks cannot exceed 500 characters.',
            'remarks.regex'             => 'Remarks cannot include script tags.',
            'is_active.required'       => 'Status is required.',
        ];
    }
}
