<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutsourcedEmployee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'cnic_number',
        'mobile_number',
        'photo_path',
        'contractor_company_id',
        'supervisor_name',
        'supervisor_contact_number',
        'organization_id',
        'sbu_id',
        'department_id',
        'job_role_trade',
        'placement_floor',
        'date_of_deployment',
        'biometric_id',
        'attendance_access',
    ];

    protected $casts = [
        'date_of_deployment' => 'date',
        'attendance_access' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function contractorCompany(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class, 'contractor_company_id');
    }
}

