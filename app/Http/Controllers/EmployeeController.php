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
use App\Http\Requests\StoreEmployeeRequest;

class EmployeeController extends Controller
{
    public function index()
    {
        return Inertia::render('employee/index', [
            'employees' => Employee::with(['person', 'department', 'designation'])->Orderby('id','desc')->paginate(10),
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
public function store(StoreEmployeeRequest $request)
{
    $data = $request->validated();

    // Map display/autocomplete fields
    $data['department_head_id'] = $data['department_head'] ?? null;
    $data['reporting_manager_id'] = $data['reporting_manager_id'] ?? null;
    $data['second_reporting_manager_id'] = $data['second_reporting_manager_id'] ?? null;

    unset(
        $data['department_head'],
        $data['department_head_name'],
        $data['reporting_manager_name'],
        $data['second_reporting_manager_name']
    );

    // Salary calculation
    $toFloat = fn($v) => is_numeric($v) ? (float) $v : 0.0;
    $gross = $toFloat($data['basic_salary'])
           + $toFloat($data['house_rent_allowance'] ?? 0)
           + $toFloat($data['medical_allowance'] ?? 0)
           + $toFloat($data['transport_allowance'] ?? 0)
           + $toFloat($data['other_allowances'] ?? 0);

    $data['gross_salary'] = $gross;
    $data['total_salary'] = $gross + $toFloat($data['overtime_rate'] ?? 0);

    // Boolean fields
    $data['is_tax_deduction'] = !empty($data['is_tax_deduction']);
    $data['is_salary_stop']   = !empty($data['is_salary_stop']);

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
public function update(StoreEmployeeRequest $request, Employee $employee)
{
    $data = $request->validated();

    $data['department_head_id'] = $data['department_head'] ?? null;
    $data['reporting_manager_id'] = $data['reporting_manager_id'] ?? null;
    $data['second_reporting_manager_id'] = $data['second_reporting_manager_id'] ?? null;

    unset(
        $data['department_head'],
        $data['department_head_name'],
        $data['reporting_manager_name'],
        $data['second_reporting_manager_name']
    );

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