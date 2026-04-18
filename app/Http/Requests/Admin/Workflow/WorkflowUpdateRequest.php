<?php

namespace App\Http\Requests\Admin\Workflow;

use App\Models\Sbu;
use Illuminate\Foundation\Http\FormRequest;

class WorkflowUpdateRequest extends FormRequest
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
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'request_type' => ['required', 'in:leave,overtime,regularization,shift'],
            'status' => ['required', 'in:active,inactive'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'sbu_id' => ['nullable', 'integer', 'exists:sbus,id'],
            'approval_levels' => ['required', 'array', 'min:1'],
            'approval_levels.*.role' => ['required', 'string', 'max:100'],
            'sla_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'escalate_to' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $orgId = $this->input('organization_id');
            $sbuId = $this->input('sbu_id');
            if ($sbuId) {
                if (! $orgId) {
                    $validator->errors()->add('organization_id', 'Select an organization when an SBU is chosen.');

                    return;
                }
                $sbu = Sbu::query()->find((int) $sbuId);
                if (! $sbu || (int) $sbu->organization_id !== (int) $orgId) {
                    $validator->errors()->add('sbu_id', 'The selected SBU must belong to the selected organization.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'approval_levels.required' => 'Please add at least one approval level.',
            'approval_levels.min' => 'At least one approval level is required.',
        ];
    }
}
