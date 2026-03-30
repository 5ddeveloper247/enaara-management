<?php

namespace App\Http\Requests\Admin\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'request_type'      => ['required', 'in:leave,overtime,regularization,shift'],
            'status'            => ['required', 'in:active,inactive'],
            'organization_id'   => ['nullable', 'exists:organizations,id'],
            'branch'            => ['nullable', 'string', 'max:100'],
            'approval_levels'   => ['required', 'array', 'min:1'],
            'approval_levels.*.role' => ['required', 'string', 'max:100'],
            'sla_hours'         => ['required', 'integer', 'min:1', 'max:720'],
            'escalate_to'       => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'approval_levels.required' => 'Please add at least one approval level.',
            'approval_levels.min'      => 'At least one approval level is required.',
        ];
    }
}
