<?php

namespace App\Http\Requests\Admin\ShiftPlanner;

use App\Services\EmployeeViewerScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftPlannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_id' => $this->filled('organization_id') ? (int) $this->input('organization_id') : $this->input('organization_id'),
            'sbu_id' => $this->filled('sbu_id') ? (int) $this->input('sbu_id') : $this->input('sbu_id'),
        ]);
    }

    public function rules(): array
    {
        $shiftId = $this->route('id');
        $orgId = (int) $this->input('organization_id');
        $sbuId = $this->resolveValidationSbuId();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('shift_planners', 'name')
                    ->ignore($shiftId)
                    ->where(function ($query) use ($sbuId) {
                        $query->whereNull('deleted_at');
                        if ($sbuId > 0) {
                            $query->where('sbu_id', $sbuId);
                        }
                    }),
            ],

            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('shift_planners', 'code')->ignore($shiftId),
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('shift_planners', 'start_time')
                    ->ignore($shiftId)
                    ->where(function ($query) use ($sbuId) {
                        $query
                            ->where('end_time', $this->input('end_time'))
                            ->whereNull('deleted_at');
                        if ($sbuId > 0) {
                            $query->where('sbu_id', $sbuId);
                        }
                    }),
            ],
            'end_time' => ['required', 'date_format:H:i'],

            'clock_in_window_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'clock_out_window_minutes' => ['required', 'integer', 'min:0', 'max:120'],

            'grace_period_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'break_time_minutes' => ['required', 'integer', 'min:0', 'max:180'],

            'overtime_allowed' => ['required', 'boolean'],

            'overtime_trigger_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
                'required_if:overtime_allowed,1',
            ],

            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($this->user()?->isSystemAdminUser()) {
            $rules['organization_id'] = ['required', 'integer', 'exists:organizations,id'];
            $rules['sbu_id'] = [
                'required',
                'integer',
                Rule::exists('sbus', 'id')->where(fn ($query) => $query->where('organization_id', $orgId)),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',
            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU is invalid for this organization.',
            'name.unique' => 'Shift name already exists for the selected SBU.',
            'start_time.unique' => 'Shift already registered on this time for the selected SBU.',
        ];
    }

    private function resolveValidationSbuId(): int
    {
        if ($this->user()?->isSystemAdminUser()) {
            return (int) $this->input('sbu_id');
        }

        $viewerSbuId = app(EmployeeViewerScopeService::class)->resolveViewerSbuId($this->user());

        return $viewerSbuId ? (int) $viewerSbuId : 0;
    }
}
