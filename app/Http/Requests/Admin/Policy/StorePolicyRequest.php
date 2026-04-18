<?php

namespace App\Http\Requests\Admin\Policy;

use App\Models\Sbu;
use App\Models\SbuFloor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_id' => $this->input('organization_id') === '' || $this->input('organization_id') === null
                ? null
                : $this->input('organization_id'),
            'sbu_id' => $this->input('sbu_id') === '' || $this->input('sbu_id') === null
                ? null
                : $this->input('sbu_id'),
            'sbu_floor_id' => $this->input('sbu_floor_id') === '' || $this->input('sbu_floor_id') === null
                ? null
                : $this->input('sbu_floor_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'required|in:Leave Policy,Attendance Grace Period,Geofencing Rules,Shift Rota Protocols,Security Policy,HR Policy',
            'status' => 'required|in:active,draft,archived',
            'effective_date' => 'required|date',
            'applicable_to' => 'required|in:global,organization,sbu,floor',
            'organization_id' => [
                'nullable',
                'integer',
                'exists:organizations,id',
                Rule::requiredIf(fn () => in_array($this->input('applicable_to'), ['organization', 'sbu', 'floor'], true)),
            ],
            'sbu_id' => [
                'nullable',
                'integer',
                'exists:sbus,id',
                Rule::requiredIf(fn () => in_array($this->input('applicable_to'), ['sbu', 'floor'], true)),
            ],
            'sbu_floor_id' => [
                'nullable',
                'integer',
                'exists:sbu_floors,id',
                Rule::requiredIf(fn () => $this->input('applicable_to') === 'floor'),
            ],
            'description' => 'nullable|string|max:5000',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $applicableTo = $this->input('applicable_to');

            if ($applicableTo === 'sbu') {
                $orgId = (int) $this->input('organization_id');
                $sbuId = (int) $this->input('sbu_id');
                $sbu = Sbu::query()->find($sbuId);
                if (! $sbu || (int) $sbu->organization_id !== $orgId) {
                    $validator->errors()->add('sbu_id', 'Pick an SBU that belongs to the organization you selected (or change the organization).');
                }
            }

            if ($applicableTo === 'floor') {
                $orgId = (int) $this->input('organization_id');
                $sbuId = (int) $this->input('sbu_id');
                $floorId = (int) $this->input('sbu_floor_id');
                $floor = SbuFloor::query()->with('sbu')->find($floorId);
                if (! $floor || ! $floor->sbu) {
                    $validator->errors()->add('sbu_floor_id', 'That floor could not be found. Refresh the page and choose a floor again.');

                    return;
                }
                if ((int) $floor->sbu_id !== $sbuId) {
                    $validator->errors()->add('sbu_floor_id', 'Choose a floor that belongs to the SBU you selected.');
                }
                if ((int) $floor->sbu->organization_id !== $orgId) {
                    $validator->errors()->add('organization_id', 'The organization must match the SBU that owns this floor.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'title' => 'policy title',
            'category' => 'category',
            'status' => 'status',
            'effective_date' => 'effective date',
            'applicable_to' => 'scope',
            'organization_id' => 'organization',
            'sbu_id' => 'SBU',
            'sbu_floor_id' => 'floor',
            'description' => 'description',
            'document' => 'document',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Enter a policy title.',
            'title.max' => 'The policy title cannot be longer than 255 characters.',
            'category.required' => 'Choose a category from the list.',
            'category.in' => 'The category you chose is not valid. Please select a category again.',
            'status.required' => 'Choose whether this policy is draft, active, or archived.',
            'status.in' => 'Status must be draft, active, or archived.',
            'effective_date.required' => 'Choose the date this policy takes effect.',
            'effective_date.date' => 'Enter a valid date for the effective date.',
            'applicable_to.required' => 'Choose who this policy applies to (scope).',
            'applicable_to.in' => 'Scope must be Global, Organization specific, SBU specific, or Floor specific.',
            'organization_id.required' => 'Select an organization for this scope.',
            'organization_id.exists' => 'That organization is no longer available. Refresh the page and select again.',
            'organization_id.integer' => 'Organization selection is invalid.',
            'sbu_id.required' => 'Select an SBU for SBU or floor scope.',
            'sbu_id.exists' => 'That SBU is no longer available. Refresh the page and select again.',
            'sbu_id.integer' => 'SBU selection is invalid.',
            'sbu_floor_id.required' => 'Select a floor for floor-specific policies.',
            'sbu_floor_id.exists' => 'That floor is no longer available. Refresh the page and select again.',
            'sbu_floor_id.integer' => 'Floor selection is invalid.',
            'description.max' => 'The description cannot be longer than 5000 characters.',
            'document.mimes' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed.',
            'document.max' => 'The file must be 10 MB or smaller.',
        ];
    }
}
