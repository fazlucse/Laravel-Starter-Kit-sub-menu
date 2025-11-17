<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'person_name',
        'gender',
        'marital_status',
        'blood_group',
        'work_experience',
        'skills',
        'company_id',
        'company_name',
        'fin_com_id',
        'fin_com_name',
        'division_id',
        'division_name',
        'department_id',
        'department_name',
        'designation_id',
        'designation_name',
        'department_head',
        'department_head_name',
        'employee_id',
        'employee_code',
        'joining_date',
        'confirmation_date',
        'probation_end_date',
        'effective_date',
        'employment_type',
        'employee_status',
        'work_location',
        'shift',
        'official_email',
        'official_phone',
        'office_in_time',
        'office_out_time',
        'late_time',
        'reporting_manager_id',
        'reporting_manager_name',
        'second_reporting_manager_id',
        'second_reporting_manager_name',
        'emergency_contact_name',
        'emergency_contact_phone',
        'last_appraisal_date',
        'next_appraisal_date',
        'last_promotion_date',
        'next_promotion_due',
        'currency',
        'basic_salary',
        'house_rent_allowance',
        'medical_allowance',
        'transport_allowance',
        'other_allowances',
        'overtime_rate',
        'bank_name',
        'bank_account_no',
        'bank_ifsc_code',
        'pan_number',
        'tax_status',
        'social_security_no',
        'passport_number',
        'is_tax_deduction',
        'is_salary_stop',
        'office_time',
        'gross_salary',
        'total_salary',
    ];
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(InfoMaster::class, 'designation_id');
    }

    public function departmentHead()
    {
        return $this->belongsTo(Person::class, 'department_head_id');
    }

    public function reportingManager()
    {
        return $this->belongsTo(Person::class, 'reporting_manager_id');
    }

    public function secondReportingManager()
    {
        return $this->belongsTo(Person::class, 'second_reporting_manager_id');
    }
}