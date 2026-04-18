<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Workflow;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    // ─────────────────────────────────────────────
    //  INDEX (view)
    // ─────────────────────────────────────────────
    public function index(): View
    {
        $organizations = Organization::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $workflowScopeTree = Organization::query()
            ->where('is_active', true)
            ->with([
                'sbus' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Organization $org) => [
                'id' => $org->id,
                'name' => $org->name,
                'sbus' => $org->sbus->map(fn ($sbu) => [
                    'id' => $sbu->id,
                    'name' => $sbu->name,
                ]),
            ]);

        $roleNames = Role::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        return view('admin.workflows.index', compact('organizations', 'workflowScopeTree', 'roleNames'));
    }

    // ─────────────────────────────────────────────
    //  TABLE DATA (AJAX)
    // ─────────────────────────────────────────────
    public function getTableData(): array
    {
        $workflows = Workflow::query()
            ->with([
                'organization:id,name',
                'sbu:id,name,organization_id',
            ])
            ->orderByDesc('id')
            ->get();

        return $workflows->map(function (Workflow $wf) {
            $levels = $wf->approval_levels ?? [];
            $orgName = $wf->organization ? $wf->organization->name : 'Global';
            $isGlobal = $wf->organization_id === null;
            $sbuName = $wf->sbu?->name;

            return [
                'id' => $wf->id,
                'name' => $wf->name,
                'request_type' => $wf->request_type,
                'status' => $wf->status,
                'organization_id' => $wf->organization_id,
                'organization' => $orgName,
                'is_global' => $isGlobal,
                'sbu_id' => $wf->sbu_id,
                'sbu_name' => $sbuName,
                'branch' => $wf->branch,
                'approval_levels' => $levels,
                'levels_count' => count($levels),
                'levels_display' => implode(' → ', array_map(fn ($l) => 'L'.$l['level'].': '.$l['role'], $levels)),
                'sla_hours' => $wf->sla_hours,
                'escalate_to' => $wf->escalate_to,
                'created_at' => $wf->created_at?->format('d M Y'),
            ];
        })->values()->all();
    }

    // ─────────────────────────────────────────────
    //  STATS (AJAX)
    // ─────────────────────────────────────────────
    public function getStats(): array
    {
        $all = Workflow::withoutTrashed();
        $total = (clone $all)->count();
        $active = (clone $all)->where('status', 'active')->count();
        $types = (clone $all)->distinct()->count('request_type');
        $avgSla = (clone $all)->avg('sla_hours') ?? 0;

        return [
            'total' => $total,
            'active' => $active,
            'request_types' => $types,
            'avg_approval_time' => round($avgSla),
        ];
    }

    // ─────────────────────────────────────────────
    //  STORE
    // ─────────────────────────────────────────────
    public function store(array $data): Workflow
    {
        return DB::transaction(function () use ($data) {
            $levels = collect($data['approval_levels'])->values()->map(function ($item, $idx) {
                return ['level' => $idx + 1, 'role' => $item['role']];
            })->all();

            $workflow = Workflow::create([
                'name' => $data['name'],
                'request_type' => $data['request_type'],
                'status' => $data['status'],
                'organization_id' => $data['organization_id'] ?: null,
                'sbu_id' => $data['sbu_id'] ?: null,
                'branch' => null,
                'approval_levels' => $levels,
                'sla_hours' => $data['sla_hours'],
                'escalate_to' => $data['escalate_to'] ?: null,
            ]);

            Log::info('Workflow created', ['id' => $workflow->id, 'name' => $workflow->name]);

            return $workflow;
        });
    }

    // ─────────────────────────────────────────────
    //  UPDATE
    // ─────────────────────────────────────────────
    public function update(int $id, array $data): Workflow
    {
        return DB::transaction(function () use ($id, $data) {
            $workflow = Workflow::findOrFail($id);

            $levels = collect($data['approval_levels'])->values()->map(function ($item, $idx) {
                return ['level' => $idx + 1, 'role' => $item['role']];
            })->all();

            $workflow->update([
                'name' => $data['name'],
                'request_type' => $data['request_type'],
                'status' => $data['status'],
                'organization_id' => $data['organization_id'] ?: null,
                'sbu_id' => $data['sbu_id'] ?: null,
                'branch' => null,
                'approval_levels' => $levels,
                'sla_hours' => $data['sla_hours'],
                'escalate_to' => $data['escalate_to'] ?: null,
            ]);

            Log::info('Workflow updated', ['id' => $workflow->id]);

            return $workflow->fresh();
        });
    }

    // ─────────────────────────────────────────────
    //  UPDATE STATUS (toggle)
    // ─────────────────────────────────────────────
    public function updateStatus(int $id, string $status): Workflow
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->update(['status' => $status]);
        Log::info('Workflow status updated', ['id' => $id, 'status' => $status]);

        return $workflow;
    }

    // ─────────────────────────────────────────────
    //  DESTROY
    // ─────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();
        Log::info('Workflow deleted', ['id' => $id]);
    }

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────
    public function getOrganizationsForDropdown(): array
    {
        return Organization::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($o) => ['id' => $o->id, 'name' => $o->name])
            ->all();
    }
}
