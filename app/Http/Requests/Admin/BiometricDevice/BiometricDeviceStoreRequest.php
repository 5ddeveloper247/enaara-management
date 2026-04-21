<?php

namespace App\Http\Requests\Admin\BiometricDevice;

use Illuminate\Foundation\Http\FormRequest;

class BiometricDeviceStoreRequest extends FormRequest
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
        return BiometricDeviceRules::definition($this, null);
    }

    public function messages(): array
    {
        return BiometricDeviceRules::messages();
    }
}
