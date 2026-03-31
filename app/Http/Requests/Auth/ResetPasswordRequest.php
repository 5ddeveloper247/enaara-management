<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                'exists:users,email',
                'not_regex:/<\s*script/i',
                'not_regex:/<\?php/i',
                'not_regex:/\?>/i',
            ],
            'token' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'not_regex:/<\s*script/i',
                'not_regex:/<\?php/i',
                'not_regex:/\?>/i',
            ],
            'password_confirmation' => [
                'required',
                'string',
                'min:8',
            ],
        ];
    }
}

