<?php

namespace App\Http\Requests\Admin\BiometricDevice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BiometricDeviceRules
{
    public static function definition(FormRequest $request, ?int $ignoreSerialDeviceId = null): array
    {
        $safeText = ['regex:/^[^<>]*$/u', 'not_regex:/<\s*script/i'];

        $serialUnique = $ignoreSerialDeviceId !== null
            ? Rule::unique('biometric_devices', 'serial_number')->ignore($ignoreSerialDeviceId)
            : Rule::unique('biometric_devices', 'serial_number');

        return [
            'organization_id' => [
                'required',
                'integer',
                Rule::exists('organizations', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'sbu_id' => [
                'required',
                'integer',
                Rule::exists('sbus', 'id')->where(function ($q) use ($request) {
                    $orgId = (int) $request->input('organization_id');
                    $q->where('organization_id', $orgId)->where('is_active', true);
                }),
            ],
            'sbu_floor_id' => [
                'required',
                'integer',
                Rule::exists('sbu_floors', 'id')->where(function ($q) use ($request) {
                    $sbuId = (int) $request->input('sbu_id');
                    $q->where('sbu_id', $sbuId)->where('is_active', true);
                }),
            ],
            'device_name' => [
                'required',
                'string',
                'max:255',
                'regex:/[a-zA-Z]/',
                ...$safeText,
            ],
            'serial_number' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9\-_]+$/',
                $serialUnique,
            ],
            'device_type' => [
                'required',
                'string',
                'max:100',
                'regex:/[a-zA-Z]/',
                ...$safeText,
            ],
            'brand_model' => [
                'required',
                'string',
                'max:255',
                'regex:/[a-zA-Z]/',
                ...$safeText,
            ],
            'ip_address' => ['required', 'ip:ipv4'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'connection_type' => ['required', Rule::in(['lan', 'wifi'])],
            'device_status' => ['required', Rule::in(['active', 'inactive', 'faulty'])],
            'installation_date' => ['required', 'date'],
        ];
    }

    public static function messages(): array
    {
        return [
            'organization_id.required' => 'Organisation is required.',
            'organization_id.exists' => 'The selected organisation is invalid or inactive.',
            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'The selected SBU must belong to the organisation and be active.',
            'sbu_floor_id.required' => 'Floor is required.',
            'sbu_floor_id.exists' => 'The selected floor must belong to the SBU and be active.',
            'device_name.required' => 'Device name is required.',
            'device_name.regex' => 'Device name must contain letters and cannot be only digits.',
            'device_type.required' => 'Device type is required.',
            'device_type.regex' => 'Device type must contain letters and cannot be only digits.',
            'brand_model.required' => 'Brand or model is required.',
            'brand_model.regex' => 'Brand or model must contain letters and cannot be only digits.',
            'serial_number.required' => 'Serial number is required.',
            'serial_number.regex' => 'Serial number may only contain letters, numbers, hyphens, and underscores.',
            'serial_number.unique' => 'This serial number is already registered.',
            'ip_address.required' => 'IP address is required.',
            'ip_address.ip' => 'Enter a valid IPv4 address.',
            'port.required' => 'Port is required.',
            'port.between' => 'Port must be between 1 and 65535.',
            'connection_type.required' => 'Connection type is required.',
            'connection_type.in' => 'Connection type must be LAN or WiFi.',
            'device_status.required' => 'Device status is required.',
            'device_status.in' => 'Device status must be Active, Inactive, or Faulty.',
            'installation_date.required' => 'Installation date is required.',
            'installation_date.date' => 'Installation date must be a valid date.',
        ];
    }
}
