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
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now();

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $isOffday = $date->isFriday(); // Friday off
                $statusOptions = ['Present', 'Absent', 'Leave', 'Holiday'];
                $status = $isOffday ? 'Offday' : $statusOptions[array_rand($statusOptions)];
                $office_in_time  = Carbon::createFromFormat('H:i', '09:00')->format('H:i');
                $office_out_time = Carbon::createFromFormat('H:i', '18:00')->format('H:i');
                if (!$isOffday && $status === 'Present') {
                    // Office times
                    // Employee actual in/out
                    $emp_in_time = Carbon::parse($office_in_time)->addMinutes(rand(-10, 20))->format('H:i'); // early/late
                    $emp_out_time = Carbon::parse($office_out_time)->addMinutes(rand(-15, 30))->format('H:i');
                    // Late/early calculation
                    $in_time_late = max(0, Carbon::parse($emp_in_time)->diffInMinutes(Carbon::parse($office_in_time)));
                    $out_time_late = max(0, Carbon::parse($office_out_time)->diffInMinutes(Carbon::parse($emp_out_time)));
                } else {
                   $emp_in_time = $emp_out_time = null;
                    $in_time_late = $out_time_late = 0;
                }

                Attendance::create([
                    'contact_id' => $employee->person_id,
                    'employee_id' => $employee->employee_id,
                    'emp_name' => $employee->person_name,
                    'designation' => $employee->designation->id ?? null,
                    'designation_name' => $employee->designation->name ?? $employee->designation_name,
                    'department' => $employee->department->id ?? null,
                    'department_name' => $employee->department->name ?? $employee->department_name,
                    'division' => $employee->division->id ?? null,
                    'division_name' => $employee->division->name ?? $employee->division_name,
                    'addby' => 1,
                    'add_time' => $date->format('Y-m-d H:i:s'),
                    'office_in_time' => $office_in_time,
                    'office_out_time' => $office_out_time,
                    'emp_in_time' => $emp_in_time,
                    'emp_out_time' => $emp_out_time,
                    'in_time_late' => $in_time_late,
                    'out_time_late' => $out_time_late,
                    'is_leave' => $status === 'Leave' ? 1 : 0,
                    'is_offday' => $isOffday ? 1 : 0,
                ]);
            }
        }

        $this->command->info('Attendance records seeded successfully!');
    }
}
