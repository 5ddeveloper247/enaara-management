<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('third_parties', 'ntn')) {
            Schema::table('third_parties', function (Blueprint $table) {
                $table->boolean('is_individual_contractor')->default(false)->after('specify_service_type');
                $table->string('ntn', 13)->nullable()->after('is_individual_contractor');
                $table->string('contractor_cnic', 15)->nullable()->after('ntn');
            });
        }

        if (Schema::hasColumn('third_parties', 'ntn_or_cnic')) {
            $rows = DB::table('third_parties')->select(['id', 'ntn_or_cnic'])->get();
            foreach ($rows as $row) {
                $raw = (string) ($row->ntn_or_cnic ?? '');
                $digits = preg_replace('/\D/', '', $raw);
                $len = strlen($digits);
                $isIndividual = false;
                $ntn = null;
                $contractorCnic = null;
                if ($len >= 13 && $len <= 15) {
                    $contractorCnic = $digits;
                    $isIndividual = true;
                } elseif ($len === 7 || $len === 13) {
                    $ntn = $digits;
                    $isIndividual = false;
                } elseif ($len > 0 && $len < 13) {
                    $ntn = $digits;
                    $isIndividual = false;
                }
                DB::table('third_parties')->where('id', $row->id)->update([
                    'is_individual_contractor' => $isIndividual,
                    'ntn'                      => $ntn,
                    'contractor_cnic'          => $contractorCnic,
                ]);
            }

            Schema::table('third_parties', function (Blueprint $table) {
                $table->dropColumn('ntn_or_cnic');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('third_parties', 'ntn_or_cnic')) {
            Schema::table('third_parties', function (Blueprint $table) {
                $table->string('ntn_or_cnic', 25)->nullable()->after('specify_service_type');
            });
        }

        if (Schema::hasColumn('third_parties', 'ntn')) {
            $rows = DB::table('third_parties')->select(['id', 'ntn', 'contractor_cnic', 'is_individual_contractor'])->get();
            foreach ($rows as $row) {
                $merged = '';
                if (! empty($row->is_individual_contractor) && ! empty($row->contractor_cnic)) {
                    $merged = (string) $row->contractor_cnic;
                } elseif (! empty($row->ntn)) {
                    $merged = (string) $row->ntn;
                }
                DB::table('third_parties')->where('id', $row->id)->update(['ntn_or_cnic' => $merged !== '' ? $merged : null]);
            }
        }

        if (Schema::hasColumn('third_parties', 'is_individual_contractor')) {
            Schema::table('third_parties', function (Blueprint $table) {
                $table->dropColumn(['is_individual_contractor', 'ntn', 'contractor_cnic']);
            });
        }
    }
};
