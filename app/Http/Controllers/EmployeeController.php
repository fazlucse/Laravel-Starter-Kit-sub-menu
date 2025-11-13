<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Division;
use App\Models\Department;
use App\Models\InfoMaster;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        return Inertia::render('employee/index', [
            'employees' => Employee::with(['person', 'department', 'designation'])->paginate(10),
            'flash' => ['success' => session('success')],
        ]);
    }

    public function create()
    {
        return Inertia::render('employee/create', [
            'companies'    => Company::select('id', 'company_name')->where('type', 'company')->get(),
            'finCompany'   => Company::select('id', 'company_name')-> where('type', 'fin_company')->get(),
            'divisions'    => Division::select('id', 'division_name')->get(),
            'departments'  => Department::select('id', 'department_name')->get(),
            'designations' => InfoMaster::select('id', 'name')->where('type', 'desgination')->get(),
            'mode'         => 'create',
        ]);
    }

    /**
     * STORE - Manual Validation (No StoreEmployeeRequest)
     */
    public function store(Request $request): RedirectResponse
    {
        // === 1. MANUAL VALIDATION ===
        $validator = Validator::make($request->all(), [
            // Personal
            'person_id'       => 'required|integer|min:1|exists:people,id',
            'person_name' => 'required|string|max:255',
            'gender'          => 'nullable|in:Male,Female,Other',
            'marital_status'  => 'nullable|in:Single,Married,Divorced,Widowed',
            'blood_group'     => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'work_experience' => 'nullable|integer|min:0',
            'skills'          => 'nullable|string',

            // Company
            'company_id' => 'nullable|integer|exists:companies,id',
            'company_name' => 'nullable|string|max:255',
            'fin_com_id' => 'nullable|integer|exists:companies,id',
            'fin_com_name' => 'nullable|string|max:255',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'division_name' => 'nullable|string|max:255',
            'department_id' => 'nullable|integer|min:1|exists:departments,id',
            'department_name' => 'nullable|string|max:255',
            'designation_id' => 'nullable|integer|min:1|exists:info_master,id',
            'designation_name' => 'nullable|string|max:255',
            'department_head' => 'nullable|integer|exists:people,id',
            'department_head_name' => 'nullable|string|max:150',

            // Employment
            'employee_id'     => 'required|string|max:20|unique:employees,employee_id',
            'employee_code'   => 'nullable|string|max:20|unique:employees,employee_code',
            'joining_date'    => 'required|date',
            'confirmation_date' => 'nullable|date|after_or_equal:joining_date',
            'probation_end_date' => 'nullable|date|after_or_equal:joining_date',
            'effective_date'  => 'nullable|date',
            'employment_type' => 'required|in:Permanent,Contract,Intern,Probation,Freelancer',
            'employee_status' => 'nullable|in:Active,Inactive,Terminated,Resigned,Retired,On Leave',
            'work_location'   => 'nullable|string|max:100',
            'shift'           => 'nullable|string|max:50',
            'official_email'  => 'nullable|email|max:100',
            'official_phone'  => 'nullable|string|max:20',
            'office_in_time'  => 'nullable|date_format:H:i',
            'office_out_time' => 'nullable|date_format:H:i',
            'late_time'       => 'nullable|integer|min:0',
            'reporting_manager_id' => 'nullable|integer|exists:people,id',
            'reporting_manager_name' => 'nullable|string|max:250',
            'second_reporting_manager_id' => 'nullable|integer|exists:people,id',
            'second_reporting_manager_name' =>  'nullable|string|max:250',

            // Emergency
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',

            // Performance
            'last_appraisal_date' => 'nullable|date',
            'next_appraisal_date' => 'nullable|date|after:last_appraisal_date',
            'last_promotion_date' => 'nullable|date',
            'next_promotion_due'  => 'nullable|date|after:last_promotion_date',
            'office_time'         => 'nullable|string',

            // Salary
            'currency'            => 'nullable|in:USD,EUR,GBP,BDT,INR',
            'basic_salary'        => 'required|numeric|min:0',
            'house_rent_allowance'=> 'nullable|numeric|min:0',
            'medical_allowance'   => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'other_allowances'    => 'nullable|numeric|min:0',
            'overtime_rate'       => 'nullable|numeric|min:0',

            // Banking & Tax
            'bank_name'           => 'nullable|string|max:255',
            'bank_account_no'     => 'nullable|string|max:50',
            'bank_ifsc_code'      => 'nullable|string|max:20',
            'pan_number'          => 'nullable|string|max:20',
            'tax_status'          => 'nullable|in:Resident,Non-Resident',
            'social_security_no'  => 'nullable|string|max:50',
            'passport_number'     => 'nullable|string|max:20',
            'is_tax_deduction'    => 'nullable|in:0,1',
            'is_salary_stop'      => 'nullable|in:0,1',
        ], [
            'person_id.required' => 'Please select a person.',
            'employee_id.unique' => 'Employee ID already exists.',
            'department_id.required' => 'Department is required.',
            'designation_id.required' => 'Designation is required.',
            'basic_salary.required' => 'Basic salary is required.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // === 2. MAP AUTOCOMPLETE FIELDS ===
        $data['department_head_id']          = $data['department_head'] ?? null;
        $data['reporting_manager_id']        = $data['reporting_manager_id'] ?? null;
        $data['second_reporting_manager_id'] = $data['second_reporting_manager_id'] ?? null;

        // Remove display names
        unset(
            $data['department_head'],
            $data['department_head_name'] ,
            $data['reporting_manager_name'] ,
            $data['second_reporting_manager_name'] 
        );

        // === 3. SALARY CALCULATION ===
        $toFloat = fn($v) => is_numeric($v) ? (float) $v : 0.0;

        $gross = $toFloat($data['basic_salary'])
               + $toFloat($data['house_rent_allowance'] ?? 0)
               + $toFloat($data['medical_allowance'] ?? 0)
               + $toFloat($data['transport_allowance'] ?? 0)
               + $toFloat($data['other_allowances'] ?? 0);

        $data['gross_salary'] = $gross;
        $data['total_salary'] = $gross + $toFloat($data['overtime_rate'] ?? 0);

        // === 4. BOOLEAN FIELDS ===
        $data['is_tax_deduction'] = !empty($data['is_tax_deduction']);
        $data['is_salary_stop']   = !empty($data['is_salary_stop']);

        // === 5. CREATE EMPLOYEE ===
        Employee::create($data);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        $employee->load([
            'person:id,name,photo',
            'departmentHead:id,name',
            'reportingManager:id,name',
            'secondReportingManager:id,name',
        ]);

        return Inertia::render('employee/create', [
            'employee'     => $employee,
            'companies'    => Company::select('id', 'company_name')->where('type', 'company')->get(),
            'finCompany'   => Company::select('id', 'company_name')->where('type', 'fin_company')->get(),
            'divisions'    => Division::select('id', 'division_name')->get(),
            'departments'  => Department::select('id', 'department_name')->get(),
            'designations' => InfoMaster::select('id', 'name')->where('type', 'desgination')->get(),
            'mode'         => 'edit',
        ]);
    }

    /**
     * UPDATE - Manual Validation (No UpdateEmployeeRequest)
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'person_id'       => 'required|integer|min:1|exists:people,id',
            'employee_id'     => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'employee_code'   => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'joining_date'    => 'required|date',
            'employment_type' => 'required|in:Permanent,Contract,Intern,Probation,Freelancer',
            'department_id'   => 'required|integer|min:1|exists:departments,id',
            'designation_id'  => 'required|integer|min:1|exists:info_master,id',
            'department_head' => 'nullable|integer|exists:people,id',
            'basic_salary'    => 'required|numeric|min:0',
            'is_tax_deduction'=> 'nullable|in:0,1',
            'is_salary_stop'  => 'nullable|in:0,1',
            // Add other fields as needed
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Map autocomplete
        $data['department_head_id']          = $data['department_head'] ?? null;
        $data['reporting_manager_id']        = $data['reporting_manager_id'] ?? null;
        $data['second_reporting_manager_id'] = $data['second_reporting_manager_id'] ?? null;

        unset(
            $data['department_head'],
            $data['department_head_name'] ,
            $data['reporting_manager_name'] ,
            $data['second_reporting_manager_name']
        );

        // Salary
        $toFloat = fn($v) => is_numeric($v) ? (float) $v : 0.0;
        $gross = $toFloat($data['basic_salary'])
               + $toFloat($data['house_rent_allowance'] ?? 0)
               + $toFloat($data['medical_allowance'] ?? 0)
               + $toFloat($data['transport_allowance'] ?? 0)
               + $toFloat($data['other_allowances'] ?? 0);

        $data['gross_salary'] = $gross;
        $data['total_salary'] = $gross + $toFloat($data['overtime_rate'] ?? 0);

        $data['is_tax_deduction'] = !empty($data['is_tax_deduction']);
        $data['is_salary_stop']   = !empty($data['is_salary_stop']);

        $employee->update($data);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }
}