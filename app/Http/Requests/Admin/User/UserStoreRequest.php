<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
            'employee_id' => ['required', 'integer', 'exists:employees,id', 'unique:users,employee_id'],
            'role_id'     => ['required', 'integer', 'exists:roles,id'],
            'password'    => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
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
            'role_id.required'  => 'Please select a role.',
            'role_id.exists'    => 'Selected role does not exist.',
            'employee_id.required' => 'Please link this user to an employee.',
            'employee_id.exists'   => 'Selected employee does not exist.',
            'employee_id.unique'   => 'This employee already has a user account.',
            'password.required' => 'Password is required.',
            'password.confirmed'=> 'Password confirmation does not match.',
        ];
    }
}
