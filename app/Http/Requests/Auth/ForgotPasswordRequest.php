<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
        ];
    }
}

