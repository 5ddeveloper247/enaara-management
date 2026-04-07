<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('leave_type_department')) {
            Schema::create('leave_type_department', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // Migrate and merge existing data
        if (Schema::hasColumn('leave_types', 'department_id')) {
            // Group by organization, name, code, quota to identify "same" leave types
            $groups = DB::table('leave_types')
                ->select('organization_id', 'name', 'code', 'annual_quota', 'is_active')
                ->groupBy('organization_id', 'name', 'code', 'annual_quota', 'is_active')
                ->get();

            foreach ($groups as $group) {
                // Get all IDs belonging to this group
                $query = DB::table('leave_types')
                    ->where('organization_id', $group->organization_id)
                    ->where('name', $group->name)
                    ->where('annual_quota', $group->annual_quota)
                    ->where('is_active', $group->is_active);
                
                if ($group->code) {
                    $query->where('code', $group->code);
                } else {
                    $query->whereNull('code');
                }

                $ids = $query->pluck('id', 'department_id')->toArray();
                
                if (empty($ids)) continue;

                // Pick the first one as the "canonical" record
                $mainId = reset($ids);
                
                // Move all department associations to pivot
                foreach ($ids as $deptId => $ltId) {
                    if ($deptId) {
                        // Check if already exists to avoid duplicates
                        $exists = DB::table('leave_type_department')
                            ->where('leave_type_id', $mainId)
                            ->where('department_id', $deptId)
                            ->exists();
                        
                        if (!$exists) {
                            DB::table('leave_type_department')->insert([
                                'leave_type_id' => $mainId,
                                'department_id' => $deptId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                // Delete other records in the group
                $others = array_diff($ids, [$mainId]);
                if (!empty($others)) {
                    DB::table('leave_types')->whereIn('id', $others)->delete();
                }
            }
        }
        
        // Remove the department_id column as it's now in the pivot table
        // Drop old unique constraints if they exist
        try {
            DB::statement('ALTER TABLE leave_types DROP INDEX leave_types_org_dept_code_unique');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE leave_types DROP INDEX leave_types_organization_id_code_unique');
        } catch (\Exception $e) {}

        if (Schema::hasColumn('leave_types', 'department_id')) {
            try {
                Schema::table('leave_types', function (Blueprint $table) {
                    $table->dropForeign(['department_id']);
                });
            } catch (\Exception $e) {}
            
            try {
                Schema::table('leave_types', function (Blueprint $table) {
                    $table->dropColumn('department_id');
                });
            } catch (\Exception $e) {}
        }
        
        // Re-apply unique constraint per organization/code
        try {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->unique(['organization_id', 'code'], 'leave_types_organization_id_code_unique');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('leave_types', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('organization_id')->constrained('departments')->nullOnDelete();
        });

        // Re-migrate back
        $pivots = DB::table('leave_type_department')->get();
        foreach ($pivots as $p) {
            DB::table('leave_types')->where('id', $p->leave_type_id)->update(['department_id' => $p->department_id]);
        }

        Schema::dropIfExists('leave_type_department');
    }
};
