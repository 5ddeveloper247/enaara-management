<?php

use App\Models\OutsourcedEmployee;
use App\Models\ThirdParty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            if (! Schema::hasColumn('outsourced_employees', 'contractor_company_id')) {
                $table->foreignId('contractor_company_id')->nullable()->after('photo_path')->constrained('third_parties')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('outsourced_employees', 'contractor_company_name')) {
            $vendorIdByName = ThirdParty::query()
                ->select(['id', 'third_party_name'])
                ->whereNotNull('third_party_name')
                ->get()
                ->mapWithKeys(function (ThirdParty $vendor) {
                    $key = mb_strtolower(trim((string) $vendor->third_party_name));
                    return $key !== '' ? [$key => (int) $vendor->id] : [];
                })
                ->all();

            $updates = OutsourcedEmployee::query()
                ->withTrashed()
                ->select(['id', 'contractor_company_name'])
                ->orderBy('id')
                ->get()
                ->map(function (OutsourcedEmployee $row) use ($vendorIdByName) {
                    $lookup = mb_strtolower(trim((string) $row->contractor_company_name));
                    $vendorId = $vendorIdByName[$lookup] ?? null;

                    return $vendorId ? [
                        'id' => (int) $row->id,
                        'contractor_company_id' => (int) $vendorId,
                    ] : null;
                })
                ->filter()
                ->values()
                ->all();

            if ($updates !== []) {
                OutsourcedEmployee::query()->upsert($updates, ['id'], ['contractor_company_id']);
            }

            Schema::table('outsourced_employees', function (Blueprint $table) {
                $table->dropColumn('contractor_company_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            if (! Schema::hasColumn('outsourced_employees', 'contractor_company_name')) {
                $table->string('contractor_company_name', 150)->nullable()->after('photo_path');
            }
        });

        if (Schema::hasColumn('outsourced_employees', 'contractor_company_id')) {
            $updates = OutsourcedEmployee::query()
                ->with(['contractorCompany:id,third_party_name'])
                ->withTrashed()
                ->select(['id', 'contractor_company_id'])
                ->orderBy('id')
                ->get()
                ->map(function (OutsourcedEmployee $row) {
                    return [
                        'id' => (int) $row->id,
                        'contractor_company_name' => $row->contractorCompany?->third_party_name,
                    ];
                })
                ->values()
                ->all();

            if ($updates !== []) {
                OutsourcedEmployee::query()->upsert($updates, ['id'], ['contractor_company_name']);
            }

            Schema::table('outsourced_employees', function (Blueprint $table) {
                $table->dropForeign(['contractor_company_id']);
                $table->dropColumn('contractor_company_id');
            });
        }
    }
};
