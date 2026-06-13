<?php

namespace App\Services\ViewerScope;

use App\Models\Sbu;
use App\Models\ThirdParty;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ThirdPartyViewerScopeService
{
    public function __construct(
        private readonly ViewerScopeContext $context,
    ) {}

    public function applyQueryScope(Builder $query, ?User $user = null): Builder
    {
        if ($this->context->isUnrestricted($user)) {
            return $query;
        }

        $sbuId = $this->context->resolveViewerSbuId($user);
        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'sbus',
            fn (Builder $sbuQuery) => $sbuQuery->where('sbus.id', $sbuId)
        );
    }

    public function belongsToViewerScope(ThirdParty $thirdParty, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        if (! $thirdParty->relationLoaded('sbus')) {
            $thirdParty->load('sbus:id');
        }

        return $thirdParty->sbus->contains(
            fn (Sbu $sbu) => (int) $sbu->id === $viewerSbuId
        );
    }

    public function assertIdAccessible(int $thirdPartyId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'third_party' => 'You are not authorized to access third parties.',
            ]);
        }

        $thirdParty = ThirdParty::query()
            ->select(['id'])
            ->with('sbus:id')
            ->find($thirdPartyId);

        if ($thirdParty === null || ! $this->belongsToViewerScope($thirdParty, $user)) {
            throw ValidationException::withMessages([
                'third_party' => 'This third party is outside your SBU scope.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertWriteDataAllowed(array $data, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $scope = $this->context->frontendScopePayload($user);
        if (empty($scope['sbu_id'])) {
            throw ValidationException::withMessages([
                'sbu_ids' => 'Your account is not linked to an SBU. You cannot manage third parties.',
            ]);
        }

        $organizationIds = array_values(array_unique(array_filter(array_map('intval', $data['organization_ids'] ?? []))));
        foreach ($organizationIds as $organizationId) {
            if ((int) $organizationId !== (int) $scope['organization_id']) {
                throw ValidationException::withMessages([
                    'organization_ids' => 'You can only manage third parties for your organization.',
                ]);
            }
        }

        $sbuIds = array_values(array_unique(array_filter(array_map('intval', $data['sbu_ids'] ?? []))));
        foreach ($sbuIds as $sbuId) {
            if ((int) $sbuId !== (int) $scope['sbu_id']) {
                throw ValidationException::withMessages([
                    'sbu_ids' => 'You can only manage third parties for your SBU.',
                ]);
            }
        }
    }
}
