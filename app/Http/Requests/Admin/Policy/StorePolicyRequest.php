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
                    $validator->errors()->add('sbu_id', 'The selected SBU must belong to the selected organization.');
                }
            }

            if ($applicableTo === 'floor') {
                $orgId = (int) $this->input('organization_id');
                $sbuId = (int) $this->input('sbu_id');
                $floorId = (int) $this->input('sbu_floor_id');
                $floor = SbuFloor::query()->with('sbu')->find($floorId);
                if (! $floor || ! $floor->sbu) {
                    $validator->errors()->add('sbu_floor_id', 'The selected floor is invalid.');

                    return;
                }
                if ((int) $floor->sbu_id !== $sbuId) {
                    $validator->errors()->add('sbu_floor_id', 'The selected floor must belong to the selected SBU.');
                }
                if ((int) $floor->sbu->organization_id !== $orgId) {
                    $validator->errors()->add('organization_id', 'The selected organization must match the floor\'s SBU.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The policy title is required.',
            'category.required' => 'Please select a category.',
            'status.required' => 'Please select a status.',
            'effective_date.required' => 'The effective date is required.',
            'applicable_to.required' => 'Please select the scope.',
            'organization_id.required' => 'Please select an organization.',
            'sbu_id.required' => 'Please select an SBU.',
            'sbu_floor_id.required' => 'Please select a floor.',
            'document.mimes' => 'Only PDF and Word documents are allowed.',
            'document.max' => 'Document size must not exceed 10MB.',
        ];
    }
}
