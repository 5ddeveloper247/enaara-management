<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThirdParty extends Model
{
    use LogsActivity;

    protected $table = 'third_parties';

    protected $fillable = [
        'organization_id',
        'vendor_id',
        'third_party_name',
        'service_type',
        'specify_service_type',
        'is_individual_contractor',
        'ntn',
        'contractor_cnic',
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
        'city',
        'address',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_individual_contractor' => 'boolean',
        'is_active' => 'boolean',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'estimated_staff_count' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'third_party_organizations')
            ->withTimestamps();
    }

    public function sbus(): BelongsToMany
    {
        return $this->belongsToMany(Sbu::class, 'third_party_sbu')
            ->withTimestamps();
    }
}
