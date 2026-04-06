<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditTrailService
{
    public function log(
        string $action,
        string $category,
        string $description,
        string $severity = 'info',
        ?Model $auditable = null,
        array $changes = [],
        array $meta = [],
        array $context = []
    ): AuditTrail {
        $user = Auth::user();
        $employee = $user?->employee;
        $request = request();

        // Determine IDs from context or auditable model
        $orgId = $employee?->organization_id;
        $sbuId = $employee?->sbu_id;
        $deptId = $employee?->department_id;

        if ($auditable) {
            $class = get_class($auditable);
            if ($class === 'App\Models\Organization') {
                $orgId = $orgId ?: ($auditable->id ?? null);
            } elseif (isset($auditable->organization_id)) {
                $orgId = $orgId ?: $auditable->organization_id;
            }

            if ($class === 'App\Models\Sbu') {
                $sbuId = $sbuId ?: ($auditable->id ?? null);
                $orgId = $orgId ?: ($auditable->organization_id ?? null);
            } elseif (isset($auditable->sbu_id)) {
                $sbuId = $sbuId ?: $auditable->sbu_id;
            }

            if ($class === 'App\Models\Department') {
                $deptId = $deptId ?: ($auditable->id ?? null);
                $sbuId = $sbuId ?: ($auditable->sbu_id ?? null);
                $orgId = $orgId ?: ($auditable->organization_id ?? null);
            } elseif (isset($auditable->department_id)) {
                $deptId = $deptId ?: $auditable->department_id;
            }
        }

        try {
            $audit = AuditTrail::create([
                'action_at' => now(),
                'user_id' => $user?->id,
                'employee_id' => $employee?->id,
                'organization_id' => $orgId ?? null,
                'sbu_id' => $sbuId ?? null,
                'department_id' => $deptId ?? null,
                'module' => strtolower(trim($category)),
                'action' => strtolower(trim($action)),
                'action_category' => trim($category),
                'severity' => strtolower(trim($severity)),
                'description' => $description,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
                'device' => $this->detectDevice($request ? $request->userAgent() : null),
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->id,
                'meta' => !empty($meta) ? $meta : null,
                'context' => !empty($context) ? $context : null,
            ]);

            foreach ($changes as $field => $change) {
                $audit->changes()->create([
                    'field' => $field,
                    'old_value' => is_array($change['old']) ? json_encode($change['old']) : $change['old'],
                    'new_value' => is_array($change['new']) ? json_encode($change['new']) : $change['new'],
                ]);
            }

            \Illuminate\Support\Facades\Log::info("AuditTrail successfully created record: ID={$audit->id}");
            return $audit;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("AuditTrail creation FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log a model action (Created, Updated, Deleted)
     */
    public function logModelAction(Model $model, string $action, ?string $description = null): ?AuditTrail
    {
        $category = class_basename($model);
        $changes = [];

        if ($action === 'updated') {
            $dirty = $model->getDirty();
            
            foreach ($dirty as $field => $newValue) {
                if ($field === 'updated_at') continue;
                $changes[$field] = [
                    'old' => $model->getOriginal($field),
                    'new' => $newValue,
                ];
            }

            if (empty($changes)) {
                return null;
            }
        }

        if (!$description) {
            $description = ucfirst($category) . " has been " . $action;
            if ($action === 'updated') {
                $fields = array_keys($changes);
                $description .= ": " . implode(', ', $fields);
            }
        }

        return $this->log(
            action: $action,
            category: $category,
            description: $description,
            severity: $action === 'deleted' ? 'warning' : 'info',
            auditable: $model,
            changes: $changes
        );
    }

    private function detectDevice(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        $agent = strtolower($userAgent);

        if (str_contains($agent, 'mobile')) {
            return 'Mobile Browser';
        }

        if (str_contains($agent, 'tablet') || str_contains($agent, 'ipad')) {
            return 'Tablet';
        }

        return 'Desktop Browser';
    }
}