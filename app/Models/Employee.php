<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'person_id', 'person_name', 'company_id', 'company_name', 'fin_com_id', 'fin_com_name',
        'division_id', 'division_name', 'department_id', 'department_name', 'designation_id',
        'designation_name', 'employee_code', 'employee_id', 'joining_date', 'confirmation_date',
        'probation_end_date', 'effective_date', 'employment_type', 'work_location', 'shift',
        'official_email', 'official_phone', 'office_in_time', 'office_out_time', 'late_time',
        'employee_status', 'gross_salary', 'basic_salary', 'house_rent_allowance', 'medical_allowance',
        'transport_allowance', 'other_allowances', 'overtime_rate', 'total_salary', 'currency',
        'bank_name', 'bank_account_no', 'bank_ifsc_code', 'pan_number', 'tax_status',
        'social_security_no', 'passport_number', 'is_tax_dedction', 'is_salary_stop',
        'emergency_contact_name', 'emergency_contact_phone', 'marital_status', 'gender',
        'blood_group', 'work_experience', 'skills', 'reporting_manager_id', 'reporting_manager_name',
        'second_reporting_manager_id', 'second_reporting_manager_name', 'deparment_head',
        'deparment_head_name', 'last_appraisal_date', 'next_appraisal_date', 'last_promotion_date',
        'next_promotion_due', 'office_time'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'probation_end_date' => 'date',
        'effective_date' => 'date',
        'last_appraisal_date' => 'date',
        'next_appraisal_date' => 'date',
        'last_promotion_date' => 'date',
        'next_promotion_due' => 'date',
        'office_in_time' => 'datetime:H:i',
        'office_out_time' => 'datetime:H:i',
        'basic_salary' => 'decimal:2',
        'house_rent_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'total_salary' => 'decimal:2',
    ];
}