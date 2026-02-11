<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Traits\LogsActions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\AttendanceImport;
use ZipArchive;
class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $perPage = (int) $request->query('per_page', 10);
    $search = $request->input('search');

    $query = Attendance::with(['employee']); // eager load employee relationship

    // Filters
    $filters = $request->only(['emp_name', 'employee_id', 'department_name', 'designation_name']);

    foreach ($filters as $field => $value) {
        if (!empty($value)) {
            $query->where($field, 'like', "%{$value}%");
        }
    }

    // Optional global search
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('emp_name', 'like', "%{$search}%")
              ->orWhere('employee_id', 'like', "%{$search}%")
              ->orWhereHas('employee', function ($q2) use ($search) {
                  $q2->where('person_name', 'like', "%{$search}%");
              });
        });
    }

    $attendance = $query->orderByDesc('add_time')
                        ->paginate($perPage)
                        ->appends($request->query());

    return Inertia::render('attendance/index', [
        'attendance' => $attendance,
        'perPage' => $perPage,
        'defaultPerPage' => 15,
        'search' => $search,
        'filters' => $filters,
        'flash' => [
            'success' => session('success'),
        ],
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::
            orderBy('person_name')
            ->get();
        return Inertia::render('attendance/create', [
            'mode'         => 'create',
            'employees' => $employees
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Handle Excel Upload
        if ($request->query('type') === 'excel') {

//            if ($request->hasFile('file')) {
//                dd($request->file('file')->getClientOriginalName());
//            }
            // 2. Validate the file type and size
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Limit to 10MB
            ]);

            try {

                // 3. Use a Transaction for data safety
                DB::beginTransaction();
                // 4. Read and process the Excel file using your Import class
                Excel::import(new AttendanceImport, $request->file('file'));

                DB::commit();
                LogsActions::logCreate(new Attendance(), 'Bulk Attendance Excel Import performed.');
                return redirect()->route('attendance.index')
                    ->with('success', 'Attendance data imported and updated successfully!');

            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                DB::rollBack();
                $failures = $e->failures();
                // Return specific Excel row validation errors to the frontend
                return back()->withErrors(['file' => 'Row ' . $failures[0]->row() . ': ' . $failures[0]->errors()[0]]);

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
            }
        }
        $data = $request->all();
        $employee = Employee::with('person')
            ->where('person_id', $data['employee_id'])
            ->where('employee_status', 'Active')
            ->first();
        if (!$employee) {
            return back()->withErrors(['employee_id' => 'The selected employee is inactive or does not exist.']);
        }
        $validated = $request->validate([
            'employee_id' => 'required',
            'date'        => 'required|date',
            'in_time'     => 'required',
            'out_time'    => 'nullable',
        ]);

       $attendance =  Attendance::updateOrCreate(
            [
                'contact_id' => $validated['employee_id'],
                'add_time'    => $validated['date'], // Unique constraint check
            ],
            [
                'contact_id'  => $employee->person_id??0,
                'employee_id'  => $employee->employee_id??'',
                'emp_name'  => $employee->person_name??'',
                'designation'  => $employee->designation_id??0,
                'designation_name'  => $employee->designation_name??'',
                'operational_office'  => $employee->company_id??0,
                'operational_office_name'  => $employee->company_name??'',
                'department'  => $employee->department_id??0,
                'department_name'  => $employee->department_name??'',
                'office_in_time'  => $employee->office_in_time??'',
                'office_out_time'  => $employee->office_out_time??'',
                'division'  => $employee->division_id??0,
                'division_name'  => $employee->division_name??'',
                'emp_in_time'  => $validated['in_time'],
                'emp_out_time' => $validated['out_time'],
                'is_manual'    => true,
                'addby'        => auth()->id(),
                'editby'       => auth()->id(),
                'edit_time'    => now(),
            ]
        );
        LogsActions::logCreate($attendance, "Manual attendance recorded for {$employee->person_name} on {$validated['date']}");
        return redirect()->route('attendance.index')->with('success', 'Attendance updated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        try {
            $message = "Attendance deleted for {$attendance->emp_name} (ID: {$attendance->employee_id}) for date {$attendance->add_time}";
            LogsActions::logDelete($attendance, $message);
            $attendance->delete();
            return redirect()->back()->with('success', 'Attendance record deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete record: ' . $e->getMessage());
        }
    }
}
