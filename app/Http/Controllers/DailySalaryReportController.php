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
            'offices' => Company::getByType('company'),
            'divisions' => Division::getDivision(),
            'departments' => Department::getDepartment(),
        ]);
    }

    public function generate2(Request $request)
    {
        try {
            $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date',
                'office' => 'nullable',
                'department' => 'nullable',
                'employee_id' => 'nullable',
            ]);

            $query = Attendance::query()
                ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                ->select(
                    'attendances.*',
                    'employees.person_name',
                    'employees.employee_id as emp_code',
                    'employees.basic_salary',
                    'employees.house_rent_allowance',
                    'employees.medical_allowance',
                    'employees.transport_allowance',
                    'employees.other_allowances'
                )
                ->whereBetween('attendances.add_time', [
                    Carbon::parse($request->date_from)->startOfDay(),
                    Carbon::parse($request->date_to)->endOfDay()
                ]);

            // Filter Logic
            if ($request->filled('employee_id')) {
                $query->where('attendances.contact_id', $request->employee_id);
            }
            if ($request->filled('office')) {
                $query->where('employees.company_id', $request->office);
            }
            if ($request->filled('department')) {
                $query->where('employees.department_id', $request->department);
            }

            $records = $query->orderBy('attendances.add_time', 'asc')->get();

            $formatToTime = function ($totalMins) {
                $totalMins = max(0, (int)$totalMins);
                $h = floor($totalMins / 60);
                $m = $totalMins % 60;
                return sprintf('%02d:%02d', $h, $m);
            };

            $reportData = $records->map(function ($record) use ($formatToTime) {
                $basic = (float)($record->basic_salary ?? 0);
                $medical = (float)($record->medical_allowance ?? 0);
                $transport = (float)($record->transport_allowance ?? 0);
                $others = (float)($record->other_allowances ?? 0);
                $houseRent = (float)($record->house_rent_allowance ?? 0);

                // --- DAILY FIXED RATES (30 Days) ---
                $dayBasicRate = ($basic > 0) ? ($basic / 30) : 0;
                $dayAllowances = ($medical + $transport + $others + $houseRent) / 30;
                $baseHourlyRate = ($basic > 0) ? ($basic / 30 / 8) : 0;
                $otHourlyRate = $baseHourlyRate * 2;

                $totalStayMins = 0;
                $lunchMins = 0;
                $actualWorkMins = 0;
                $otMins = 0;
                $workedTk = 0;
                $otTk = 0;

                // --- STATUS LABEL LOGIC ---
                $isPaidNonWorkDay = false;
                $statusLabel = "";

                if ($record->is_leave == 1) {
                    $isPaidNonWorkDay = true;
                    $statusLabel = $record->leave_type ?: "Leave";
                } elseif ($record->is_holiday == 1) {
                    $isPaidNonWorkDay = true;
                    $statusLabel = $record->holiday_name ?: "Holiday";
                } elseif ($record->is_offday == 1) {
                    $isPaidNonWorkDay = true;
                    $statusLabel = "Week Off Day";
                }

                if ($record->emp_in_time && $record->emp_out_time) {
                    $in = Carbon::parse($record->emp_in_time);
                    $out = Carbon::parse($record->emp_out_time);
                    $totalStayMins = $in->diffInMinutes($out);

                    if ($isPaidNonWorkDay) {
                        // Full stay is OT on non-working days
                        $actualWorkMins = 0;
                        $otMins = $totalStayMins;
                        $lunchMins = 0;
                        $workedTk = $dayBasicRate;
                    } else {
                        // Regular Day Logic (9 Hours stay rule)
                        if ($totalStayMins > 300) $lunchMins = 60;
                        $requiredStayMins = 540;

                        if ($totalStayMins > $requiredStayMins) {
                            $actualWorkMins = 480;
                            $otMins = $totalStayMins - $requiredStayMins;
                        } else {
                            $actualWorkMins = max(0, $totalStayMins - $lunchMins);
                            $otMins = 0;
                        }
                    }

                    $workedTk = ($actualWorkMins / 60) * $baseHourlyRate;

                    // OT Threshold Rule (30 Mins)
                    if ($otMins >= 30) {
                        $otTk = ($otMins / 60) * $otHourlyRate;
                    } else {
                        $otMins = 0;
                        $otTk = 0;
                    }
                }

                // Calculation per record
                $basePay = $isPaidNonWorkDay ? $dayBasicRate : $workedTk;
                $totalToday = $basePay + $dayAllowances + $otTk;

                return [
                    'date' => Carbon::parse($record->add_time)->format('d M, Y'),
                    'is_off_day' => $isPaidNonWorkDay,
                    'day_status' => $statusLabel,
                    'emp_id' => $record->emp_code,
                    'name' => $record->person_name,
                    'office_in_time' => $record->office_in_time ?: '--',
                    'office_out_time' => $record->office_out_time ?: '--',
                    'in_time' => $record->emp_in_time ? Carbon::parse($record->emp_in_time)->format('h:i A') : '--',
                    'out_time' => $record->emp_out_time ? Carbon::parse($record->emp_out_time)->format('h:i A') : '--',
                    'total_work_str' => $formatToTime($totalStayMins),
                    'actual_work_str' => $formatToTime($actualWorkMins),
                    'ot_hours_str' => $formatToTime($otMins),
                    'medical_tk' => round($medical / 30, 2),
                    'transport_tk' => round($transport / 30, 2),
                    'other_tk' => round($others / 30, 2),
                    'house_rent_tk' => round($houseRent / 30, 2),
                    'worked_salary_tk' => round($isPaidNonWorkDay ? $dayBasicRate : $workedTk, 2),
                    'ot_amount' => round($otTk, 2),
                    'total_today' => round($totalToday, 2),
                    'work_hours' => round($actualWorkMins / 60, 4),
                    'ot_hours' => round($otMins / 60, 4),
                    'hourly_rate' => round($baseHourlyRate, 2)
                ];
            });

            return response()->json([
                'reportData' => $reportData,
                'summary' => [
                    'total_days' => $reportData->count(),
                    'grand_total' => round($reportData->sum('total_today'), 2),
                    'total_ot_tk' => round($reportData->sum('ot_amount'), 2),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => "System Error: " . $e->getMessage()], 500);
        }
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date',
                'office' => 'nullable',
                'department' => 'nullable',
                'employee_id' => 'required',
            ], [
                // CUSTOM MESSAGES
                'employee_id.required' => 'Please select an employee.',
                'date_from.required' => 'Please select a start date.',
                'date_to.required' => 'Please select an end date.',
            ]);

            $query = Attendance::query()
                ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                ->select(
                    'attendances.*',
                    'employees.person_name',
                    'employees.employee_id as emp_code',
                    'employees.basic_salary',
                    'employees.house_rent_allowance',
                    'employees.medical_allowance',
                    'employees.transport_allowance',
                    'employees.other_allowances'
                )
                ->whereBetween('attendances.add_time', [
                    Carbon::parse($request->date_from)->startOfDay(),
                    Carbon::parse($request->date_to)->endOfDay()
                ]);

            // Filter Logic
            if ($request->filled('employee_id')) {
                $query->where('attendances.contact_id', $request->employee_id);
            }
            if ($request->filled('office')) {
                $query->where('employees.company_id', $request->office);
            }
            if ($request->filled('department')) {
                $query->where('employees.department_id', $request->department);
            }

            $records = $query->orderBy('attendances.add_time', 'asc')->get();

            $formatToTime = function ($totalMins) {
                $totalMins = max(0, (int)$totalMins);
                $h = floor($totalMins / 60);
                $m = $totalMins % 60;
                return sprintf('%02d:%02d', $h, $m);
            };

            $reportData = $records->map(function ($record) use ($formatToTime) {
                $basic = (float)($record->basic_salary ?? 0);
                $medical = (float)($record->medical_allowance ?? 0);
                $transport = (float)($record->transport_allowance ?? 0);
                $others = (float)($record->other_allowances ?? 0);
                $houseRent = (float)($record->house_rent_allowance ?? 0);

                // --- PRORATION LOGIC (For Fixed Monthly Total) ---
                $attendanceDate = Carbon::parse($record->add_time);
                $daysInThisMonth = $attendanceDate->daysInMonth; // 28, 29, 30, or 31

                // Safety check: ensure divisor is never 0
                $divisor = ($daysInThisMonth > 0) ? $daysInThisMonth : 30;

                // Base Pay & Allowances divided by ACTUAL days (Total will be exactly Monthly Basic)
                $dayBasicRate = ($basic > 0) ? ($basic / $divisor) : 0;
                $dayMedicalRate = $medical / $divisor;
                $dayTransportRate = $transport / $divisor;
                $dayOthersRate = $others / $divisor;
                $dayHouseRentRate = $houseRent / $divisor;

                $dayAllowancesToday = $dayMedicalRate + $dayTransportRate + $dayOthersRate + $dayHouseRentRate;

                // --- OT CALCULATION (Factory Standard Fixed 30 Days) ---
                $baseHourlyRate = ($basic > 0) ? ($basic / 30 / 8) : 0;
                $otHourlyRate = $baseHourlyRate * 2;

                $totalStayMins = 0;
                $lunchMins = 0;
                $actualWorkMins = 0;
                $otMins = 0;
                $workedTk = 0;
                $otTk = 0;

                // --- STATUS LABEL LOGIC ---
                $isPaidNonWorkDay = false;
                $statusLabel = "";

                if ($record->is_leave == 1) {
                    $isPaidNonWorkDay = true;
                    $statusLabel = $record->leave_type ?: "Leave";
                } elseif ($record->is_holiday == 1) {
                    $isPaidNonWorkDay = true;
                    $statusLabel = $record->holiday_name ?: "Holiday";
                } elseif ($record->is_offday == 1) {
                    $isPaidNonWorkDay = true;
                    $statusLabel = "Week Off Day";
                }

                if ($record->emp_in_time && $record->emp_out_time) {
                    $in = Carbon::parse($record->emp_in_time);
                    $out = Carbon::parse($record->emp_out_time);
                    $totalStayMins = $in->diffInMinutes($out);

                    if ($isPaidNonWorkDay) {
                        $actualWorkMins = 0;
                        $otMins = $totalStayMins;
                        $lunchMins = 0;
                        $workedTk = $dayBasicRate;
                    } else {
                        if ($totalStayMins > 300) $lunchMins = 60;
                        $requiredStayMins = 540;

                        if ($totalStayMins > $requiredStayMins) {
                            $actualWorkMins = 480;
                            $otMins = $totalStayMins - $requiredStayMins;
                        } else {
                            $actualWorkMins = max(0, $totalStayMins - $lunchMins);
                            $otMins = 0;
                        }
                        // Calculate earned basic for worked hours
                        $hourlyBaseForToday = $dayBasicRate / 8;
                        $workedTk = ($actualWorkMins / 60) * $hourlyBaseForToday;
                    }

                    if ($otMins >= 30) {
                        $otTk = ($otMins / 60) * $otHourlyRate;
                    } else {
                        $otMins = 0;
                        $otTk = 0;
                    }
                } else {
                    $workedTk = $isPaidNonWorkDay ? $dayBasicRate : 0;
                }

                $totalToday = $workedTk + $dayAllowancesToday + $otTk;

                return [
                    'date' => $attendanceDate->format('d M, Y'),
                    'is_off_day' => $isPaidNonWorkDay,
                    'day_status' => $statusLabel,
                    'emp_id' => $record->emp_code,
                    'name' => $record->person_name,
                    'office_in_time' => $record->office_in_time ?: '--',
                    'office_out_time' => $record->office_out_time ?: '--',
                    'lunch_break' => ($lunchMins > 0) ? '01:00' : '00:00',
                    'in_time' => $record->emp_in_time ? Carbon::parse($record->emp_in_time)->format('h:i A') : '--',
                    'out_time' => $record->emp_out_time ? Carbon::parse($record->emp_out_time)->format('h:i A') : '--',
                    'total_work_str' => $formatToTime($totalStayMins),
                    'actual_work_str' => $formatToTime($actualWorkMins),
                    'ot_hours_str' => $formatToTime($otMins),
                    'medical_tk' => round($dayMedicalRate, 2),
                    'transport_tk' => round($dayTransportRate, 2),
                    'other_tk' => round($dayOthersRate, 2),
                    'house_rent_tk' => round($dayHouseRentRate, 2),
                    'worked_salary_tk' => round($workedTk, 2),
                    'ot_amount' => round($otTk, 2),
                    'total_today' => round($totalToday, 2),
                    'work_hours' => round($actualWorkMins / 60, 4),
                    'ot_hours' => round($otMins / 60, 4),
                    'hourly_rate' => round($baseHourlyRate, 2)
                ];
            });

            return response()->json([
                'reportData' => $reportData,
                'summary' => [
                    'total_days' => $reportData->count(),
                    'grand_total' => round($reportData->sum('total_today'), 2),
                    'total_ot_tk' => round($reportData->sum('ot_amount'), 2),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => "System Error: " . $e->getMessage()], 500);
        }
    }
}
