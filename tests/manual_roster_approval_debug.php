<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\Role;

$assignee = Employee::find(32);
$gmRoles = Role::whereIn('id', [49, 53])->get();
foreach ($gmRoles as $r) {
    echo "Role {$r->id}: name=[{$r->name}] slug=[{$r->slug}] dept={$r->department_id}\n";
}
$gmEmp = Employee::find(1);
echo "Emp 1 role: " . ($gmEmp->role->name ?? '-') . " slug=" . ($gmEmp->role->slug ?? '-') . "\n";

$resolver = app(\App\Services\ShiftRosterApproverResolver::class);
$gm = $resolver->resolveGmForEmployee($assignee);
echo 'GM for executive 32: ' . ($gm ? $gm->id . ' ' . $gm->full_name : 'NONE') . "\n";
