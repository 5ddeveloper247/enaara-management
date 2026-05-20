<?php

namespace App\Http\Requests\Admin\LeaveType;

use App\Http\Requests\Admin\LeaveType\Concerns\ValidatesLeaveTypeInput;
use Illuminate\Foundation\Http\FormRequest;

class LeaveTypeUpdateRequest extends FormRequest
{
    use ValidatesLeaveTypeInput;

    protected function prepareForValidation(): void
    {
        $this->prepareLeaveTypeInput();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->leaveTypeRules((int) $this->route('id'));
    }

    public function messages(): array
    {
        return $this->leaveTypeMessages();
    }
}
