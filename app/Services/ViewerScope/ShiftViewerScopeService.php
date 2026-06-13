<?php

namespace App\Services\ViewerScope;

use App\Models\Sbu;
use App\Models\ShiftPlanner;
use App\Models\ShiftType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ShiftViewerScopeService
{
    public function __construct(
        private readonly ViewerScopeContext $context,
    ) {}

    public function applyPlannerQueryScope(Builder $query, ?User $user = null): Builder
    {
        return $this->applySbuColumnScope($query, 'sbu_id', $user);
    }

    public function applyShiftTypeQueryScope(Builder $query, ?User $user = null): Builder
    {
        if ($this->context->isUnrestricted($user)) {
            return $query;
        }

        $sbuId = $this->context->resolveViewerSbuId($user);
        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'department',
            fn (Builder $departmentQuery) => $departmentQuery->where('sbu_id', $sbuId)
        );
    }

    public function plannerBelongsToViewerScope(ShiftPlanner $shiftPlanner, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        return (int) ($shiftPlanner->sbu_id ?? 0) === $viewerSbuId;
    }

    public function assertPlannerIdAccessible(int $shiftPlannerId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'shift' => 'You are not authorized to access shifts.',
            ]);
        }

        $shiftPlanner = ShiftPlanner::query()->select(['id', 'sbu_id'])->find($shiftPlannerId);
        if ($shiftPlanner === null || ! $this->plannerBelongsToViewerScope($shiftPlanner, $user)) {
            throw ValidationException::withMessages([
                'shift' => 'This shift is outside your SBU scope.',
            ]);
        }
    }

    public function assertShiftTypeIdAccessible(int $shiftTypeId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'shift_type' => 'You are not authorized to access shift types.',
            ]);
        }

        $shiftType = ShiftType::query()
            ->select(['id', 'department_id'])
            ->with('department:id,sbu_id')
            ->find($shiftTypeId);

        if ($shiftType === null
            || $shiftType->department === null
            || (int) ($shiftType->department->sbu_id ?? 0) !== $viewerSbuId) {
            throw ValidationException::withMessages([
                'shift_type' => 'This shift type is outside your SBU scope.',
            ]);
        }
    }

    /**
     * @return array{organization_id: ?int, sbu_id: ?int}
     */
    public function resolvePlannerOwnership(array $data, ?User $user = null): array
    {
        if ($this->context->isUnrestricted($user)) {
            $orgId = (int) ($data['organization_id'] ?? 0);
            $sbuId = (int) ($data['sbu_id'] ?? 0);

            if ($orgId <= 0) {
                throw ValidationException::withMessages([
                    'organization_id' => 'Organization is required.',
                ]);
            }

            if ($sbuId <= 0) {
                throw ValidationException::withMessages([
                    'sbu_id' => 'SBU is required.',
                ]);
            }

            $belongs = Sbu::query()
                ->whereKey($sbuId)
                ->where('organization_id', $orgId)
                ->exists();

            if (! $belongs) {
                throw ValidationException::withMessages([
                    'sbu_id' => 'The selected SBU does not belong to the selected organization.',
                ]);
            }

            return [
                'organization_id' => $orgId,
                'sbu_id' => $sbuId,
            ];
        }

        $ownership = $this->defaultPlannerOwnership($user);
        if (empty($ownership['sbu_id'])) {
            throw ValidationException::withMessages([
                'sbu_id' => 'Your account is not linked to an SBU. You cannot manage shifts.',
            ]);
        }

        return $ownership;
    }

    /**
     * @return array{organization_id: ?int, sbu_id: ?int}
     */
    public function defaultPlannerOwnership(?User $user = null): array
    {
        $scope = $this->context->frontendScopePayload($user);

        if (! $scope['restricted'] || empty($scope['sbu_id'])) {
            return [
                'organization_id' => null,
                'sbu_id' => null,
            ];
        }

        return [
            'organization_id' => $scope['organization_id'],
            'sbu_id' => $scope['sbu_id'],
        ];
    }

    private function applySbuColumnScope(Builder $query, string $column, ?User $user = null): Builder
    {
        $sbuId = $this->context->resolveViewerSbuId($user);

        if ($sbuId === null) {
            return $query;
        }

        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $sbuId);
    }
}
