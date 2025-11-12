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
public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        // Auto-calculate if not provided
        $data['gross_salary'] = $data['gross_salary'] ?? (
            $data['basic_salary'] +
            ($data['house_rent_allowance'] ?? 0) +
            ($data['medical_allowance'] ?? 0) +
            ($data['transport_allowance'] ?? 0) +
            ($data['other_allowances'] ?? 0)
        );

        $data['total_salary'] = $data['total_salary'] ?? ($data['gross_salary'] + ($data['overtime_rate'] ?? 0));

        Employee::create($data);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }
    public function edit(Employee $employee)
    {
        return Inertia::render('Employees/Form', [
            'employee' => $employee->toArray(),
            'mode' => 'edit',
        ]);
    }
    public function update(StoreEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        $data['gross_salary'] = $data['gross_salary'] ?? (
            $data['basic_salary'] +
            ($data['house_rent_allowance'] ?? 0) +
            ($data['medical_allowance'] ?? 0) +
            ($data['transport_allowance'] ?? 0) +
            ($data['other_allowances'] ?? 0)
        );

        $data['total_salary'] = $data['total_salary'] ?? ($data['gross_salary'] + ($data['overtime_rate'] ?? 0));

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }
}
