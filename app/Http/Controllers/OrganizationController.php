<?php

namespace App\Http\Controllers;

use App\Services\OrganizationService;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService
    ) {}

    public function index(): View
    {
        $organizations = $this->organizationService->getOrganizationsList();
        $counts = $this->organizationService->getOrganizationsCounts();

        return view('admin.organization.index', [
            'organizations' => $organizations,
            'totalOrganizations' => $counts['total'],
            'activeOrganizations' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }
}
