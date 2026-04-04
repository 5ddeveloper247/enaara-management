<?php

namespace App\Http\Requests\Admin\ThirdParty;

use Illuminate\Foundation\Http\FormRequest;

class ThirdPartyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id'   => ['required', 'integer', 'exists:organizations,id'],
            'name'              => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'third_party_name'  => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'city'              => ['nullable', 'string', 'max:255'],
            'address'           => ['nullable', 'string'],
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'is_active'         => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists'   => 'Selected organization is invalid.',
            'name.required'            => 'SBU name is required.',
            'name.regex'               => 'SBU name must contain at least one letter.',
            'third_party_name.required'=> 'Third party name is required.',
            'third_party_name.regex'   => 'Third party name must contain at least one letter.',
            'latitude.between'         => 'Latitude must be between -90 and 90.',
            'longitude.between'        => 'Longitude must be between -180 and 180.',
            'is_active.required'       => 'Status is required.',
        ];
    }
}
