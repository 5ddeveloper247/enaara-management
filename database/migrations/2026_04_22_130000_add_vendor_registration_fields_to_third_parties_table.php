<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->string('vendor_id', 40)->nullable()->after('organization_id');
            $table->string('service_type', 50)->nullable()->after('third_party_name');
            $table->string('ntn_or_cnic', 25)->nullable()->after('service_type');

            $table->string('contact_person_name', 120)->nullable()->after('ntn_or_cnic');
            $table->string('mobile_number', 20)->nullable()->after('contact_person_name');
            $table->string('email', 150)->nullable()->after('mobile_number');

            $table->string('supervisor_name', 120)->nullable()->after('email');
            $table->string('supervisor_cnic', 20)->nullable()->after('supervisor_name');
            $table->string('supervisor_mobile_number', 20)->nullable()->after('supervisor_cnic');

            $table->date('contract_start_date')->nullable()->after('supervisor_mobile_number');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->string('scope_of_work', 500)->nullable()->after('contract_end_date');
            $table->unsignedInteger('estimated_staff_count')->nullable()->after('scope_of_work');

            $table->string('company_registration_document_path')->nullable()->after('estimated_staff_count');
            $table->string('contract_copy_path')->nullable()->after('company_registration_document_path');
            $table->string('remarks', 500)->nullable()->after('contract_copy_path');

            $table->unique('vendor_id', 'third_parties_vendor_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->dropUnique('third_parties_vendor_id_unique');
            $table->dropColumn([
                'vendor_id',
                'service_type',
                'ntn_or_cnic',
                'contact_person_name',
                'mobile_number',
                'email',
                'supervisor_name',
                'supervisor_cnic',
                'supervisor_mobile_number',
                'contract_start_date',
                'contract_end_date',
                'scope_of_work',
                'estimated_staff_count',
                'company_registration_document_path',
                'contract_copy_path',
                'remarks',
            ]);
        });
    }
};

