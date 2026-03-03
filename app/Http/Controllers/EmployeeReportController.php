<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\InfoMaster;
use App\Models\Office;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployeeReportController extends Controller
{
    /**
     * Display the report filter page.
     */
    public function index()
    {
        return Inertia::render('Reports/EmployeeReport', [
            'offices'    => Company::select('id', 'name as company_name as name')->where('type', 'company')->get(),
            'finCompany'   => Company::select('id', 'name as company_name as name')-> where('type', 'fin_company')->get(),
            'divisions'    => Division::select('id', 'division_name as name')->get(),
            'departments'  => Department::select('id', 'department_name as name ')->get(),
            'designations' => InfoMaster::select('id', 'name')->where('type', 'desgination')->get(),
            'employmentStatuses' => ['Active', 'Probation', 'Resigned', 'Terminated', 'On Leave'],
        ]);
    }

    /**
     * Handle the AJAX request to generate report data.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'office' => 'nullable|string',
            'division' => 'nullable|string',
            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'status' => 'nullable|string',
            'gender' => 'nullable|string',
            'search' => 'nullable|string',
            'joined_from' => 'nullable|date',
            'joined_to' => 'nullable|date|after_or_equal:joined_from',
            'confirmed_from' => 'nullable|date',
            'confirmed_to' => 'nullable|date|after_or_equal:confirmed_from',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        try {
            $query = Employee::query();

            // 1. Organizational Filters
            $query->when($request->office, function ($q, $office) {
                return $q->where('company_name', $office);
            })
                ->when($request->division, function ($q, $division) {
                    return $q->where('division_name', $division);
                })
                ->when($request->department, function ($q, $dept) {
                    return $q->where('department_name', $dept);
                })
                ->when($request->designation, function ($q, $desig) {
                    return $q->where('designation_name', $desig);
                });

            // 2. Status & Gender
            $query->when($request->status, function ($q, $status) {
                return $q->where('employee_status', $status);
            })
                ->when($request->gender, function ($q, $gender) {
                    return $q->where('gender', $gender);
                });

            // 3. Search (Name, ID, Code)
            $query->when($request->search, function ($q, $search) {
                return $q->where(function($sub) use ($search) {
                    $sub->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('person_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            });

            // 4. Date Ranges
            $query->when($request->joined_from, fn($q, $date) => $q->whereDate('joining_date', '>=', $date))
                ->when($request->joined_to, fn($q, $date) => $q->whereDate('joining_date', '<=', $date))
                ->when($request->confirmed_from, fn($q, $date) => $q->whereDate('confirmation_date', '>=', $date))
                ->when($request->confirmed_to, fn($q, $date) => $q->whereDate('confirmation_date', '<=', $date))
                ->when($request->effective_from, fn($q, $date) => $q->whereDate('effective_date', '>=', $date))
                ->when($request->effective_to, fn($q, $date) => $q->whereDate('effective_date', '<=', $date));

            // 5. Execute and Map Data (Including ALL requested columns)
            $reportData = $query->orderBy('employee_id', 'asc')
                ->get()
                ->map(function ($emp) {
                    return [
                        'id' => $emp->id,
                        'employee_id' => $emp->employee_id,
                        'employee_code' => $emp->employee_code,
                        'person_name' => $emp->person_name,
                        'gender' => $emp->gender,
                        'dob' => $emp->dob, // Birthday

                        // Employment Info
                        'designation_name' => $emp->designation_name,
                        'department_name' => $emp->department_name,
                        'division_name' => $emp->division_name,
                        'company_name' => $emp->company_name,
                        'employee_status' => $emp->employee_status,

                        // Contact Info
                        'official_email' => $emp->official_email,
                        'official_phone' => $emp->official_phone,

                        // Management Hierarchy
                        'reporting_manager_name' => $emp->reporting_manager_name,
                        'department_head_name' => $emp->department_head_name,

                        // Vitals & Skills
                        'blood_group' => $emp->blood_group,
                        'skills' => $emp->skills,

                        // Office Timing
                        'office_in_time' => $emp->office_in_time ? date('h:i A', strtotime($emp->office_in_time)) : '---',
                        'office_out_time' => $emp->office_out_time ? date('h:i A', strtotime($emp->office_out_time)) : '---',
                        'late_time' => $emp->late_time,

                        // Dates
                        'joining_date' => $emp->joining_date,
                        'confirmed_date' => $emp->confirmation_date,
                        'effective_date' => $emp->effective_date,

                        // Payroll Flags
                        'is_salary_stop' => (bool)$emp->is_salary_stop,
                        'is_tax_deduction' => (bool)$emp->is_tax_deduction,

                        // Photo Placeholder (Link this to your storage if available)
                        'photo' => $emp->photo_url ?? null,
                    ];
                });

            return response()->json(['reportData' => $reportData, 'status' => 'success'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['server' => ['An error occurred: ' . $e->getMessage()]]
            ], 500);
        }
    }
}
