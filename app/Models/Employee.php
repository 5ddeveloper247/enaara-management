<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Role;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'full_name', 'father_name', 'employee_code', 'organization_id', 'sbu_id', 'department_id',
        'employee_type_id', 'employee_type', 'employment_type', 'designation', 'grade', 'branch',
        'location', 'email', 'phone', 'cnic', 'cnic_expiry', 'father_cnic', 'ntn', 'gender',
        'nationality', 'dob', 'domicile_district', 'domicile_province', 'city_of_birth',
        'religion', 'sect', 'marital_status', 'spouse_name', 'nok_name', 'nok_cnic',
        'nok_relation', 'nok_dob', 'nok_contact', 'line_manager_id', 'is_manager',
        'is_active', 'role_id', 'site', 'join_date', 'floor_access',
        'biometric_id', 'sync_with_biometric',
    ];

    protected $casts = [
        'dob'                 => 'date',
        'nok_dob'             => 'date',
        'cnic_expiry'         => 'date',
        'join_date'           => 'date',
        'floor_access'        => 'boolean',
        'sync_with_biometric' => 'boolean',
        'is_manager'          => 'boolean',
        'is_active'           => 'boolean',
    ];

    public function organization()      { return $this->belongsTo(Organization::class); }
    public function sbu()               { return $this->belongsTo(Sbu::class); }
    public function department()        { return $this->belongsTo(Department::class); }
    public function lineManager()       { return $this->belongsTo(Employee::class, 'line_manager_id'); }
    public function user()              { return $this->hasOne(User::class, 'employee_id'); }
    public function policeVerification(){ return $this->hasOne(EmployeePoliceVerification::class); }
    public function armedForce()        { return $this->hasOne(EmployeeArmedForce::class); }
    public function contact()           { return $this->hasOne(EmployeeContact::class); }
    public function bankDetail()        { return $this->hasOne(EmployeeBankDetail::class); }
    public function familyMembers()     { return $this->hasMany(EmployeeFamilyMember::class); }
    public function academics()         { return $this->hasMany(EmployeeAcademic::class); }
    public function exEmployments()     { return $this->hasMany(EmployeeExEmployment::class); }
    public function medical()           { return $this->hasOne(EmployeeMedical::class); }
    public function references()        { return $this->hasMany(EmployeeReference::class); }
    public function mediaFiles()        { return $this->hasMany(MediaFile::class, 'module_id')->where('module_name', 'employee'); }
    public function role()              { return $this->belongsTo(Role::class, 'role_id'); }
    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }
}
