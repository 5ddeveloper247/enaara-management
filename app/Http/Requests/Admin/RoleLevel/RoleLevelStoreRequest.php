<?php

namespace App\Http\Requests\Admin\RoleLevel;

use App\Models\RoleLevel;
use Illuminate\Foundation\Http\FormRequest;

class RoleLevelStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('name'))) : $this->input('name'),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : $this->input('description'),
            'is_active' => $this->has('is_active')
                ? filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN)
                : $this->input('is_active'),
        ]);
    }

    protected function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:role_levels,name',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $normalizedInput = $this->normalizeName((string) $value);
                    $existingNames = RoleLevel::query()->pluck('name');
                    foreach ($existingNames as $existingName) {
                        if ($this->normalizeName((string) $existingName) === $normalizedInput) {
                            $fail('This role level name is already in use.');
                            return;
                        }
                    }
                },
            ],
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|digits_between:1,10|max:9999999999',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Role level name is required.',
            'name.unique' => 'This role level name is already in use.',
            'name.max' => 'Role level name must not exceed 50 characters.',
            'level.required' => 'Role level priority is required.',
            'level.integer' => 'Role level priority must be a number.',
            'level.min' => 'Role level priority must be at least 1.',
            'level.digits_between' => 'You can enter maximum 10 digits for role level priority.',
            'level.max' => 'You can enter maximum 10 digits for role level priority.',
            'description.max' => 'Description must not exceed 500 characters.',
        ];
    }
}
