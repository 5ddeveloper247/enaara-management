<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\TemporaryPasswordNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private readonly UserRoleSyncService $userRoleSyncService
    ) {}

    public function index(): View
    {
        $linkedEmployeeIds = User::whereNotNull('employee_id')->pluck('employee_id');

        $employees = Employee::with(['role:id,name', 'contact:id,employee_id,email', 'sbu:id,name', 'mediaFiles'])
            ->whereNull('deleted_at')
            ->whereNotIn('id', $linkedEmployeeIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code', 'email', 'role_id', 'sbu_id']);

        $allEmployees = Employee::with(['role:id,name', 'contact:id,employee_id,email'])
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code', 'email', 'role_id']);

        return view('admin.users.index', compact('employees', 'allEmployees'));
    }

    public function getTableData(): array
    {
        $users = User::with(['roles', 'employee.department', 'employee.sbu', 'employee.mediaFiles'])
            ->orderByDesc('id')
            ->get();

        $departmentIds = [];
        foreach ($users as $user) {
            $employee = $user->employee;
            if (! $employee) {
                continue;
            }
            $ids = is_array($employee->department_ids) ? $employee->department_ids : [];
            foreach ($ids as $id) {
                $val = (int) $id;
                if ($val > 0) {
                    $departmentIds[] = $val;
                }
            }
            if ($employee->department_id) {
                $departmentIds[] = (int) $employee->department_id;
            }
        }
        $departmentIds = array_values(array_unique(array_filter($departmentIds)));
        $departmentNameById = empty($departmentIds)
            ? []
            : Department::query()
                ->whereIn('id', $departmentIds)
                ->pluck('name', 'id')
                ->all();

        $data = $users->map(function ($user) use ($departmentNameById) {
            $role       = $user->roles->first();
            $employee   = $user->employee;
            $department = '-';
            if ($employee) {
                $ids = is_array($employee->department_ids) ? $employee->department_ids : [];
                if ($employee->department_id) {
                    $ids[] = $employee->department_id;
                }
                $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
                $names = [];
                foreach ($ids as $id) {
                    $name = $departmentNameById[$id] ?? null;
                    if ($name) {
                        $names[] = $name;
                    }
                }
                if (! empty($names)) {
                    $department = implode(', ', $names);
                } else {
                    $department = $employee?->department?->name ?? '-';
                }
            }
            $empCode    = $employee?->employee_code ?? '-';
            $sbuName    = $employee?->sbu?->name ?? '-';
            
            $avatarUrl = null;
            if ($employee) {
                $photo = $employee->mediaFiles->where('file_type', 'photo')->first();
                if ($photo && $photo->file_path) {
                    $avatarUrl = asset('storage/' . $photo->file_path);
                }
            }

            $initials   = $this->getInitials($user->name);

            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'initials'      => $initials,
                'avatar_url'    => $avatarUrl,
                'employee_id'   => $employee?->id,
                'employee_code' => $empCode,
                'employee_name' => $employee?->full_name ?? '',
                'sbu_name'      => $sbuName,
                'department'    => $department,
                'role_id'       => $role?->id,
                'role'          => $role?->name ?? '-',
                'last_login'    => $user->updated_at?->diffForHumans() ?? '-',
                'is_active'     => (bool) $user->is_active,
            ];
        })->values()->all();

        return ['data' => $data];
    }

    public function getStats(): array
    {
        $users   = User::with('roles')->get();
        $total   = $users->count();
        $active  = $users->where('is_active', true)->count();

        $admins    = 0;
        $managers  = 0;
        $employees = 0;

        foreach ($users as $user) {
            $roleName = strtolower($user->roles->first()?->name ?? '');
            if (str_contains($roleName, 'admin'))   $admins++;
            elseif (str_contains($roleName, 'manager')) $managers++;
            else $employees++;
        }

        return compact('total', 'active', 'admins', 'managers', 'employees');
    }

    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $plainPassword = Str::password(12);

            $user = User::create([
                'name'                 => $data['name'],
                'email'                => $data['email'],
                'employee_id'          => $data['employee_id'] ?? null,
                'is_active'            => true,
                'password'             => $plainPassword,
                'must_change_password' => true,
            ]);

            $employee = Employee::findOrFail((int) $data['employee_id']);
            $this->userRoleSyncService->syncFromEmployee($employee);

            $user->notify(new TemporaryPasswordNotification($plainPassword, 'welcome'));

            Log::info('User created', ['user_id' => $user->id, 'email' => $user->email]);

            return $user;
        });
    }

    public function sendTemporaryPasswordReset(int $id): User
    {
        return DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            $plain = Str::password(12);

            $user->password             = $plain;
            $user->must_change_password = true;
            $user->save();

            $user->notify(new TemporaryPasswordNotification($plain, 'admin_reset'));

            Log::info('User temporary password reset', ['user_id' => $user->id]);

            return $user;
        });
    }

    public function update(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            $updateData = [
                'name'        => $data['name'],
                'email'       => $data['email'],
                'employee_id' => $data['employee_id'] ?? null,
            ];

            $user->update($updateData);

            $employee = Employee::findOrFail((int) $data['employee_id']);
            $this->userRoleSyncService->syncFromEmployee($employee);

            Log::info('User updated', ['user_id' => $user->id]);

            return $user;
        });
    }

    public function updateStatus(int $id, bool $isActive): User
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => $isActive]);
        Log::info('User status updated', ['user_id' => $id, 'is_active' => $isActive]);
        return $user;
    }

    public function destroy(int $id): void
    {
        $user = User::findOrFail($id);
        $user->userRoles()->whereNull('deleted_at')->update(['deleted_at' => now()]);
        $user->delete();
        Log::info('User deleted', ['user_id' => $id]);
    }

    private function getInitials(string $name): string
    {
        $words = array_values(array_filter(explode(' ', trim($name))));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return strtoupper(substr($name, 0, 2)) ?: '??';
    }
}
