<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Division;
use App\Models\InfoMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LeaveReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/LeaveReport', [
            'offices'      => Company::getByType('company'),
            'finCompany'   => Company::getByType('fin_company'),
            'divisions'    => Division::getDivision(),
            'departments'  => Department::getDepartment(),
            'designations' => InfoMaster::getByType('designation'),
            'statuses' => ['Pending','Approved','Rejected']
        ]);
    }

    public function generate(Request $request)
    {
        // 1. Validate incoming request
        $validated = $request->validate([
            'person_id'   => 'nullable|integer|exists:employees,person_id',
            'department'  => 'nullable|string',
            'leave_type'  => 'nullable|string',
            'status'      => 'nullable|string|in:Pending,Approved,Rejected',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
        ]);

        // 2. Build query
        $query = DB::table('leave_requests')
            ->join('leave_request_details', 'leave_requests.id', '=', 'leave_request_details.leave_request_id')
            ->select(
                'leave_requests.id',
                'leave_requests.person_id',
                'leave_requests.person_name',
                'leave_requests.department_name',
                'leave_request_details.leave_reason as leave_type',
                'leave_request_details.from_date',
                'leave_request_details.to_date',
                'leave_request_details.total_days',
                'leave_requests.purpose_leave as reason',
                'leave_requests.status'
            );

        // 3. Apply filters
        if ($request->person_id) {
            $query->where('leave_requests.person_id', $request->person_id);
        }

        if ($request->department) {
            $query->where('leave_requests.department_name', $request->department);
        }

        if ($request->leave_type) {
            $query->where('leave_request_details.leave_type', $request->leave_type);
        }

        if ($request->status) {
            $query->where('leave_requests.status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('leave_request_details.from_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('leave_request_details.to_date', '<=', $request->to_date);
        }

        // 4. Get results
        $reportData = $query->orderBy('leave_request_details.from_date', 'desc')->get();

        // 5. Return JSON
        return response()->json([
            'success' => true,
            'reportData' => $reportData,
        ]);
    }
}
