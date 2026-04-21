<?php

namespace App\Http\Requests\Admin\BiometricDevice;

trait PreparesBiometricDeviceInput
{
    protected function prepareBiometricDeviceStrings(): void
    {
        $trim = function (?string $v): ?string {
            if ($v === null) {
                return null;
            }
            $t = preg_replace('/\s+/', ' ', trim($v));

            return $t === '' ? null : $t;
        };

        $merge = [];
        foreach (['device_name', 'serial_number', 'device_type', 'brand_model', 'ip_address'] as $k) {
            if ($this->has($k)) {
                $merge[$k] = $trim((string) $this->input($k));
            }
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
