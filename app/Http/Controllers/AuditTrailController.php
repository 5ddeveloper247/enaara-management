<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Display audit trail page
     */
    public function index()
    {
        $organizations = Organization::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.audit-trails.index', compact('organizations'));
    }

    /**
     * Return audit trail data for DataTable / AJAX
     */
    public function data(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 100);

        if ($perPage <= 0) {
            $perPage = 100;
        }

        $query = AuditTrail::with([
            'user.roles',
            'employee.organization',
            'employee.sbu',
            'changes',
        ])
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('action_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('action_at', '<=', $request->date_to);
            })
            ->when($request->filled('organization_id'), function ($q) use ($request) {
                $q->where('organization_id', $request->organization_id);
            })
            ->when($request->filled('action_category'), function ($q) use ($request) {
                $q->where('action_category', $request->action_category);
            })
            ->when($request->filled('severity'), function ($q) use ($request) {
                $q->where('severity', strtolower($request->severity));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);

                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('action_category', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('action_at');

        $records = $query->paginate($perPage);

        $data = $records->getCollection()->map(function ($audit) {
            $userName = $audit->user?->name ?? 'System';

            return [
                'id' => $audit->id,
                'timestamp' => optional($audit->action_at)->toDateTimeString(),
                'action' => $audit->action,
                'module' => $audit->module,
                'user' => [
                    'id' => $audit->user?->id,
                    'name' => $userName,
                    'role' => $audit->user?->roles?->first()?->name ?? 'N/A',
                    'avatar' => strtoupper(substr($userName, 0, 1)),
                ],
                'employee_id' => $audit->employee_id,
                'organization_id' => $audit->organization_id,
                'organization' => $audit->employee?->organization?->name ?? 'N/A',
                'sbu_id' => $audit->sbu_id,
                'branch' => $audit->employee?->sbu?->name ?? 'N/A',
                'department_id' => $audit->department_id,
                'category' => $audit->action_category,
                'description' => $audit->description,
                'severity' => strtolower($audit->severity ?? 'info'),
                'ipAddress' => $audit->ip_address,
                'device' => $audit->device,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'meta' => $audit->meta,
                'hasChanges' => $audit->changes->isNotEmpty(),
                'changes' => $audit->changes->map(function ($change) {
                    return [
                        'field' => $change->field,
                        'before' => $change->old_value,
                        'after' => $change->new_value,
                    ];
                })->values(),
                'context' => $this->formatContext($audit->context),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
            ],
        ]);
    }

    /**
     * Return single audit trail detail
     */
    public function show(AuditTrail $auditTrail): JsonResponse
    {
        $auditTrail->load([
            'user.roles',
            'employee.organization',
            'employee.sbu',
            'changes',
        ]);

        $userName = $auditTrail->user?->name ?? 'System';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $auditTrail->id,
                'timestamp' => optional($auditTrail->action_at)->toDateTimeString(),
                'action' => $auditTrail->action,
                'module' => $auditTrail->module,
                'category' => $auditTrail->action_category,
                'description' => $auditTrail->description,
                'severity' => strtolower($auditTrail->severity ?? 'info'),
                'user' => [
                    'id' => $auditTrail->user?->id,
                    'name' => $userName,
                    'role' => $auditTrail->user?->roles?->first()?->name ?? 'N/A',
                    'avatar' => strtoupper(substr($userName, 0, 1)),
                ],
                'employee_id' => $auditTrail->employee_id,
                'organization_id' => $auditTrail->organization_id,
                'organization' => $auditTrail->employee?->organization?->name ?? 'N/A',
                'sbu_id' => $auditTrail->sbu_id,
                'branch' => $auditTrail->employee?->sbu?->name ?? 'N/A',
                'department_id' => $auditTrail->department_id,
                'ipAddress' => $auditTrail->ip_address,
                'user_agent' => $auditTrail->user_agent,
                'device' => $auditTrail->device,
                'auditable_type' => $auditTrail->auditable_type,
                'auditable_id' => $auditTrail->auditable_id,
                'meta' => $auditTrail->meta,
                'context' => $auditTrail->context,
                'context_text' => $this->formatContext($auditTrail->context),
                'hasChanges' => $auditTrail->changes->isNotEmpty(),
                'changes' => $auditTrail->changes->map(function ($change) {
                    return [
                        'id' => $change->id,
                        'field' => $change->field,
                        'before' => $change->old_value,
                        'after' => $change->new_value,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Convert context array/json to displayable text
     */
    private function formatContext($context): ?string
    {
        if (empty($context)) {
            return null;
        }

        if (is_string($context)) {
            return $context;
        }

        if (is_array($context)) {
            if (isset($context['note']) && !empty($context['note'])) {
                return (string) $context['note'];
            }

            return collect($context)
                ->map(function ($value, $key) {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }

                    return ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                })
                ->implode(', ');
        }

        return null;
    }
}
