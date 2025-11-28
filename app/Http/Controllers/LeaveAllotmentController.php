<?php

namespace App\Http\Controllers;

use App\Models\LeaveAllotment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveAllotmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);

        $leaveAllotments = LeaveAllotment::with('employee')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        return Inertia::render('leave-allotment/index', [
            'leaveAllotment' => $leaveAllotments,
        ]);
    }

    // Show create form
    public function create()
    {
        $employees = Employee::select('id', 'person_name')->get();
        return Inertia::render('leave-allotment/create', [
            'employees' => $employees,
            'mode'         => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'year'        => 'nullable|integer',
            'remarks'     => 'nullable|string|max:255',
        ]);

        $year = $data['year'] ?? date('Y');
        if ($data['employee_id']) {
            $employee = Employee::find($data['employee_id']);
            if ($employee) {
                LeaveAllotment::distributeLeave($employee, $year);
            }
        } else {
            $employees = Employee::all();
            foreach ($employees as $employee) {
                LeaveAllotment::distributeLeave($employee, $year);
            }
        }
   return redirect()->route('leave_allotments.index')
                 ->with('success', 'Leave distributed successfully for ' . $year);
    }

    // Show edit form
    public function edit(LeaveAllotment $leaveAllotment)
    {
        $employees = Employee::select('id', 'person_name')->get();

        return Inertia::render('LeaveAllotment/Edit', [
            'leaveAllotment' => $leaveAllotment,
            'employees' => $employees,
             'mode'         => 'edit',
        ]);
    }

    // Update leave allotment
    public function update(Request $request, LeaveAllotment $leaveAllotment)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|string|max:20',
            'annual_allotment' => 'nullable|numeric',
            'casual_allotment' => 'nullable|numeric',
            'sick_allotment' => 'nullable|numeric',
            'maternal_allotment' => 'nullable|numeric',
            'paternal_allotment' => 'nullable|numeric',
            'earn_allotment' => 'nullable|numeric',
            'lwp_allotment' => 'nullable|numeric',
            'remarks' => 'nullable|string|max:255',
        ]);

        $data['updated_by'] = Auth::id();

        $leaveAllotment->update($data);

        return redirect()->route('leave-allotment.index')
            ->with('success', 'Leave allotment updated successfully.');
    }

    // Delete leave allotment
    public function destroy(LeaveAllotment $leaveAllotment)
    {
        $leaveAllotment->delete();

        return redirect()->route('leave-allotment.index')
            ->with('success', 'Leave allotment deleted successfully.');
    }
}
