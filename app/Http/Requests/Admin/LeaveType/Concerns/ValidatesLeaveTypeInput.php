<?php

namespace App\Http\Requests\Admin\LeaveType\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesLeaveTypeInput
{
    protected function alphaNameRegex(): string
    {
        return "/^(?=.*[A-Za-z])[A-Za-z0-9][A-Za-z0-9\s\.\-&,\/()']*$/";
    }

    protected function leaveTypeRules(?int $ignoreLeaveTypeId = null): array
    {
        $orgId = (int) $this->input('organization_id');

        $nameRule = Rule::unique('leave_types', 'name')
            ->where(fn ($query) => $query->where('organization_id', $orgId));

        if ($ignoreLeaveTypeId) {
            $nameRule = $nameRule->ignore($ignoreLeaveTypeId);
        }

        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'sbu_ids' => ['required', 'array', 'min:1'],
            'sbu_ids.*' => [
                'integer',
                Rule::exists('sbus', 'id')->where(fn ($query) => $query->where('organization_id', $orgId)),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:' . $this->alphaNameRegex(),
                $nameRule,
            ],
            'leave_condition' => ['required', 'string', 'in:conditional,unconditional'],
            'code' => ['required', 'string', 'min:2', 'max:5', 'regex:/^[A-Z0-9]{2,5}$/'],
            'leave_category' => ['required', 'string', 'in:paid,unpaid,special,attendance'],
            'description' => ['nullable', 'string', 'max:250'],
            'annual_quota' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'is_active' => ['boolean'],
            'employment_type' => ['required', 'string', 'in:all,permanent,contract,probation'],
            'gender' => ['required', 'string', 'in:all,male,female'],
            'min_service_months' => ['required', 'integer', 'min:0', 'max:600'],
            'eligible_from' => ['required', 'string', 'in:doj,confirmation,custom'],
            'probation_eligible' => ['boolean'],
            'unit_of_leave' => ['required', 'string', 'in:days,hours'],
            'accrual_frequency' => ['nullable', 'string', 'in:monthly,quarterly,yearly,once_in_tenure,none'],
            'accrual_start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'carry_forward' => ['required', 'string', 'in:no,yes,as_earned'],
            'max_carry_forward_days' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'encashment_allowed' => ['required', 'string', 'in:no,yes,as_per_policy'],
            'encashment_rule' => ['nullable', 'string', 'in:full,partial,as_per_policy'],
            'max_consecutive_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'advance_notice_days' => ['required', 'integer', 'min:0', 'max:365'],
            'short_leave_applicable' => ['boolean'],
            'short_leave_max_hours' => ['nullable', 'integer', 'in:2,4,6'],
            'half_day_applicable' => ['boolean'],
        ];
    }

    protected function leaveTypeMessages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',
            'sbu_ids.required' => 'At least one SBU is required.',
            'sbu_ids.min' => 'At least one SBU is required.',
            'sbu_ids.*.exists' => 'One or more selected SBUs are invalid for this organization.',
            'name.required' => 'Leave type name is required.',
            'name.regex' => 'Leave type name must contain letters and cannot be only numbers.',
            'name.unique' => 'This leave type already exists for the selected organization.',
            'leave_condition.required' => 'Leave type is required.',
            'leave_condition.in' => 'Leave type must be Conditional leave or Unconditional leave.',
            'code.required' => 'Leave code is required.',
            'code.regex' => 'Leave code must be 2–5 uppercase letters or numbers.',
            'leave_category.required' => 'Leave category is required.',
            'leave_category.in' => 'Leave category is invalid.',
            'annual_quota.required' => 'Entitlement days are required.',
            'description.max' => 'Description must not exceed 250 characters.',
            'employment_type.required' => 'Employment type is required.',
            'gender.required' => 'Gender is required.',
            'min_service_months.required' => 'Minimum service is required.',
            'eligible_from.required' => 'Eligible from is required.',
            'unit_of_leave.required' => 'Unit of leave is required.',
            'carry_forward.required' => 'Carry forward is required.',
            'encashment_allowed.required' => 'Encashment allowed is required.',
            'advance_notice_days.required' => 'Advance notice days is required.',
        ];
    }

    protected function prepareLeaveTypeInput(): void
    {
        $merge = [
            'name' => $this->filled('name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('name'))) : $this->input('name'),
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->input('code'))) : $this->input('code'),
            'is_active' => $this->boolean('is_active'),
            'probation_eligible' => $this->boolean('probation_eligible'),
            'short_leave_applicable' => $this->boolean('short_leave_applicable'),
            'half_day_applicable' => $this->boolean('half_day_applicable'),
            'accrual_frequency' => $this->filled('accrual_frequency') ? $this->input('accrual_frequency') : null,
        ];

        if ($this->filled('description')) {
            $merge['description'] = trim(strip_tags((string) $this->input('description')));
        }

        if ($this->has('sbu_ids') && is_array($this->input('sbu_ids'))) {
            $merge['sbu_ids'] = array_values(array_filter(array_map('intval', $this->input('sbu_ids'))));
        }

        $this->merge($merge);
    }
}
