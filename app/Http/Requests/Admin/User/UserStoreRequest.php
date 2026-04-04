<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->whereNotNull('role_id'), 'unique:users,employee_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Full name is required.',
            'name.regex'        => 'Name must contain at least one letter.',
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'email.unique'      => 'This email is already registered.',
            'employee_id.required' => 'Please link this user to an employee.',
            'employee_id.exists'   => 'Selected employee does not exist or does not have a role assigned.',
            'employee_id.unique'   => 'This employee already has a user account.',
        ];
    }
}
