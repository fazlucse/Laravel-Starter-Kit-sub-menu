<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all authenticated users
    }

    public function rules(): array
    {
        return [
            // === Personal Info ===
            'person_id' => 'required|integer|min:1',
            'person_name' => 'required|string|max:150',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'nullable|string|max:5',
            'work_experience' => 'nullable|integer|min:0',
            'skills' => 'nullable|string',

            // === Company Info ===
            'company_id' => 'nullable|integer',
            'company_name' => 'nullable|string|max:150',
            'fin_com_id' => 'nullable|integer',
            'fin_com_name' => 'nullable|string|max:150',
            'division_id' => 'nullable|integer',
            'division_name' => 'nullable|string|max:150',
            'department_id' => 'required|integer|min:1',
            'department_name' => 'required|string|max:100',
            'designation_id' => 'required|integer|min:1',
            'designation_name' => 'required|string|max:100',
            'deparment_head' => 'nullable|integer',
            'deparment_head_name' => 'nullable|string|max:150',

            // === Employment ===
            'employee_code' => 'required|string|max:20|unique:employees,employee_code' . ($this->employee ? ',' . $this->employee->id : ''),
            'employee_id' => 'required|string|max:20|unique:employees,employee_id' . ($this->employee ? ',' . $this->employee->id : ''),
            'joining_date' => 'required|date',
            'confirmation_date' => 'nullable|date|after_or_equal:joining_date',
            'probation_end_date' => 'nullable|date|after_or_equal:joining_date',
            'effective_date' => 'nullable|date',
            'employment_type' => 'required|in:Permanent,Contract,Intern,Probation,Freelancer',
            'employee_status' => 'nullable|in:Active,Inactive,Terminated,Resigned,Retired,On Leave',
            'work_location' => 'nullable|string|max:100',
            'shift' => 'nullable|string|max:50',
            'official_email' => 'nullable|email|max:100',
            'official_phone' => 'nullable|string|max:20',
            'office_in_time' => 'nullable|date_format:H:i',
            'office_out_time' => 'nullable|date_format:H:i',
            'late_time' => 'nullable|integer|min:0',
            'reporting_manager_id' => 'nullable|integer',
            'reporting_manager_name' => 'nullable|string|max:150',
            'second_reporting_manager_id' => 'nullable|integer',
            'second_reporting_manager_name' => 'nullable|string|max:150',

            // === Emergency ===
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',

            // === Performance ===
            'last_appraisal_date' => 'nullable|date',
            'next_appraisal_date' => 'nullable|date|after:last_appraisal_date',
            'last_promotion_date' => 'nullable|date',
            'next_promotion_due' => 'nullable|date|after:last_promotion_date',

            // === Salary ===
            'currency' => 'nullable|in:USD,EUR,GBP,BDT,INR',
            'basic_salary' => 'required|numeric|min:0',
            'house_rent_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'gross_salary' => 'nullable|numeric|min:0',
            'total_salary' => 'nullable|numeric|min:0',

            // === Banking & Tax ===
            'bank_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc_code' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'tax_status' => 'nullable|in:Resident,Non-Resident',
            'social_security_no' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:20',
            'is_tax_dedction' => 'required|in:0,1',
            'is_salary_stop' => 'required|in:0,1',

            // === Notes ===
            'office_time' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'person_id.required' => 'Person ID is required.',
            'person_name.required' => 'Person Name is required.',
            'department_id.required' => 'Department ID is required.',
            'department_name.required' => 'Department Name is required.',
            'designation_id.required' => 'Designation ID is required.',
            'designation_name.required' => 'Designation Name is required.',
            'employee_code.required' => 'Employee Code is required.',
            'employee_code.unique' => 'This Employee Code is already taken.',
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'This Employee ID is already taken.',
            'joining_date.required' => 'Joining Date is required.',
            'employment_type.required' => 'Employment Type is required.',
            'basic_salary.required' => 'Basic Salary is required.',
            'basic_salary.min' => 'Basic Salary must be at least 0.',
            'is_tax_dedction.in' => 'Tax Deduction must be Yes or No.',
            'is_salary_stop.in' => 'Salary Stop must be Yes or No.',
        ];
    }
}