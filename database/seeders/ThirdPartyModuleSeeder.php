<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Role;
use App\Models\RolePrivilege;
use Illuminate\Database\Seeder;

class ThirdPartyModuleSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            ['admin.third-party.index', 'Third Party', true],
            ['admin/third-party/add', 'Third Party Add', false],
            ['admin/third-party/edit', 'Third Party Edit', false],
            ['admin/third-party/delete', 'Third Party Delete', false],
        ];

        $template = Module::query()
            ->where(function ($q) {
                $q->where('route', 'admin.sbu.index')
                    ->orWhere('route', 'like', '%sbu%');
            })
            ->orderBy('id')
            ->first();

        $categoryId = $template?->module_category_id
            ?? ModuleCategory::where('is_active', 1)->orderBy('display_order')->value('ID');

        if (! $categoryId) {
            $this->command->warn('No module category found. Skipping Third Party modules.');

            return;
        }

        $baseOrder = $template
            ? (int) $template->display_order
            : (int) (Module::where('module_category_id', $categoryId)->max('display_order') ?? 0);

        $css = $template->css_class ?? 'bi bi-people';

        foreach ($pairs as $idx => [$route, $label, $showMenu]) {
            if (Module::where('route', $route)->exists()) {
                continue;
            }

            $new = Module::create([
                'module_category_id' => $categoryId,
                'module_name'        => $label,
                'route'              => $route,
                'show_in_menu'       => $showMenu ? 1 : 0,
                'css_class'          => $css,
                'display_order'      => $baseOrder + 1 + $idx,
            ]);

            $roleIds = Role::query()->pluck('id');

            foreach ($roleIds as $roleId) {
                RolePrivilege::firstOrCreate(
                    [
                        'role_id'   => $roleId,
                        'module_id' => $new->id,
                    ],
                    []
                );
            }

            $this->command->info('Registered module: ' . $route);
        }
    }
}
