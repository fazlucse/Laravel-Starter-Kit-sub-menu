<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Division;
use App\Models\Department;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class DailySalaryReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/dailySalaryReport', [
            'offices'     => Company::getByType('company'),
            'divisions'   => Division::getDivision(),
            'departments' => Department::getDepartment(),
        ]);
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'date_from'   => 'required|date',
                'date_to'     => 'required|date',
                'office'      => 'nullable',
                'department'  => 'nullable',
                'employee_id' => 'nullable',
            ]);

            $query = Attendance::query()
                ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                ->select(
                    'attendances.*',
                    'employees.person_name',
                    'employees.employee_id as emp_code',
                    'employees.basic_salary'
                )
                ->whereBetween('attendances.add_time', [
                    Carbon::parse($request->date_from)->startOfDay(),
                    Carbon::parse($request->date_to)->endOfDay()
                ]);

            if ($request->filled('employee_id')) {
                // Using employee_id from join to ensure match
                $query->where('attendances.contact_id', $request->employee_id);
            }
            if ($request->filled('office')) {
                $query->where('employees.company_id', $request->office);
            }
            if ($request->filled('department')) {
                $query->where('employees.department_id', $request->department);
            }

            $records = $query->orderBy('attendances.add_time', 'asc')->get();

            $reportData = $records->map(function ($record) {
                $basic = $record->basic_salary ?? 0;

                // --- HOURLY RATE CALCULATION ---
                // Standard: Basic / 30 Days / 8 Hours
                $baseHourlyRate = ($basic > 0) ? ($basic / 30 / 8) : 0;
                $otHourlyRate   = $baseHourlyRate * 2; // 2.0x Multiplier

                $workHours = 0;
                $otHours   = 0;
                $workedBaseSalary = 0;
                $otTk      = 0;

                if ($record->emp_in_time && $record->emp_out_time) {
                    $in  = Carbon::parse($record->emp_in_time);
                    $out = Carbon::parse($record->emp_out_time);

                    // Shift Boundaries (09:00 - 18:00)
                    $shiftStart = $in->copy()->setTime(9, 0, 0);
                    $shiftEnd   = $in->copy()->setTime(18, 0, 0);

                    $presenceMins = $in->diffInMinutes($out);
                    $deductLunch = ($presenceMins > 300); // 60 mins deduction if worked > 5 hrs

                    // 1. Calculate Normal Work Hours (Capped at 8)
                    $effIn  = $in->isBefore($shiftStart) ? $shiftStart : $in;
                    $effOut = $out->isAfter($shiftEnd)   ? $shiftEnd   : $out;

                    if ($effOut->isAfter($effIn)) {
                        $normalMins = $effIn->diffInMinutes($effOut);
                        if ($deductLunch) $normalMins -= 60;
                        $workHours = round(max(0, $normalMins / 60), 2);

                        // Hourly Based Salary for the day
                        $workedBaseSalary = $workHours * $baseHourlyRate;
                    }

                    // 2. Calculate OT Hours (After 18:00)
                    if ($out->isAfter($shiftEnd)) {
                        $otMins  = $shiftEnd->diffInMinutes($out);
                        $otHours = round($otMins / 60, 2);
                        $otTk    = round($otHours * $otHourlyRate, 2);
                    }
                }

                // If no clock in/out but date exists (Absent logic or basic day rate)
                $dayRate = $basic / 30;

                return [
                    'date'               => Carbon::parse($record->add_time)->format('d M, Y'),
                    'emp_id'             => $record->emp_code,
                    'name'               => $record->person_name,
                    'in_time'            => $record->emp_in_time ? Carbon::parse($record->emp_in_time)->format('h:i A') : '--',
                    'out_time'           => $record->emp_out_time ? Carbon::parse($record->emp_out_time)->format('h:i A') : '--',
                    'work_hours'         => $workHours,
                    'ot_hours'           => $otHours,
                    'hourly_rate'        => round($baseHourlyRate, 2),
                    'hourly_salary_tk'   => round($workedBaseSalary, 2), // Actual earned via hours
                    'day_salary_fixed'   => round($dayRate, 2),        // Fixed rate for the day
                    'ot_amount'          => $otTk,
                    'total_today'        => round($workedBaseSalary + $otTk, 2),
                ];
            });

            return response()->json([
                'reportData' => $reportData,
                'summary' => [
                    'total_days'     => $reportData->count(),
                    'total_ot_tk'    => round($reportData->sum('ot_amount'), 2),
                    'total_work_tk'  => round($reportData->sum('hourly_salary_tk'), 2),
                    'grand_total'    => round($reportData->sum('total_today'), 2),
                ]
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => "SQL ERROR: " . $e->getMessage(),
                'sql' => $e->getSql()
            ], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => "System Error: " . $e->getMessage()], 500);
        }
    }
}
