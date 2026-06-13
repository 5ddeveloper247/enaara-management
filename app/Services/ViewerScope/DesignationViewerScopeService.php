<?php

namespace App\Services\ViewerScope;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class DesignationViewerScopeService
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

    public function belongsToViewerScope(Designation $designation, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        return (int) ($designation->sbu_id ?? 0) === $viewerSbuId;
    }

    public function assertIdAccessible(int $designationId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'designation' => 'You are not authorized to access designations.',
            ]);
        }

        $designation = Designation::query()->select(['id', 'sbu_id'])->find($designationId);
        if ($designation === null || ! $this->belongsToViewerScope($designation, $user)) {
            throw ValidationException::withMessages([
                'designation' => 'This designation is outside your SBU scope.',
            ]);
        }
    }
}
