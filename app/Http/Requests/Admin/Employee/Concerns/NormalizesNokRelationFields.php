<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use Illuminate\Validation\Rule;

trait NormalizesNokRelationFields
{
    protected function normalizeNokRelationFromRequest(): void
    {
        if (! $this->filled('nok_relation_type')) {
            return;
        }
        $type = (string) $this->input('nok_relation_type');
        if ($type === 'Other') {
            $this->merge([
                'nok_relation' => trim((string) $this->input('nok_relation_other', '')),
            ]);
        } else {
            $this->merge(['nok_relation' => $type]);
        }
    }

    public static function nokRelationDropdownOptions(): array
    {
        return ['Father', 'Mother', 'Husband', 'Wife', 'Son', 'Daughter', 'Brother', 'Sister', 'Other'];
    }

    protected function nokRelationValidationRules(): array
    {
        return [
            'nok_relation_type' => ['required', Rule::in(self::nokRelationDropdownOptions())],
            'nok_relation_other' => [
                'nullable',
                'required_if:nok_relation_type,Other',
                'string',
                'min:2',
                'max:100',
                'regex:' . $this->localeAlphaLabelRegex(),
            ],
            'nok_relation' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $fixed = array_slice(self::nokRelationDropdownOptions(), 0, -1);
                    if (in_array($value, $fixed, true)) {
                        return;
                    }
                    if (! is_string($value) || ! preg_match($this->localeAlphaLabelRegex(), $value)) {
                        $fail('The relation with NOK must be valid text (letters and standard punctuation).');
                    }
                },
            ],
        ];
    }
}
