<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Role;
use App\Models\RolePrivilege;
use Illuminate\Database\Seeder;

class BiometricDeviceModuleSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            ['admin.biometric-device.index', 'Biometric Devices', true],
            ['admin/biometric-device/add', 'Biometric Device Add', false],
            ['admin/biometric-device/edit', 'Biometric Device Edit', false],
            ['admin/biometric-device/delete', 'Biometric Device Delete', false],
        ];

        $template = Module::query()
            ->where(function ($q) {
                $q->where('route', 'admin.third-party.index')
                    ->orWhere('route', 'like', '%third-party%');
            })
            ->orderBy('id')
            ->first();

        $categoryId = $template?->module_category_id
            ?? ModuleCategory::where('is_active', 1)->orderBy('display_order')->value('ID');

        if (! $categoryId) {
            $this->command->warn('No module category found. Skipping Biometric Device modules.');

            return;
        }

        $css = $template->css_class ?? 'bi bi-fingerprint';
        $nextDisplayOrder = (int) (Module::withTrashed()->max('display_order') ?? 0);

        foreach ($pairs as [$route, $label, $showMenu]) {
            if (Module::where('route', $route)->exists()) {
                continue;
            }

            $nextDisplayOrder += 1;

            $new = Module::create([
                'module_category_id' => $categoryId,
                'module_name' => $label,
                'route' => $route,
                'show_in_menu' => $showMenu ? 1 : 0,
                'css_class' => $css,
                'display_order' => $nextDisplayOrder,
            ]);

            $roleIds = Role::query()->pluck('id');

            foreach ($roleIds as $roleId) {
                RolePrivilege::firstOrCreate(
                    [
                        'role_id' => $roleId,
                        'module_id' => $new->id,
                    ],
                    []
                );
            }

            $this->command->info('Registered module: '.$route);
        }
    }
}
