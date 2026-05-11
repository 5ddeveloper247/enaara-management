<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize old/inconsistent attachment_type values in media_files
     * to the canonical names used by RequiredDocumentTypeSeeder.
     */
    public function up(): void
    {
        $renames = [
            // CNIC
            'CNIC / National ID'              => 'Employee CNIC (Front)',

            // Family
            'Family Character Certificate'    => 'Family Registration Certificate',
            'Family registration certificate' => 'Family Registration Certificate',

            // Police
            'Police verification letter'      => 'Police Verification Letter',

            // Medical
            'Medical fitness certificate'     => 'Medical Document',

            // Employment docs
            'Previous experience letter'      => 'Experience Letter',

            // Academics
            'Academic certificate'            => 'Academic Transcript',

            // Professional
            'Professional certificates'       => 'Professional Certificate',
        ];

        foreach ($renames as $old => $new) {
            DB::table('media_files')
                ->where('attachment_type', $old)
                ->update(['attachment_type' => $new]);
        }
    }

    /**
     * Rollback is intentionally a no-op — we cannot safely reverse data renames.
     */
    public function down(): void
    {
        //
    }
};
