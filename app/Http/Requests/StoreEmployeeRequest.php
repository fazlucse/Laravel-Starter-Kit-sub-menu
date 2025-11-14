<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust authorization if needed
    }

    public function rules(): array
    {
        // Detect current employee ID for update (null if creating)
        $employeeId = $this->route('employee')?->id ?? null;

        return [
            // === Personal ===
            'person_id'       => 'required|integer|min:1|exists:people,id',
            'person_name'     => 'required|string|max:255',
            'gender'          => 'nullable|in:Male,Female,Other',
            'marital_status'  => 'nullable|in:Single,Married,Divorced,Widowed',
            'blood_group'     => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'work_experience' => 'nullable|integer|min:0',
            'skills'          => 'nullable|string',

            // === Company ===
            'company_id'      => 'nullable|integer|exists:companies,id',
            'company_name'    => 'nullable|string|max:255',
            'fin_com_id'      => 'nullable|integer|exists:companies,id',
            'fin_com_name'    => 'nullable|string|max:255',
            'division_id'     => 'nullable|integer|exists:divisions,id',
            'division_name'   => 'nullable|string|max:255',
            'department_id'   => 'nullable|integer|min:1|exists:departments,id',
            'department_name' => 'nullable|string|max:255',
            'designation_id'  => 'nullable|integer|min:1|exists:info_master,id',
            'designation_name'=> 'nullable|string|max:255',
            'department_head' => 'nullable|integer|exists:people,id',
            'department_head_name' => 'nullable|string|max:150',

            // === Employment ===
            'employee_id' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'employee_id')->ignore($employeeId),
            ],
            'employee_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'employee_code')->ignore($employeeId),
            ],
            'joining_date'     => 'required|date',
            'confirmation_date'=> 'nullable|date|after_or_equal:joining_date',
            'probation_end_date'=> 'nullable|date|after_or_equal:joining_date',
            'effective_date'   => 'nullable|date',
            'employment_type'  => 'required|in:Permanent,Contract,Intern,Probation,Freelancer',
            'employee_status'  => 'nullable|in:Active,Inactive,Terminated,Resigned,Retired,On Leave',
            'work_location'    => 'nullable|string|max:100',
            'shift'            => 'nullable|string|max:50',
            'official_email'   => 'nullable|email|max:100',
            'official_phone'   => 'nullable|string|max:20',
            'office_in_time'   => 'nullable',
            'office_out_time'  => 'nullable',
            'late_time'        => 'nullable|integer|min:0',
            'reporting_manager_id'        => 'nullable|integer|exists:people,id',
            'reporting_manager_name'      => 'nullable|string|max:250',
            'second_reporting_manager_id' => 'nullable|integer|exists:people,id',
            'second_reporting_manager_name'=> 'nullable|string|max:250',

            // === Emergency ===
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',

            // === Performance ===
            'last_appraisal_date' => 'nullable|date',
            'next_appraisal_date' => 'nullable|date|after:last_appraisal_date',
            'last_promotion_date' => 'nullable|date',
            'next_promotion_due'  => 'nullable|date|after:last_promotion_date',
            'office_time'         => 'nullable|string',

            // === Salary ===
            'currency'             => 'nullable|in:USD,EUR,GBP,BDT,INR',
            'basic_salary'         => 'required|numeric|min:0',
            'house_rent_allowance' => 'nullable|numeric|min:0',
            'medical_allowance'    => 'nullable|numeric|min:0',
            'transport_allowance'  => 'nullable|numeric|min:0',
            'other_allowances'     => 'nullable|numeric|min:0',
            'overtime_rate'        => 'nullable|numeric|min:0',

            // === Banking & Tax ===
            'bank_name'           => 'nullable|string|max:255',
            'bank_account_no'     => 'nullable|string|max:50',
            'bank_ifsc_code'      => 'nullable|string|max:20',
            'pan_number'          => 'nullable|string|max:20',
            'tax_status'          => 'nullable|in:Resident,Non-Resident',
            'social_security_no'  => 'nullable|string|max:50',
            'passport_number'     => 'nullable|string|max:20',
            'is_tax_deduction'    => 'nullable|in:0,1',
            'is_salary_stop'      => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'person_id.required'       => 'Please select a person.',
            'employee_id.unique'       => 'Employee ID already exists.',
            'department_id.required'   => 'Department is required.',
            'designation_id.required'  => 'Designation is required.',
            'basic_salary.required'    => 'Basic salary is required.',
        ];
    }
}
