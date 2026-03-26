<?php

namespace App\Http\Requests\Admin\LeaveCalendar;

use Illuminate\Foundation\Http\FormRequest;

class LeaveCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_recurring' => $this->boolean('is_recurring'),
            'is_blackout' => $this->boolean('is_blackout'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date', 
            'is_recurring' => 'boolean', 
            'organization_scope' => 'nullable|in:all,specific',
            'is_blackout' => 'boolean',
            'organizations' => 'nullable|array|required_if:organization_scope,specific',
            'organizations.*' => 'exists:organizations,id',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'start_date.required' => 'The start date field is required.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end_date.required' => 'The end date field is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'organizations.required_if' => 'Please select at least one organization when scope is specific.',
        ];
    }
}

