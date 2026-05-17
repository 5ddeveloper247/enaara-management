<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkShiftRosterRequest extends FormRequest
{
    private const WEEKDAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $refs = collect($this->input('employee_ids', []))
                ->map(fn ($r) => trim((string) $r))
                ->filter()
                ->unique()
                ->values();

            if ($refs->isEmpty()) {
                return;
            }

            $employeeIds = [];
            $outsourcedIds = [];

            foreach ($refs as $ref) {
                [$type, $id] = array_pad(explode(':', $ref, 2), 2, null);
                $id = (int) $id;
                if (! $type || ! $id) {
                    $v->errors()->add('employee_ids', 'Invalid employee selection format.');
                    return;
                }
                if ($type === 'employee') {
                    $employeeIds[] = $id;
                    continue;
                }
                if ($type === 'outsourced') {
                    $outsourcedIds[] = $id;
                    continue;
                }
                $v->errors()->add('employee_ids', 'Invalid employee type selected.');
                return;
            }

            if ($employeeIds !== []) {
                $validEmployeeCount = Employee::query()
                    ->whereKey($employeeIds)
                    ->where('engagement_mode', 'shifts')
                    ->count();
                if ($validEmployeeCount !== count(array_unique($employeeIds))) {
                    $v->errors()->add('employee_ids', 'Only shift-based employees can be assigned.');
                    return;
                }
            }

            if ($outsourcedIds !== []) {
                $validOutsourcedCount = OutsourcedEmployee::query()
                    ->whereKey($outsourcedIds)
                    ->whereNull('deleted_at')
                    ->count();
                if ($validOutsourcedCount !== count(array_unique($outsourcedIds))) {
                    $v->errors()->add('employee_ids', 'One or more selected outsourced employees are invalid.');
                }
            }

            $days = collect($this->input('days', []))
                ->map(static fn ($day) => strtolower(trim((string) $day)))
                ->filter()
                ->unique()
                ->values();
            $offDays = collect($this->input('off_days', []))
                ->map(static fn ($day) => strtolower(trim((string) $day)))
                ->filter()
                ->unique()
                ->values();

            if ($days->isEmpty() && $offDays->isEmpty()) {
                $v->errors()->add('days', 'Select at least one working day or off day.');
            }

            if ($days->intersect($offDays)->isNotEmpty()) {
                $v->errors()->add('off_days', 'A day cannot be both a working day and an off day.');
            }

            $isCustomTime = filter_var($this->input('is_custom_time'), FILTER_VALIDATE_BOOLEAN);
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $shiftId = $this->input('shift_planner_id');
            $hasTimes = $startTime && $endTime;
            $hasPartialTimes = ($startTime && ! $endTime) || (! $startTime && $endTime);

            if ($isCustomTime) {
                if ($shiftId) {
                    $v->errors()->add('shift_planner_id', 'Clear the shift selection when using custom time.');
                }
                if (! $hasTimes) {
                    $v->errors()->add('start_time', 'Start and end time are required for custom shifts.');
                    $v->errors()->add('end_time', 'Start and end time are required for custom shifts.');
                }
            } elseif (! $shiftId) {
                $v->errors()->add('shift_planner_id', 'Select a shift or enable custom start and end time.');
            }

            if ($hasPartialTimes) {
                $v->errors()->add('start_time', 'Both start and end time are required when using custom times.');
                $v->errors()->add('end_time', 'Both start and end time are required when using custom times.');
            }

            if ($hasTimes && $startTime === $endTime) {
                $v->errors()->add('end_time', 'End time must be different from start time.');
            }
        });
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            // Employees
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'string', 'regex:/^(employee|outsourced):\d+$/'],

            'shift_planner_id' => ['nullable', 'integer', 'exists:shift_planners,id'],
            'is_custom_time' => ['nullable', 'boolean'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],

            // Dates
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'array'],
            'days.*' => ['string', Rule::in(self::WEEKDAYS)],
            'off_days' => ['nullable', 'array'],
            'off_days.*' => ['string', Rule::in(self::WEEKDAYS)],
            'assign_mode' => ['required', 'string', 'in:default,custom'],

            // Options
            'check_conflicts' => ['nullable', 'boolean'],
            'override_existing' => ['nullable', 'boolean'],
            'exclude_weekends' => ['nullable', 'boolean'],

            // Optional
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Please select at least one employee.',
            'employee_ids.array' => 'Employees must be a valid array.',
            'employee_ids.min' => 'Select at least one employee.',
            'employee_ids.*.exists' => 'One or more selected employees are invalid.',
            'employee_ids.*.regex' => 'Employee selection format is invalid.',

            'shift_planner_id.exists' => 'Selected shift does not exist.',
            'start_time.date_format' => 'Start time must be a valid time.',
            'end_time.date_format' => 'End time must be a valid time.',

            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be valid.',

            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be valid.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',

            'notes.max' => 'Notes must not exceed 1000 characters.',
        ];
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation(): void
    {
        $shiftPlannerId = $this->input('shift_planner_id');
        $isCustomTime = filter_var($this->input('is_custom_time'), FILTER_VALIDATE_BOOLEAN);

        $this->merge([
            'check_conflicts' => filter_var($this->check_conflicts, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'override_existing' => filter_var($this->override_existing, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'exclude_weekends' => filter_var($this->exclude_weekends, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'is_custom_time' => $isCustomTime,
            'shift_planner_id' => $isCustomTime || $shiftPlannerId === '' || $shiftPlannerId === null
                ? null
                : (int) $shiftPlannerId,
        ]);
    }
}
