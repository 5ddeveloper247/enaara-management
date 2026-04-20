<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ThirdParty\ThirdPartyStoreRequest;
use App\Http\Requests\Admin\ThirdParty\ThirdPartyUpdateRequest;
use App\Models\Organization;
use App\Models\Sbu;
use App\Services\ThirdPartyService;
use Illuminate\View\View;

class ThirdPartyController extends Controller
{
    public function __construct(
        private ThirdPartyService $thirdPartyService
    ) {}

    public function index(): View
    {
        $thirdParties = $this->thirdPartyService->getList();
        $counts       = $this->thirdPartyService->getCounts();
        $organizations = Organization::orderBy('name')->get();
        $sbus = Sbu::query()
            ->select(['id', 'name', 'organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.third-party.index', [
            'thirdParties'     => $thirdParties,
            'organizations'   => $organizations,
            'sbus'            => $sbus,
            'totalThirdParties' => $counts['total'],
            'activeThirdParties' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create()
    {
        if (! validatePermissions('admin/third-party/add')) {
            abort(403, 'Unauthorized action.');
        }

        $thirdParties = $this->thirdPartyService->getList();
        $counts         = $this->thirdPartyService->getCounts();
        $organizations = Organization::orderBy('name')->get();
        $sbus = Sbu::query()
            ->select(['id', 'name', 'organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.third-party.index', [
            'thirdParties'       => $thirdParties,
            'organizations'     => $organizations,
            'sbus'              => $sbus,
            'totalThirdParties'   => $counts['total'],
            'activeThirdParties'  => $counts['active'],
            'activePercentage'   => $counts['active_percentage'],
        ]);
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
            $this->thirdPartyService->store($request->validated());

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
                    'message' => 'Failed to create third party: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create third party: ' . $e->getMessage());
        }
    }

    public function show(int $id): View
    {
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
                'data'    => [
                    'id'               => $thirdParty->id,
                    'organization_id'  => $thirdParty->organization_id,
                    'organization_ids' => $thirdParty->organizations->pluck('id')->whenEmpty(
                        fn ($collection) => collect($thirdParty->organization_id ? [$thirdParty->organization_id] : [])
                    )->values(),
                    'sbu_ids'          => $thirdParty->sbus->pluck('id')->values(),
                    'third_party_name' => $thirdParty->third_party_name,
                    'city'             => $thirdParty->city,
                    'address'          => $thirdParty->address,
                    'latitude'         => $thirdParty->latitude,
                    'longitude'        => $thirdParty->longitude,
                    'is_active'        => $thirdParty->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch third party: ' . $e->getMessage(),
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
            $this->thirdPartyService->update($id, $request->validated());

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
                    'message' => 'Failed to update third party: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update third party: ' . $e->getMessage());
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
                    'message' => 'Failed to delete third party: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete third party: ' . $e->getMessage());
        }
    }
}
