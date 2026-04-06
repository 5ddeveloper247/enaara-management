<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class FirstPasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) ($user && $user->must_change_password);
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Enter your temporary password.',
            'current_password.current_password' => 'The temporary password is incorrect.',
            'password.required'         => 'Choose a new password.',
            'password.confirmed'        => 'Password confirmation does not match.',
        ];
    }
}
