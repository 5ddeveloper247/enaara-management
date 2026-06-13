<?php

namespace App\Services\ViewerScope;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DepartmentViewerScopeService
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

    public function belongsToViewerScope(Department $department, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        return (int) ($department->sbu_id ?? 0) === $viewerSbuId;
    }
}
