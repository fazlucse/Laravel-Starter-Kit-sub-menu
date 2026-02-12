<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $leaveRequests = LeaveRequest::with('employee')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        $leaveRequests->getCollection()->transform(function ($leave) {
            $leave->total_utilized = $leave->al_leave + $leave->cl_leave + $leave->sl_leave + $leave->pat_leave + $leave->mat_leave + $leave->others_emp_leave + $leave->lwp_leave;
            $leave->remaining_al = $leave->balance_al;
            $leave->remaining_cl = $leave->balance_cl;
            $leave->remaining_sl = $leave->balance_sl;
            $leave->remaining_pat = $leave->balance_pat;
            $leave->remaining_mat = $leave->balance_mat;

            return $leave;
        });

        return Inertia::render('leave-request/index', [
            'leaveRequests' => $leaveRequests,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('leave-request/create', [
            'mode' => 'create'
            // pass any data you need for the page
            // Example:
            // 'offices' => Office::all(),
            // 'divisions' => Division::all(),
            // 'employees' => Employee::all(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Parent Table Fields
            'emergency_contact_name' => 'nullable|string|max:255',
            'reliever_id' => 'required',
            'remarks' => 'nullable|string',
            // Child Table Fields (The List)
            'leaves' => 'required|array|min:1',
            'leaves.*.request_for' => 'required|in:self,other',
//            'leaves.*.person_id' => 'required',
//            'leaves.*.from_date' => 'required|date',
//            'leaves.*.to_date' => 'required|date|after_or_equal:leaves.*.from_date',
            'leaves.*.leave_duration' => 'required|string',
            'leaves.*.leave_reason' => 'required|string',
            'leaves.*.total_days' => 'required|integer|min:.5',
        ]);
        return DB::transaction(function () use ($request) {
            $leaves = $request->leaves;
            $first = $leaves[0];
            $personId = ($first['request_for'] === 'self') ? Auth::id() : $first['person_id'];
            $employee = Employee::where('person_id', $personId)->firstOrFail();
            $totals = ['annual' => 0, 'casual' => 0, 'sick' => 0, 'paternity' => 0, 'maternity' => 0];
            foreach ($leaves as $item) {
                if (isset($totals[$item['leave_reason']])) {
                    $totals[$item['leave_reason']] += (float)$item['total_days'];
                }
            }
            $parent = LeaveRequest::create([
                'year' => now()->year,
                'office' => $employee->office_id,
                'person_id' => $personId,
                'person_name' => $employee->full_name,
                'employee_id' => $employee->employee_code,
                'on_behalf_request' => ($first['request_for'] === 'other') ? 'Yes' : 'No',
                'al_leave' => $totals['annual'],
                'cl_leave' => $totals['casual'],
                'sl_leave' => $totals['sick'],
                'pat_leave' => $totals['paternity'],
                'mat_leave' => $totals['maternity'],
                'total_leave' => array_sum($totals),
                'remarks' => $request->remarks,
                'reliever_employee' => $request->reliever_employee,
                'leave_type' => count(array_filter($totals)) > 1 ? 'Mixed' : $first['leave_reason'],
            ]);
            // 4. Create Detail Records
            foreach ($leaves as $item) {
                LeaveRequestDetail::create([
                    'leave_request_id' => $parent->id,
                    'from_date' => $item['from_date'],
                    'to_date' => $item['to_date'],
                    'leave_reason' => $item['leave_reason'],
                    'total_days' => $item['total_days'],
                    'leave_duration' => $item['leave_duration'],
                ]);
            }

            return redirect()->back()->with('success', 'Leave Request Processed!');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
