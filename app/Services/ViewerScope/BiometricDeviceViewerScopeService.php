<?php

namespace App\Services\ViewerScope;

use App\Models\BiometricDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class BiometricDeviceViewerScopeService
{
    public function __construct(
        private readonly ViewerScopeContext $context,
    ) {}

    public function applyQueryScope(Builder $query, ?User $user = null): Builder
    {
        $sbuId = $this->context->resolveViewerSbuId($user);

        if ($sbuId === null) {
            return $query;
        }

        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('sbu_id', $sbuId);
    }

    public function belongsToViewerScope(BiometricDevice $device, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        return (int) ($device->sbu_id ?? 0) === $viewerSbuId;
    }

    public function assertIdAccessible(int $deviceId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'biometric_device' => 'You are not authorized to access biometric devices.',
            ]);
        }

        $device = BiometricDevice::query()->select(['id', 'sbu_id'])->find($deviceId);
        if ($device === null || ! $this->belongsToViewerScope($device, $user)) {
            throw ValidationException::withMessages([
                'biometric_device' => 'This biometric device is outside your SBU scope.',
            ]);
        }
    }
}
