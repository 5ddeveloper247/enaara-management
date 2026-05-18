<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftRosterPdfExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDateRange = $this->input('export_period_type') === 'date_range';

        return [
            'export_period_type' => ['nullable', 'in:month,date_range'],
            'year' => [Rule::requiredIf(! $isDateRange), 'integer', 'min:2000', 'max:2100'],
            'month' => [Rule::requiredIf(! $isDateRange), 'integer', 'min:1', 'max:12'],
            'start_date' => [Rule::requiredIf($isDateRange), 'date', 'date_format:Y-m-d'],
            'end_date' => [
                Rule::requiredIf($isDateRange),
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'export_layout' => ['nullable', 'in:calendar,tabular,per_employee'],
            'employee_group' => ['required', 'in:internal,third_party'],
            'include_shift_times' => ['nullable', 'boolean'],
            'include_department_grouping' => ['nullable', 'boolean'],
            'include_deleted' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'month.required' => 'Month is required.',
            'month.min' => 'Month must be between 1 and 12.',
            'month.max' => 'Month must be between 1 and 12.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'employee_group.required' => 'Employee group is required.',
            'employee_group.in' => 'Employee group is invalid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('export_period_type') !== 'date_range') {
                return;
            }

            $start = $this->input('start_date');
            $end = $this->input('end_date');
            if (! $start || ! $end) {
                return;
            }

            $startDate = \Carbon\Carbon::parse($start);
            $endDate = \Carbon\Carbon::parse($end);
            $maxDays = 366;

            if ($startDate->diffInDays($endDate) + 1 > $maxDays) {
                $v->errors()->add('end_date', 'Date range may not exceed ' . $maxDays . ' days.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $exportPeriodType = $this->input('export_period_type', 'month');
        if (! in_array($exportPeriodType, ['month', 'date_range'], true)) {
            $exportPeriodType = 'month';
        }

        $exportLayout = $this->input('export_layout', 'per_employee');
        if (! in_array($exportLayout, ['calendar', 'tabular', 'per_employee'], true)) {
            $exportLayout = 'per_employee';
        }

        $merge = [
            'export_period_type' => $exportPeriodType,
            'export_layout' => $exportLayout,
            'employee_group' => $this->input('employee_group', 'internal'),
            'include_shift_times' => filter_var($this->input('include_shift_times'), FILTER_VALIDATE_BOOLEAN),
            'include_department_grouping' => filter_var(
                $this->input('include_department_grouping', true),
                FILTER_VALIDATE_BOOLEAN
            ),
            'include_deleted' => filter_var($this->input('include_deleted'), FILTER_VALIDATE_BOOLEAN),
        ];

        if ($exportPeriodType === 'date_range') {
            $merge['start_date'] = $this->input('start_date');
            $merge['end_date'] = $this->input('end_date');
        } else {
            $merge['year'] = (int) $this->input('year');
            $merge['month'] = (int) $this->input('month');
        }

        $this->merge($merge);
    }
}
