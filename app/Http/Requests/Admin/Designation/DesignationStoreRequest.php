<?php

namespace App\Http\Requests\Admin\Designation;

use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignationStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('name'))) : $this->input('name'),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : $this->input('description'),
            'is_active' => $this->has('is_active')
                ? filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN)
                : $this->input('is_active'),
            'organization_id' => $this->filled('organization_id') ? (int) $this->input('organization_id') : $this->input('organization_id'),
            'sbu_id' => $this->filled('sbu_id') ? (int) $this->input('sbu_id') : $this->input('sbu_id'),
            'department_id' => $this->filled('department_id') ? (int) $this->input('department_id') : $this->input('department_id'),
        ]);
    }

    protected function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    protected function alphaTextRegex(): string
    {
        return "/^[A-Za-z]+[\sA-Za-z\.\-&,\/()']*$/";
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orgId = (int) $this->input('organization_id');
        $sbuId = (int) $this->input('sbu_id');
        $departmentId = (int) $this->input('department_id');

        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'sbu_id' => [
                'required',
                'integer',
                Rule::exists('sbus', 'id')->where(function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                }),
            ],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(function ($q) use ($orgId, $sbuId) {
                    $q->where('organization_id', $orgId)->where('sbu_id', $sbuId);
                }),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:' . $this->alphaTextRegex(),
                function (string $attribute, mixed $value, \Closure $fail) use ($departmentId) {
                    if ($departmentId <= 0) {
                        return;
                    }

                    $normalizedInput = $this->normalizeName((string) $value);
                    $existingNames = Designation::query()
                        ->where('department_id', $departmentId)
                        ->pluck('name');

                    foreach ($existingNames as $existingName) {
                        if ($this->normalizeName((string) $existingName) === $normalizedInput) {
                            $fail('This designation name is already in use for the selected department.');
                            return;
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',
            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU is invalid for this organization.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department is invalid for this SBU.',
            'name.required' => 'Designation name is required.',
            'name.min' => 'Designation name must be at least 2 characters.',
            'name.max' => 'Designation name must not exceed 100 characters.',
            'name.regex' => 'Designation may only contain letters, spaces, and punctuation (like dot or hyphen).',
            'description.max' => 'Description must not exceed 500 characters.',
        ];
    }
}
