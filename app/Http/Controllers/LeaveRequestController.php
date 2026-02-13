<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetail;
use App\Traits\LogsActions;
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
            ->visibleTo(auth()->user())
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        // Transform calculations
        $leaveRequests->getCollection()->transform(function ($leave) {
            $leave->total_utilized =
                ($leave->al_leave ?? 0) +
                ($leave->cl_leave ?? 0) +
                ($leave->sl_leave ?? 0) +
                ($leave->pat_leave ?? 0) +
                ($leave->mat_leave ?? 0) +
                ($leave->others_emp_leave ?? 0) +
                ($leave->lwp_leave ?? 0);
            $leave->remaining_al  = $leave->balance_al ?? 0;
            $leave->remaining_cl  = $leave->balance_cl ?? 0;
            $leave->remaining_sl  = $leave->balance_sl ?? 0;
            $leave->remaining_pat = $leave->balance_pat ?? 0;
            $leave->remaining_mat = $leave->balance_mat ?? 0;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'emergency_contact_name' => 'nullable|string|max:255',
            'reliever_id' => 'required',
            'remarks' => 'nullable|string',
            'leaves' => 'required|array|min:1',
            'leaves.*.request_for' => 'required|in:self,other',
            'leaves.*.leave_duration' => 'required|string',
            'leaves.*.leave_reason' => 'required|string',
            'leaves.*.total_days' => 'required|numeric|min:0.5|multiple_of:0.5',
        ]);

        return DB::transaction(function () use ($request) {
            $leaves = $request->leaves;
            $first = $leaves[0];
            $personId = ($first['request_for'] === 'self') ? Auth::id() : $first['person_id'];

            foreach ($leaves as $item) {
                $overlap = LeaveRequestDetail::whereHas('leaveRequest', function ($query) use ($personId) {
                    $query->where('person_id', $personId)
                        ->whereNotIn('status', [22, 99]); // 22=Rejected, 99=Cancelled (Adjust based on your constants)
                })
                    ->where(function ($query) use ($item) {
                        $query->where('from_date', '<=', $item['to_date'])
                            ->where('to_date', '>=', $item['from_date']);
                    })
                    ->exists();

                if ($overlap) {
                    $msg = "Leave already applied for the selected dates ({$item['from_date']} to {$item['to_date']}).";
                    return redirect()->back()
                        ->with('error', $msg)
                        ->withErrors(['leaves' => $msg]) // Keeps the red border on the input
                        ->withInput();
                }
            }

            // --- 2. DATA PROCESSING ---
            $employee = Employee::where('person_id', $personId)->firstOrFail();
            $totals = ['annual' => 0, 'casual' => 0, 'sick' => 0, 'paternity' => 0, 'maternity' => 0];

            foreach ($leaves as $item) {
                if (isset($totals[$item['leave_reason']])) {
                    $totals[$item['leave_reason']] += (float)$item['total_days'];
                }
            }

            // --- 3. CREATE PARENT RECORD ---
            $parent = LeaveRequest::create([
                'year' => now()->year,
                'office' => $employee->company_id,
                'person_id' => $personId,
                'person_name' => $employee->person_name,
                'employee_id' => $employee->id,
                'department_name' => $employee->department_name,
                'division_name' => $employee->division_name,
                'division_id' => $employee->division_id,
                'department_id' => $employee->department_id,
                'inline_supervisor_id' => $employee->reporting_manager_id,
                'inline_supervisor_name' => $employee->reporting_manager_name,
                'inline_supervisor_designation' => '',
                'department_head' =>  $employee->department_head,
                'department_head_name' =>  $employee->department_head_name,
                'on_behalf_request' => ($first['request_for'] === 'other') ? 'Yes' : 'No',
                'al_leave' => $totals['annual'],
                'cl_leave' => $totals['casual'],
                'sl_leave' => $totals['sick'],
                'pat_leave' => $totals['paternity'],
                'mat_leave' => $totals['maternity'],
                'total_leave' => array_sum($totals),
                'remarks' => $request->remarks,
                'reliever_employee' => $request->reliever_employee,
                'reliever_id' => $request->reliever_id,
                'leave_type' => count(array_filter($totals)) > 1 ? 'Mixed' : $first['leave_reason'],
                'status' => 0, // Ensure it starts as Pending
            ]);

            // --- 4. CREATE DETAIL RECORDS ---
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

            LogsActions::logCreate($parent, 'Leave submitted successfully.');
            return redirect()->route('leave_requests.index')
                ->with('success',  'Leave Request Processed!');
        });
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store2(Request $request)
    {
        $data = $request->validate([
            // Parent Table Fields
            'emergency_contact_name' => 'nullable|string|max:255',
            'reliever_id' => 'required',
            'remarks' => 'nullable|string',
            'leaves' => 'required|array|min:1',
            'leaves.*.request_for' => 'required|in:self,other',
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
                'office' => $employee->company_id,
                'person_id' => $personId,
                'person_name' => $employee->person_name,
                'employee_id' => $employee->id,
                'department_name' => $employee->department_name,
                'division_name' => $employee->division_name,
                'division_id' => $employee->division_id,
                'department_id' => $employee->department_id,
                'inline_supervisor_id' => $employee->reporting_manager_id,
                'inline_supervisor_name' => $employee->reporting_manager_name,
                'inline_supervisor_designation' => '',
                'department_head' =>  $employee->department_head,
                'department_head_name' =>  $employee->department_head_name,
                'on_behalf_request' => ($first['request_for'] === 'other') ? 'Yes' : 'No',
                'al_leave' => $totals['annual'],
                'cl_leave' => $totals['casual'],
                'sl_leave' => $totals['sick'],
                'pat_leave' => $totals['paternity'],
                'mat_leave' => $totals['maternity'],
                'total_leave' => array_sum($totals),
                'remarks' => $request->remarks,
                'reliever_employee' => $request->reliever_employee,
                'reliever_id' => $request->reliever_id,
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
            LogsActions::logCreate($parent, 'Leave submitted successfully.');
            return redirect()->back()->with('success', 'Leave Request Processed!');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $leaveRequest = LeaveRequest::with(['employee', 'details'])->findOrFail($id);
        return Inertia::render('leave-request/show', [
            'leaveRequest' => $leaveRequest,
            'mode' => 'view',
            'can_approve' => auth()->user()->canApproveLeave($leaveRequest)
        ]);
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
        return DB::transaction(function () use ($id) {
            $leaveRequest = LeaveRequest::findOrFail($id);
            if ($leaveRequest->status !== 0) {
                return redirect()->back()->with('error', 'Only pending requests can be deleted.');
            }
            LogsActions::logDelete(
                $leaveRequest,
                "Leave Request ID: {$leaveRequest->id} for {$leaveRequest->person_name} was deleted by " . auth()->user()->name
            );
            $leaveRequest->details()->delete();
            $leaveRequest->delete();
            return redirect()->route('leave_requests.index')
                ->with('success', 'Leave request deleted successfully.');
        });
    }

    /**
     * Handle the Approval or Rejection status update.
     */
    public function updateStatus(Request $request, $id)
    {

        $leaveRequest = LeaveRequest::findOrFail($id);
        if (!auth()->user()->canApproveLeave($leaveRequest)) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'status' => 'required|in:2,22', // 2=Approve, 22=Reject
        ]);
        $leaveRequest->update([
            'status' => $request->status,
            'approved_by' => auth()->id(),
            'approved_by_name' => auth()->user()->name,
            'approved_date' => now(),
            'approved_note' => $request->status == 2 ? 'Approved by Supervisor' : 'Rejected by Supervisor',
        ]);
        $actionName = $request->status == 2 ? 'Approved' : 'Rejected';
        \App\Traits\LogsActions::logUpdate($leaveRequest, "Leave request was $actionName by " . auth()->user()->name);
        return redirect()->route('leave_requests.index')->with('success', "Leave request $actionName successfully!");
//        return redirect()->back()->with('success', "Leave request $actionName successfully!");
    }

}
