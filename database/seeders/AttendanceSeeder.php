<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::where('employee_status', 'Active')->get();

        foreach ($employees as $employee) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();

            $monthlyLeaveCount = 0;
            $maxLeavePerMonth  = 2; // maximum leave days per employee

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Skip duplicate check: ensure only 1 entry per employee per day
                $exists = Attendance::where('employee_id', $employee->employee_id)
                    ->whereDate('add_time', $date->format('Y-m-d'))
                    ->exists();
                if ($exists) continue;

                // Fridays are holidays
                $isOffday = $date->isFriday();

                // Determine status
                if ($isOffday) {
                    $status = 'Offday';
                } elseif ($monthlyLeaveCount < $maxLeavePerMonth && rand(1, 10) <= 2) {
                    $status = 'Leave';
                    $monthlyLeaveCount++;
                } else {
                    $status = 'Present';
                }

                // Office timings
                $office_in_time  = Carbon::createFromFormat('H:i', '09:00');
                $office_out_time = Carbon::createFromFormat('H:i', '18:00');

                if ($status === 'Present') {
                    // Randomize in/out time: early or late arrival/departure
                    $emp_in_time  = $office_in_time->copy()->addMinutes(rand(-15, 40));
                    $emp_out_time = $office_out_time->copy()->addMinutes(rand(-30, 60));

                    $emp_in_time_str  = $emp_in_time->format('H:i');
                    $emp_out_time_str = $emp_out_time->format('H:i');

                    $in_time_late  = max(0, $emp_in_time->diffInMinutes($office_in_time, false));
                    $out_time_late = max(0, $office_out_time->diffInMinutes($emp_out_time, false));
                } else {
                    $emp_in_time_str  = null;
                    $emp_out_time_str = null;
                    $in_time_late     = 0;
                    $out_time_late    = 0;
                }

                // Create attendance record
                Attendance::create([
                    'contact_id'       => $employee->person_id,
                    'employee_id'      => $employee->employee_id,
                    'emp_name'         => $employee->person_name,
                    'designation'      => $employee->designation_id ?? null,
                    'designation_name' => $employee->designation_name,
                    'department'       => $employee->department_id ?? null,
                    'department_name'  => $employee->department_name,
                    'division'         => $employee->division_id ?? null,
                    'division_name'    => $employee->division_name,
                    'addby'            => 1,
                    'add_time'         => $date->format('Y-m-d H:i:s'),
                    'office_in_time'   => $office_in_time->format('H:i'),
                    'office_out_time'  => $office_out_time->format('H:i'),
                    'emp_in_time'      => $emp_in_time_str,
                    'emp_out_time'     => $emp_out_time_str,
                    'in_time_late'     => $in_time_late,
                    'out_time_late'    => $out_time_late,
                    'is_leave'         => $status === 'Leave' ? 1 : 0,
                    'is_offday'        => $isOffday ? 1 : 0,
                    'leave_type'       => $status === 'Leave' ? 'Planned Leave' : null,
                ]);
            }
        }

        $this->command->info('Attendance records for current month seeded successfully!');
    }
}
