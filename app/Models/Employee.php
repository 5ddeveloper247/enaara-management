<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Role;
use App\Traits\LogsActivity;

class Employee extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'employees';

    protected $fillable = [
        'full_name', 'father_name', 'employee_code', 'tas_id', 'organization_id', 'sbu_id', 'department_id', 'department_ids',
        'employee_type_id', 'employee_type', 'employment_type', 'designation', 'designation_id', 'grade', 'branch',
        'location', 'email', 'phone', 'cnic', 'cnic_issue_date', 'cnic_expiry', 'father_cnic', 'ntn', 'gender',
        'nationality', 'dob', 'domicile_district', 'domicile_province', 'city_of_birth',
        'religion', 'sect', 'marital_status', 'spouse_name', 'spouse_cnic', 'spouse_nationality', 'nok_name', 'nok_cnic',
        'nok_relation', 'nok_dob', 'nok_contact', 'nok_cnic_expiry_date', 'is_ex_armed_force', 'line_manager_id', 'is_manager',
        'is_active', 'employee_status', 'termination_reason', 'termination_date',
        'suspension_reason', 'suspension_start_date', 'suspension_end_date',
        'role_id', 'site', 'join_date', 'floor_access', 'is_father_deceased',
        'biometric_id', 'sync_with_biometric',
        'employment_category', 'intern_type', 'intern_duration', 'contractual_type',
        'contract_start_date', 'contract_end_date', 'probation_start_date', 'probation_end_date',
        'engagement_mode', 'hybrid_days',
        'standard_schedule_mode', 'working_days', 'working_start_time', 'working_end_time',
        'opening_grace_period', 'closing_grace_period',
    ];

    protected $casts = [
        'dob'                 => 'date',
        'nok_dob'             => 'date',
        'nok_cnic_expiry_date' => 'date',
        'cnic_issue_date'     => 'date',
        'cnic_expiry'         => 'date',
        'join_date'           => 'date',
        'contract_start_date' => 'date',
        'contract_end_date'   => 'date',
        'probation_start_date' => 'date',
        'probation_end_date' => 'date',
        'termination_date'    => 'date',
        'suspension_start_date' => 'date',
        'suspension_end_date'   => 'date',
        'floor_access'        => 'boolean',
        'sync_with_biometric' => 'boolean',
        'is_manager'          => 'boolean',
        'is_active'           => 'boolean',
        'is_ex_armed_force'   => 'boolean',
        'is_father_deceased'  => 'boolean',
        'hybrid_days'         => 'array',
        'department_ids'      => 'array',
        'working_days'        => 'array',
    ];

    public function organization()      { return $this->belongsTo(Organization::class); }
    public function assignedDesignation() { return $this->belongsTo(Designation::class, 'designation_id'); }
    public function sbu()               { return $this->belongsTo(Sbu::class); }
    public function department()        { return $this->belongsTo(Department::class); }
    public function lineManager()       { return $this->belongsTo(Employee::class, 'line_manager_id'); }
    public function user()              { return $this->hasOne(User::class, 'employee_id'); }
    public function policeVerification(){ return $this->hasOne(EmployeePoliceVerification::class); }
    public function armedForce()        { return $this->hasOne(EmployeeArmedForce::class); }
    public function contact()           { return $this->hasOne(EmployeeContact::class); }
    public function bankDetails()       { return $this->hasMany(EmployeeBankDetail::class)->orderByDesc('id'); }
    public function familyMembers()
    {
        return $this->hasMany(EmployeeFamilyMember::class)->orderByDesc('id');
    }
    public function academics()         { return $this->hasMany(EmployeeAcademic::class); }
    public function certificates()      { return $this->hasMany(EmployeeCertificate::class)->orderByDesc('id'); }
    public function exEmployments()     { return $this->hasMany(EmployeeExEmployment::class); }
    public function medical()           { return $this->hasOne(EmployeeMedical::class); }
    public function references()        { return $this->hasMany(EmployeeReference::class); }
    public function mediaFiles()        { return $this->hasMany(MediaFile::class, 'module_id')->where('module_name', 'employee'); }
    public function role()              { return $this->belongsTo(Role::class, 'role_id'); }
    public function assignedFloors(): BelongsToMany
    {
        return $this->belongsToMany(SbuFloor::class, 'employee_floor_privileges', 'employee_id', 'sbu_floor_id')->withTimestamps();
    }
    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }

    public function leaveQuotas()
    {
        return $this->hasMany(EmployeeLeaveQuota::class, 'employee_id');
    }

    public function scopeShiftBasedWorkArrangement($query)
    {
        return $query->where('engagement_mode', 'shifts');
    }
}
