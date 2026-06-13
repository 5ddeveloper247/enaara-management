<?php

namespace App\Services\ViewerScope;

use App\Models\SbuFloor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class SbuFloorViewerScopeService
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

    public function belongsToViewerScope(SbuFloor $sbuFloor, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        return (int) ($sbuFloor->sbu_id ?? 0) === $viewerSbuId;
    }

    public function assertIdAccessible(int $sbuFloorId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'sbu_floor' => 'You are not authorized to access SBU floors.',
            ]);
        }

        $sbuFloor = SbuFloor::query()->select(['id', 'sbu_id'])->find($sbuFloorId);
        if ($sbuFloor === null || ! $this->belongsToViewerScope($sbuFloor, $user)) {
            throw ValidationException::withMessages([
                'sbu_floor' => 'This SBU floor is outside your SBU scope.',
            ]);
        }
    }
}
