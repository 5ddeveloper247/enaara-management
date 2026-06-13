<?php

namespace App\Services\ViewerScope;

use App\Models\Organization;
use App\Models\Sbu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ViewerScopeContext
{
    /**
     * null = unrestricted (system admin), 0 = blocked, positive int = viewer SBU.
     */
    public function resolveViewerSbuId(?User $user = null): ?int
    {
        $user = $user ?? Auth::user();

        if ($user === null) {
            return 0;
        }

        if ($user->isSystemAdminUser()) {
            return null;
        }

        $viewerEmployee = $user->employee;
        if ($viewerEmployee === null || ! $viewerEmployee->sbu_id) {
            return 0;
        }

        return (int) $viewerEmployee->sbu_id;
    }

    public function resolveViewerOrganizationId(?User $user = null): ?int
    {
        $user = $user ?? Auth::user();

        if ($user === null) {
            return null;
        }

        if ($user->isSystemAdminUser()) {
            return null;
        }

        $organizationId = $user->employee?->organization_id;

        return $organizationId ? (int) $organizationId : null;
    }

    public function isUnrestricted(?User $user = null): bool
    {
        return $this->resolveViewerSbuId($user) === null;
    }

    public function canManageEmployees(?User $user = null): bool
    {
        $sbuId = $this->resolveViewerSbuId($user);

        return $sbuId === null || $sbuId > 0;
    }

    /**
     * @return array{
     *     restricted: bool,
     *     organization_id: ?int,
     *     sbu_id: ?int,
     *     organization_name: ?string,
     *     sbu_name: ?string
     * }
     */
    public function frontendScopePayload(?User $user = null): array
    {
        if ($this->isUnrestricted($user)) {
            return [
                'restricted' => false,
                'organization_id' => null,
                'sbu_id' => null,
                'organization_name' => null,
                'sbu_name' => null,
            ];
        }

        $sbuId = $this->resolveViewerSbuId($user);
        if ($sbuId <= 0) {
            return [
                'restricted' => true,
                'organization_id' => null,
                'sbu_id' => null,
                'organization_name' => null,
                'sbu_name' => null,
            ];
        }

        $sbu = Sbu::query()->select(['id', 'name', 'organization_id'])->find($sbuId);
        $organizationId = $sbu?->organization_id ? (int) $sbu->organization_id : $this->resolveViewerOrganizationId($user);
        $organizationName = $organizationId
            ? Organization::query()->whereKey($organizationId)->value('name')
            : null;

        return [
            'restricted' => true,
            'organization_id' => $organizationId,
            'sbu_id' => $sbuId,
            'organization_name' => $organizationName,
            'sbu_name' => $sbu?->name,
        ];
    }
}
