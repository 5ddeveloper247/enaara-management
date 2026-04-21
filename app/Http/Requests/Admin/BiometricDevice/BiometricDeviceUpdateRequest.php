<?php

namespace App\Http\Requests\Admin\BiometricDevice;

use Illuminate\Foundation\Http\FormRequest;

class BiometricDeviceUpdateRequest extends FormRequest
{
    use PreparesBiometricDeviceInput;

    protected function prepareForValidation(): void
    {
        $this->prepareBiometricDeviceStrings();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return BiometricDeviceRules::definition($this, (int) $this->route('id'));
    }

    public function messages(): array
    {
        return BiometricDeviceRules::messages();
    }
}
