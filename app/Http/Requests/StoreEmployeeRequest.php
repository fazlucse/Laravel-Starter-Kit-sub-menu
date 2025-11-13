<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // allow all users; adjust if needed
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            // === Personal Info ===
            'person_id' => [
                'required',
                'integer',
                'min:1',
                'exists:people,id',
            ],
            'person_name' => 'required|string|max:150',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'work_experience' => 'nullable|integer|min:0',
            'skills' => 'nullable|string',

            // === Company Info ===
            'company_id' => 'nullable|integer|exists:companies,id',
            'fin_com_id' => 'nullable|integer|exists:companies,id',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'department_id' => 'nullable|integer|min:1|exists:departments,id',
            'designation_id' => 'nullable|integer|min:1|exists:info_master,id',
            'department_head' => 'nullable|integer|exists:people,id',
            'department_head_name' => 'nullable|string|max:150',

            // === Employment ===
            'employee_id' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'employee_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'joining_date' => 'nullable|date',
            'confirmation_date' => 'nullable|date|after_or_equal:joining_date',
            'probation_end_date' => 'nullable|date|after_or_equal:joining_date',
            'effective_date' => 'nullable|date',
            'employment_type' => 'nullable|in:Permanent,Contract,Intern,Probation,Freelancer',
            'employee_status' => 'nullable|in:Active,Inactive,Terminated,Resigned,Retired,On Leave',
            'work_location' => 'nullable|string|max:100',
            'shift' => 'nullable|string|max:50',
            'official_email' => 'nullable|email|max:100',
            'official_phone' => 'nullable|string|max:20',
            'office_in_time' => 'nullable|date_format:H:i',
            'office_out_time' => 'nullable|date_format:H:i',
            'late_time' => 'nullable|integer|min:0',
            'reporting_manager_id' => 'nullable|integer|exists:people,id',
            'second_reporting_manager_id' => 'nullable|integer|exists:people,id',

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

            // === Banking & Tax ===
            'bank_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc_code' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'tax_status' => 'nullable|in:Resident,Non-Resident',
            'social_security_no' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:20',
            'is_tax_deduction' => 'nullable|in:0,1',
            'is_salary_stop' => 'nullable|in:0,1',

            'office_time' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'person_id.required' => 'Please select a person.',
            'person_id.exists' => 'Selected person does not exist.',
            'department_id.required' => 'Department is required.',
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'This Employee ID is already in use.',
            // 'employee_code.required' => 'Employee Code is required.',
            // 'employee_code.unique' => 'This Employee Code is already in use.',
            // 'basic_salary.required' => 'Basic salary is required.',
        ];
    }
}
