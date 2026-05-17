<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Services\ShiftRosterService;
use Illuminate\Foundation\Http\FormRequest;

class ShiftRosterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'employee_type' => ['required', 'in:employee,outsourced'],
            'employee_id' => [
                'required',
                'integer',
            ],
            'shift_planner_id' => ['nullable', 'integer', 'exists:shift_planners,id'],
            'is_custom_time' => ['nullable', 'boolean'],
            'roster_date' => ['required', 'date'],

            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'sbu_floor_id' => ['required', 'integer', 'exists:sbu_floors,id'],
            'location_text' => ['nullable', 'string', 'min:3', 'max:15', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s\-\'\.]+$/'],
            'late_check_in' => ['nullable', 'boolean'],

            'status' => ['nullable', 'integer', 'in:0,1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'employee_type.required' => 'Employee type is required.',
            'employee_type.in' => 'Employee type is invalid.',
            'employee_id.required' => 'Employee is required.',
            'employee_id.integer' => 'Selected employee is invalid.',

            'shift_planner_id.required' => 'Shift is required.',
            'shift_planner_id.exists' => 'Selected shift does not exist.',

            'roster_date.required' => 'Roster date is required.',
            'roster_date.date' => 'Roster date must be a valid date.',

            'status.in' => 'Status must be either assigned or cancelled.',

            'notes.max' => 'Notes may not be greater than 1000 characters.',
            'sbu_floor_id.required' => 'Floor is required.',
            'sbu_floor_id.integer' => 'Selected floor is invalid.',
            'sbu_floor_id.exists' => 'Selected floor does not exist.',
            'location_text.min' => 'Location must be at least 3 characters.',
            'location_text.max' => 'Location may not be greater than 15 characters.',
            'location_text.regex' => 'Location must contain letters and may include numbers, spaces, or hyphens.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $type = $this->input('employee_type');
            $id = (int) $this->input('employee_id');
            if (! $type || ! $id) {
                return;
            }

            $floorId = $this->input('sbu_floor_id');
            $allowedIds = collect(app(ShiftRosterService::class)->floorOptionsForAssignee($type, $id))
                ->pluck('id')
                ->map(fn ($value) => (int) $value)
                ->all();

            if (! in_array((int) $floorId, $allowedIds, true)) {
                $v->errors()->add('sbu_floor_id', 'Selected floor is not available for this employee.');
            }

            $isCustomTime = filter_var($this->input('is_custom_time'), FILTER_VALIDATE_BOOLEAN);
            $shiftId = $this->input('shift_planner_id');
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
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
                $v->errors()->add('shift_planner_id', 'Select a shift from the list or enable custom start and end time.');
            }

            if ($hasPartialTimes) {
                $v->errors()->add('start_time', 'Both start and end time are required when using custom times.');
                $v->errors()->add('end_time', 'Both start and end time are required when using custom times.');
            }

            if ($hasTimes && $startTime === $endTime) {
                $v->errors()->add('end_time', 'End time must be different from start time.');
            }

            if ($type === 'employee') {
                $exists = Employee::query()
                    ->whereKey($id)
                    ->where('engagement_mode', 'shifts')
                    ->exists();
                if (! $exists) {
                    $v->errors()->add('employee_id', 'Selected employee does not exist or is not shift-based.');
                }
                return;
            }

            $exists = OutsourcedEmployee::query()
                ->whereKey($id)
                ->whereNull('deleted_at')
                ->exists();
            if (! $exists) {
                $v->errors()->add('employee_id', 'Selected outsourced employee does not exist.');
            }
        });
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation(): void
    {
        $floorId = $this->input('sbu_floor_id');

        $shiftPlannerId = $this->input('shift_planner_id');
        $isCustomTime = filter_var($this->input('is_custom_time'), FILTER_VALIDATE_BOOLEAN);

        $merge = [
            'employee_type' => $this->input('employee_type', 'employee'),
            'status' => $this->has('status') ? $this->status : 1,
            'late_check_in' => $this->has('late_check_in') ? true : false,
            'sbu_floor_id' => $floorId === '' || $floorId === null ? null : (int) $floorId,
            'is_custom_time' => $isCustomTime,
            'shift_planner_id' => $isCustomTime || $shiftPlannerId === '' || $shiftPlannerId === null
                ? null
                : (int) $shiftPlannerId,
        ];

        if ($this->has('notes')) {
            $notes = $this->input('notes');
            if ($notes !== null && $notes !== '') {
                $notes = trim(strip_tags((string) $notes));
            } else {
                $notes = null;
            }
            $merge['notes'] = $notes;
        }

        if ($this->has('location_text')) {
            $location = $this->input('location_text');
            if ($location !== null && $location !== '') {
                $location = trim(strip_tags((string) $location));
                $location = $location === '' ? null : $location;
            } else {
                $location = null;
            }
            $merge['location_text'] = $location;
        }

        $this->merge($merge);
    }
}
