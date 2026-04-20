<?php

namespace App\Http\Controllers;

use App\Services\SbuService;
use Illuminate\View\View;
use App\Models\Organization;
use App\Http\Requests\Admin\SBU\SbuStoreRequest;
use App\Http\Requests\Admin\SBU\SbuUpdateRequest;
use Illuminate\Support\Facades\Log;

class SbuController extends Controller
{
    public function __construct(
        private SbuService $sbuService
    ) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        $sbus = $this->sbuService->getList();
        $counts = $this->sbuService->getCounts();
        $organizations = Organization::orderBy('name')->get();

        if (request()->expectsJson() || request()->ajax()) {
            $organizationId = request()->get('organization_id');
            if ($organizationId) {
                $filteredSbus = $sbus->filter(function($sbu) use ($organizationId) {
                    return $sbu->organization_id == $organizationId;
                })->map(function($sbu) {
                    return [
                        'id' => $sbu->id,
                        'name' => $sbu->name,
                        'organization_id' => $sbu->organization_id,
                    ];
                })->values();
                return response()->json(['sbus' => $filteredSbus]);
            }
            return response()->json(['sbus' => $sbus]);
        }

        return view('admin.sbu.index', [
            'sbus' => $sbus,
            'organizations' => $organizations,
            'totalSbus' => $counts['total'],
            'activeSbus' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create()
    {
        if (!validatePermissions('admin/sbu/add')) {
            abort(403, 'Unauthorized action.');
        }

        $sbus = $this->sbuService->getList();
        $counts = $this->sbuService->getCounts();
        $organizations = Organization::orderBy('name')->get();

        return view('admin.sbu.index', [
            'sbus' => $sbus,
            'organizations' => $organizations,
            'totalSbus' => $counts['total'],
            'activeSbus' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function store(SbuStoreRequest $request)
    {
        if (!validatePermissions('admin/sbu/add')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->sbuService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SBU created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sbu.index')
                ->with('success', 'SBU created successfully.');
        } catch (\Exception $e) {
            Log::error('SBU create failed', [
                'exception' => $e->getMessage(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create SBU.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create SBU.');
        }
    }

    public function show(int $id): View
    {
        $sbu = $this->sbuService->findById($id);

        if (!$sbu) {
            abort(404);
        }

        return view('admin.sbu.show', ['sbu' => $sbu]);
    }

    public function edit($id)
    {
        if (!validatePermissions('admin/sbu/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $sbu = $this->sbuService->findById($id);

            if (!$sbu) {
                return response()->json([
                    'success' => false,
                    'message' => 'SBU not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $sbu->id,
                    'organization_id' => $sbu->organization_id,
                    'name' => $sbu->name,
                    'city' => $sbu->city,
                    'address' => $sbu->address,
                    'latitude' => $sbu->latitude,
                    'longitude' => $sbu->longitude,
                    'working_days' => $sbu->working_days,
                    'working_start_time' => $sbu->working_start_time,
                    'working_end_time' => $sbu->working_end_time,
                    'opening_grace_period' => $sbu->opening_grace_period,
                    'closing_grace_period' => $sbu->closing_grace_period,
                    'is_active' => $sbu->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('SBU edit fetch failed', [
                'sbu_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch SBU.',
            ], 500);
        }
    }

    public function update(SbuUpdateRequest $request, $id)
    {
        if (!validatePermissions('admin/sbu/edit')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->sbuService->update($id, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SBU updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sbu.index')
                ->with('success', 'SBU updated successfully.');
        } catch (\Exception $e) {
            Log::error('SBU update failed', [
                'sbu_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update SBU.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update SBU.');
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/sbu/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->sbuService->destroy($id);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SBU deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sbu.index')
                ->with('success', 'SBU deleted successfully.');
        } catch (\Exception $e) {
            Log::error('SBU delete failed', [
                'sbu_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete SBU.',
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete SBU.');
        }
    }
}
