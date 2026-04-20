<?php

namespace App\Http\Requests\Admin\ModuleCategory;

use App\Models\ModuleCategory;
use Illuminate\Foundation\Http\FormRequest;

class ModuleCategoryStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_name' => $this->filled('category_name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('category_name'))) : $this->input('category_name'),
            'css_class' => $this->filled('css_class') ? trim((string) $this->input('css_class')) : $this->input('css_class'),
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
            'category_name' => [
                'required',
                'string',
                'max:155',
                'not_regex:/<[^>]*>/',
                'unique:module_categories,category_name',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $normalizedInput = $this->normalizeName((string) $value);
                    $existing = ModuleCategory::query()->pluck('category_name');
                    foreach ($existing as $categoryName) {
                        if ($this->normalizeName((string) $categoryName) === $normalizedInput) {
                            $fail('This module category name is already in use.');
                            return;
                        }
                    }
                },
            ],
            'css_class' => ['nullable', 'string', 'max:100', 'not_regex:/<[^>]*>/'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Category name is required.',
            'category_name.unique' => 'This module category name is already in use.',
            'category_name.not_regex' => 'Category name must not contain HTML or script tags.',
            'css_class.not_regex' => 'CSS class must not contain HTML or script tags.',
        ];
    }
}
