<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
            'leaves' => 'required|array|min:1',
            'leaves.*.request_for' => 'required|in:self,other',
            'leaves.*.person_name' => 'nullable|string|max:255',
            'leaves.*.from_date' => 'required|date',
            'leaves.*.to_date' => 'required|date|after_or_equal:leaves.*.from_date',
            'leaves.*.leave_duration' => 'required|string',
            'leaves.*.leave_reason' => 'required|string|max:255',
            'leaves.*.total_days' => 'required|integer|min:1',
        ]);

        DB::transaction(function() use ($data) {
            foreach ($data['leave_requests'] as $leave) {
                LeaveRequest::create([
                    'user_id' => Auth::id(),
                    'request_for' => $leave['request_for'],
                    'person_name' => $leave['person_name'] ?? null,
                    'from_date' => $leave['from_date'],
                    'to_date' => $leave['to_date'],
                    'leave_duration' => $leave['leave_duration'],
                    'leave_reason' => $leave['leave_reason'],
                    'total_days' => $leave['total_days'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Leave requests submitted successfully!');
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
