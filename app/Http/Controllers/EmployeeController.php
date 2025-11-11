<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Division;
use App\Models\Department;
use App\Models\Designation;
use App\Models\InfoMaster;
use Illuminate\Support\Str;
use Auth;

class EmployeeController extends Controller
{
     public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $employees = Employee::paginate($perPage);

        return Inertia::render('employee/index', [
            'employees' => $employees
        ]);
    }
    /**
     * Show the create employee form
     */
    public function create()
    {
        $companies = Company::select('id', 'company_name')->get();
        $divisions = Division::select('id', 'division_name')->get();
        $departments = Department::select('department_id', 'department_name')->get();
        $designations = InfoMaster::select('id', 'name')->get();

        return Inertia::render('employee/create', [
            'companies' => $companies,
            'divisions' => $divisions,
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }

    /**
     * Store a new employee
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'person_id' => 'required|integer',
            'person_name' => 'required|string|max:150',
            'employee_code' => 'required|string|max:20|unique:employees',
            'employee_id' => 'required|string|max:20|unique:employees',
            'company_id' => 'nullable|exists:companies,id',
            'division_id' => 'nullable|exists:divisions,id',
            'department_id' => 'required|exists:departments,department_id',
            'designation_id' => 'required|exists:designations,designation_id',
            'joining_date' => 'required|date',
            'employment_type' => 'required|in:Permanent,Contract,Intern,Probation,Freelancer',
            // You can add other optional fields here if needed
        ]);

        // Create employee
        $employee = new Employee();
        $employee->person_id = $validated['person_id'];
        $employee->person_name = $validated['person_name'];
        $employee->employee_code = $validated['employee_code'];
        $employee->employee_id = $validated['employee_id'];
        $employee->company_id = $validated['company_id'] ?? null;
        $employee->company_name = $validated['company_id'] ? Company::find($validated['company_id'])->company_name : null;
        $employee->division_id = $validated['division_id'] ?? null;
        $employee->division_name = $validated['division_id'] ? Division::find($validated['division_id'])->division_name : null;
        $employee->department_id = $validated['department_id'];
        $employee->department_name = Department::find($validated['department_id'])->department_name;
        $employee->designation_id = $validated['designation_id'];
        $employee->designation_name = Designation::find($validated['designation_id'])->designation_name;
        $employee->joining_date = $validated['joining_date'];
        $employee->employment_type = $validated['employment_type'];
        $employee->created_by = Auth::id();

        // Optional fields (fill if request has them)
        $optionalFields = [
            'confirmation_date', 'probation_end_date', 'effective_date', 'work_location', 'shift',
            'official_email', 'official_phone', 'office_in_time', 'office_out_time', 'late_time',
            'employee_status', 'gross_salary', 'basic_salary', 'house_rent_allowance', 'medical_allowance',
            'transport_allowance', 'other_allowances', 'overtime_rate', 'total_salary', 'currency',
            'bank_name', 'bank_account_no', 'bank_ifsc_code', 'pan_number', 'tax_status', 'social_security_no',
            'passport_number', 'is_tax_dedction', 'is_salary_stop', 'emergency_contact_name', 'emergency_contact_phone',
            'marital_status', 'gender', 'blood_group', 'work_experience', 'skills',
            'reporting_manager_id', 'reporting_manager_name', 'second_reporting_manager_id', 'second_reporting_manager_name',
            'deparment_head', 'deparment_head_name', 'last_appraisal_date', 'next_appraisal_date',
            'last_promotion_date', 'next_promotion_due', 'office_time'
        ];

        foreach ($optionalFields as $field) {
            if ($request->has($field)) {
                $employee->$field = $request->$field;
            }
        }

        $employee->save();

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }
}
