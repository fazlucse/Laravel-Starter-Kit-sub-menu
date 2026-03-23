<?php

namespace App\Http\Controllers;

use App\Constants\RoleConstants;
use App\Models\{Attendance, ActionLog, LeaveRequest, MovementRegister, Payroll, User, Holiday};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        $today = Carbon::today();
        $thisMonth = now()->month;

        // 1. Holidays (Universal)
        $upcomingHolidays = Holiday::where('holiday_date', '>=', $today)
            ->orderBy('holiday_date', 'asc')->take(3)->get()
            ->map(fn($h) => [
                'date' => $h->holiday_date,
                'title' => $h->holiday_type_name ?? $h->remarks ?? 'Public Holiday',
            ]);

        $data = [
            'auth' => ['user' => $user->only('id', 'name', 'role', 'department_id', 'division_id')],
            'upcoming_holidays' => $upcomingHolidays,
        ];

        $role = $user->role;

        // --- GROUP A: FULL ACCESS (Dev, Admin, HR) ---
        if (in_array($role, RoleConstants::FULL_ACCESS)) {
            $this->prepareAdminStats($data, $today);
            $data['table_data'] = ActionLog::with('user')->latest()->take(5)->get();
        }

        // --- GROUP B: ACCOUNT / FINANCE ---
        elseif ($role === RoleConstants::Account) {
            $data['stats'] = [
                ['label' => 'Ready for Payment', 'value' => Payroll::where('status', 'approved')->count(), 'color' => 'text-orange-600'],
                ['label' => 'Disbursed This Month', 'value' => '৳' . number_format(Payroll::where('status', 'paid')->whereMonth('created_at', $thisMonth)->sum('net_payable')), 'color' => 'text-emerald-600'],
                ['label' => 'Total TDS/Tax', 'value' => '৳' . number_format(Payroll::sum('tax_amount')), 'color' => 'text-slate-600'],
            ];
            $data['table_data'] = Payroll::with('user')->where('status', 'approved')->get();
        }

        // --- GROUP C: DEPARTMENT HEAD / SUPERVISOR (RESTORED) ---
        elseif (in_array($role, [RoleConstants::DEPARTMENT_HEAD, RoleConstants::SUPERVISOR])) {
            // Get Team IDs
            $teamIds = ($role === RoleConstants::DEPARTMENT_HEAD)
                ? User::where('department_id', $user->department_id)->pluck('id')
                : User::where('supervisor_id', $user->id)->pluck('id');

            $data['stats'] = [
                ['label' => 'Team Presence', 'value' => Attendance::whereIn('contact_id', $teamIds)->whereDate('add_time', $today)->whereNotNull('emp_in_time')->count() . '/' . count($teamIds), 'color' => 'text-blue-600'],
                ['label' => 'Pending Team Leaves', 'value' => LeaveRequest::whereIn('person_id', $teamIds)->where('status', 'pending')->count(), 'color' => 'text-rose-600'],
                ['label' => 'Team Movements', 'value' => MovementRegister::whereIn('created_by', $teamIds)->whereDate('created_at', $today)->count(), 'color' => 'text-indigo-600'],
            ];

            $data['table_data'] = LeaveRequest::with('employee')
                ->whereIn('person_id', $teamIds)
                ->where('status', 'pending')
                ->latest()->get();
        }

        // --- GROUP D: STANDARD EMPLOYEE ---
        else {
            $data['stats'] = [
                ['label' => 'My Attendance', 'value' => Attendance::where('user_id', $user->id)->whereMonth('add_time', $thisMonth)->count() . ' Days', 'color' => 'text-emerald-600'],
                ['label' => 'My Leave Balance', 'value' => $user->leave_balance ?? 0, 'color' => 'text-blue-600'],
            ];
            $data['table_data'] = MovementRegister::where('user_id', $user->id)->latest()->take(5)->get();
        }

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Helper to keep the Admin Stats logic clean
     */
    private function prepareAdminStats(&$data, $today) {
        $present = Attendance::with('employee.designation', 'employee.department')
            ->whereDate('add_time', $today)->whereNotNull('emp_in_time')->where('emp_in_time', '!=', '')->where('is_leave', '!=', 1)->get();

        $absent = Attendance::with('employee.designation', 'employee.department')
            ->whereDate('add_time', $today)->where(fn($q) => $q->whereNull('emp_in_time')->orWhere('emp_in_time', ''))
            ->where('is_offday', '!=', 1)->where('is_holiday', '!=', 1)->where('is_leave', '!=', 1)->get();

        $leave = Attendance::with('employee.designation', 'employee.department')
            ->whereDate('add_time', $today)->where('is_leave', 1)->get();

        $late = Attendance::with('employee.designation', 'employee.department')
            ->whereDate('add_time', $today)->whereNotNull('emp_in_time')->whereTime('emp_in_time', '>', '09:15:00')->get();

        $data['stats'] = [
            ['label' => 'Total Present', 'value' => $present->count(), 'color' => 'text-emerald-600'],
            ['label' => 'On Leave Today', 'value' => $leave->count(), 'color' => 'text-blue-600'],
            ['label' => 'Total Absent', 'value' => $absent->count(), 'color' => 'text-rose-600'],
            ['label' => 'Late In Today', 'value' => $late->count(), 'color' => 'text-orange-600'],
        ];

        $data['stats_details'] = [
            'Total Present' => $present->map(fn($a) => $this->mapPerson($a, 'In: '.$a->emp_in_time, 'bg-emerald-500')),
            'On Leave Today' => $leave->map(fn($a) => $this->mapPerson($a, 'On Leave', 'bg-blue-500')),
            'Total Absent' => $absent->map(fn($a) => $this->mapPerson($a, 'Absent', 'bg-rose-500')),
            'Late In Today' => $late->map(fn($a) => $this->mapPerson($a, 'Late: '.$a->emp_in_time, 'bg-orange-500')),
        ];
    }

    private function mapPerson($att, $info, $color) {
        return [
            'name' => $att->employee->person_name ?? 'Unknown',
            'id' => $att->employee->employee_id ?? 'N/A',
            'photo' => $att->employee->profile_photo_url ?? null,
            'dept' => $att->employee->department->name ?? 'N/A',
            'desig' => $att->employee->designation->name ?? 'N/A',
            'info' => $info,
            'status_color' => $color
        ];
    }
}
