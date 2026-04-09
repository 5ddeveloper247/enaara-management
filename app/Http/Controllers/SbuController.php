<?php

namespace App\Http\Controllers;

use App\Services\SbuService;
use Illuminate\View\View;
use App\Models\Organization;
use App\Http\Requests\Admin\SBU\SbuStoreRequest;
use App\Http\Requests\Admin\SBU\SbuUpdateRequest;

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

    public function create(SbuStoreRequest $request)
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create SBU: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create SBU: ' . $e->getMessage());
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
                    'is_active' => $sbu->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch SBU: ' . $e->getMessage(),
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update SBU: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update SBU: ' . $e->getMessage());
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
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete SBU: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete SBU: ' . $e->getMessage());
        }
    }
}