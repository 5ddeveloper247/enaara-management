<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ThirdParty\ThirdPartyStoreRequest;
use App\Http\Requests\Admin\ThirdParty\ThirdPartyUpdateRequest;
use App\Services\EmployeeViewerScopeService;
use App\Services\ThirdPartyService;
use Illuminate\View\View;

class ThirdPartyController extends Controller
{
    public function __construct(
        private ThirdPartyService $thirdPartyService,
        private EmployeeViewerScopeService $viewerScope,
    ) {}

    public function index(): View
    {
        if (! validatePermissions('admin/third-party')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.third-party.index', $this->indexViewData());
    }

    public function create()
    {
        if (! validatePermissions('admin/third-party/add')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.third-party.index', $this->indexViewData());
    }

    public function store(ThirdPartyStoreRequest $request)
    {
        if (! validatePermissions('admin/third-party/add')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->thirdPartyService->store(
                $request->validated(),
                $request->file('company_registration_document'),
                $request->file('contract_copy')
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Third party created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.third-party.index')
                ->with('success', 'Third party created successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create third party: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create third party: '.$e->getMessage());
        }
    }

    public function show(int $id): View
    {
        if (! validatePermissions('admin/third-party')) {
            abort(403, 'Unauthorized action.');
        }

        $thirdParty = $this->thirdPartyService->findById($id);

        if (! $thirdParty) {
            abort(404);
        }

        return view('admin.third-party.show', ['thirdParty' => $thirdParty]);
    }

    public function edit($id)
    {
        if (! validatePermissions('admin/third-party/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $thirdParty = $this->thirdPartyService->findById($id);

            if (! $thirdParty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third party not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $thirdParty->id,
                    'organization_id' => $thirdParty->organization_id,
                    'organization_ids' => $thirdParty->organizations->pluck('id')->whenEmpty(
                        fn ($collection) => collect($thirdParty->organization_id ? [$thirdParty->organization_id] : [])
                    )->values(),
                    'sbu_ids' => $thirdParty->sbus->pluck('id')->values(),
                    'third_party_name' => $thirdParty->third_party_name,
                    'vendor_id' => $thirdParty->vendor_id,
                    'service_type' => $thirdParty->service_type,
                    'specify_service_type' => $thirdParty->specify_service_type,
                    'is_individual_contractor' => $thirdParty->is_individual_contractor ? 1 : 0,
                    'ntn' => $thirdParty->ntn,
                    'contractor_cnic' => $thirdParty->contractor_cnic,
                    'contact_person_name' => $thirdParty->contact_person_name,
                    'mobile_number' => $thirdParty->mobile_number,
                    'email' => $thirdParty->email,
                    'supervisor_name' => $thirdParty->supervisor_name,
                    'supervisor_cnic' => $thirdParty->supervisor_cnic,
                    'supervisor_mobile_number' => $thirdParty->supervisor_mobile_number,
                    'contract_start_date' => optional($thirdParty->contract_start_date)->format('Y-m-d'),
                    'contract_end_date' => optional($thirdParty->contract_end_date)->format('Y-m-d'),
                    'scope_of_work' => $thirdParty->scope_of_work,
                    'estimated_staff_count' => $thirdParty->estimated_staff_count,
                    'company_registration_document_url' => $thirdParty->company_registration_document_path ? asset('storage/'.$thirdParty->company_registration_document_path) : null,
                    'contract_copy_url' => $thirdParty->contract_copy_path ? asset('storage/'.$thirdParty->contract_copy_path) : null,
                    'remarks' => $thirdParty->remarks,
                    'city' => $thirdParty->city,
                    'address' => $thirdParty->address,
                    'latitude' => $thirdParty->latitude,
                    'longitude' => $thirdParty->longitude,
                    'is_active' => $thirdParty->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch third party: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(ThirdPartyUpdateRequest $request, $id)
    {
        if (! validatePermissions('admin/third-party/edit')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->thirdPartyService->update(
                $id,
                $request->validated(),
                $request->file('company_registration_document'),
                $request->file('contract_copy')
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Third party updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.third-party.index')
                ->with('success', 'Third party updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update third party: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update third party: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (! validatePermissions('admin/third-party/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->thirdPartyService->destroy($id);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Third party deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.third-party.index')
                ->with('success', 'Third party deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete third party: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete third party: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function indexViewData(): array
    {
        $counts = $this->thirdPartyService->getCounts();

        return [
            'thirdParties' => $this->thirdPartyService->getList(),
            'organizations' => $this->thirdPartyService->getOrganizationsForFilter(),
            'sbus' => $this->thirdPartyService->getSbusForFilter(),
            'totalThirdParties' => $counts['total'],
            'activeThirdParties' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
            'viewerEmployeeScope' => $this->viewerScope->frontendScopePayload(),
        ];
    }
}
