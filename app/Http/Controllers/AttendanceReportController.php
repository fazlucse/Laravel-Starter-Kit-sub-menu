<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/AttendanceReport', [
            'offices' => DB::table('attendances')->distinct()->pluck('operational_office_name'),
            'departments' => DB::table('attendances')->distinct()->pluck('department_name'),
            'divisions' => DB::table('attendances')->distinct()->pluck('division_name'),
        ]);
    }

    public function generate2(Request $request)
    {
        $query = DB::table('attendances');

        // Filters
        if ($request->office) $query->where('operational_office_name', $request->office);
        if ($request->department) $query->where('department_name', $request->department);
        if ($request->division) $query->where('division_name', $request->division);
        if ($request->employee_id) $query->where('employee_id', $request->employee_id);

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('add_time', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }

        $attendances = $query->get()->map(function ($row) {
            // --- 1. Set Office Schedule Defaults (9 AM to 6 PM) ---
            $offInTime  = !empty($row->office_in_time) ? $row->office_in_time : '09:00:00';
            $offOutTime = !empty($row->office_out_time) ? $row->office_out_time : '18:00:00';

            // --- 2. Determine Attendance Status ---
            // Priority: Leave > Off Day > Present (In-time exists) > Absent
            $status = 'Absent';
            if ($row->is_leave) {
                $status = 'Leave (' . $row->leave_type . ')';
            } elseif ($row->is_offday) {
                $status = 'Off Day';
            } elseif (!empty($row->emp_in_time)) {
                $status = 'Present';
            }

            $working_time = '--:--';
            $overtime     = '--:--';
            $early_out    = '--';
            $late_in      = '--';

            // --- 3. Calculations ---
            if (!empty($row->emp_in_time)) {
                $in = \Carbon\Carbon::parse($row->emp_in_time);

                // Late In Calculation
                $offInThreshold = \Carbon\Carbon::parse($offInTime);
                if ($in->gt($offInThreshold)) {
                    $lateMinutes = $in->diffInMinutes($offInThreshold);
                    $late_in = $lateMinutes . ' min';
                }

                // Only calculate Work/Overtime/EarlyOut if Out-time exists
                if (!empty($row->emp_out_time)) {
                    $out = \Carbon\Carbon::parse($row->emp_out_time);

                    // Working Time (Gross)
                    $totalMinutes = $in->diffInMinutes($out);
                    $working_time = $this->minutesToTime($totalMinutes);

                    // Overtime (Threshold: 8hrs work + 1hr lunch = 9hrs / 540 min)
                    $thresholdMinutes = 540;
                    if ($totalMinutes > $thresholdMinutes) {
                        $extra = $totalMinutes - $thresholdMinutes;
                        $overtime = $this->minutesToTime($extra);
                    } else {
                        $overtime = '00:00';
                    }

                    // Early Out Calculation
                    $offOutThreshold = \Carbon\Carbon::parse($offOutTime);
                    if ($out->lt($offOutThreshold)) {
                        $earlyMinutes = $out->diffInMinutes($offOutThreshold);
                        $early_out = $earlyMinutes . ' min';
                    }
                }
            }
            $out_status = '--';
            if (!empty($row->emp_out_time)) {
                $out = \Carbon\Carbon::parse($row->emp_out_time);
                $offOutThreshold = \Carbon\Carbon::parse($offOutTime); // Default 18:00:00

                if ($out->lt($offOutThreshold)) {
                    $diff = $out->diffInMinutes($offOutThreshold);
                    $out_status = 'Early: ' . $diff . 'm';
                } elseif ($out->gt($offOutThreshold)) {
                    $diff = $out->diffInMinutes($offOutThreshold);
                    $out_status = 'Late: ' . $diff . 'm';
                }
            }
            $offInThreshold = \Carbon\Carbon::parse($offInTime);
            $offOutThreshold = \Carbon\Carbon::parse($offOutTime);

            $officeDurationMinutes = $offInThreshold->diffInMinutes($offOutThreshold);
            $office_work_hours = $this->minutesToTime($officeDurationMinutes);
            return [
                'emp_name'     => $row->emp_name,
                'employee_id'  => $row->employee_id,
                'designation'  => $row->designation_name,
                'department'   => $row->department_name,
                'division'     => $row->division_name,
                'status'       => $status,
                'date'         => $row->add_time ? date('d M y', strtotime($row->add_time)) : '---',

                'off_in'       => $this->formatAmPm($offInTime),
                'off_out'      => $this->formatAmPm($offOutTime),
                'att_in'       => $this->formatAmPm($row->emp_in_time),
                'att_out'      => $this->formatAmPm($row->emp_out_time),
                'out_status' => $out_status,
                'late_in'      => $late_in,
                'early_out'    => $early_out,
                'working_time' => $working_time,
                'overtime'     => $overtime,
                'office_work_hours' => $office_work_hours,
            ];
        });

        return response()->json(['reportData' => $attendances]);
    }
    public function generate3(Request $request)
    {
        $query = DB::table('attendances');
        // Filters
        if ($request->office) $query->where('operational_office_name', $request->office);
        if ($request->department) $query->where('department_name', $request->department);
        if ($request->division) $query->where('division_name', $request->division);
        if ($request->employee_id) $query->where('contact_id', $request->employee_id);

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('add_time', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }

        // Ramadan range
        $ramadanStart = \Carbon\Carbon::parse('2025-02-18');
        $ramadanEnd   = \Carbon\Carbon::parse('2025-03-19');

        $attendances = $query->get()->map(function ($row) use ($ramadanStart, $ramadanEnd) {

            // --- Attendance Date ---
            $attendanceDate = \Carbon\Carbon::parse($row->add_time);

            // --- Check Ramadan ---
            $isRamadan = $attendanceDate->between($ramadanStart, $ramadanEnd);

            // --- Lunch Break ---
            $lunchBreak = $isRamadan ? 30 : 60;

            // --- Office Schedule ---
            $offInTime  = !empty($row->office_in_time) ? $row->office_in_time : '09:00:00';
            $offOutTime = !empty($row->office_out_time) ? $row->office_out_time : '18:00:00';

            // --- Status ---
            $status = 'Absent';
            if ($row->is_leave) {
                $status = 'Leave (' . $row->leave_type . ')';
            } elseif ($row->is_offday) {
                $status = 'Off Day';
            } elseif (!empty($row->emp_in_time)) {
                $status = 'Present';
            }

            $working_time = '--:--';
            $overtime     = '--:--';
            $early_out    = '--';
            $late_in      = '--';

            if (!empty($row->emp_in_time)) {

                $in = \Carbon\Carbon::parse($row->emp_in_time);
                $offInThreshold = \Carbon\Carbon::parse($offInTime);

                // Late In
                if ($in->gt($offInThreshold)) {
                    $lateMinutes = $in->diffInMinutes($offInThreshold);
                    $late_in = $lateMinutes . ' min';
                }

                if (!empty($row->emp_out_time)) {

                    $out = \Carbon\Carbon::parse($row->emp_out_time);

                    // Gross Work
                    $totalMinutes = $in->diffInMinutes($out);

                    // Lunch deduction
                    $netMinutes = $totalMinutes > $lunchBreak
                        ? $totalMinutes - $lunchBreak
                        : $totalMinutes;

                    $working_time = $this->minutesToTime($netMinutes);

                    // Overtime (8 hours)
                    $requiredMinutes = 480;

                    if ($netMinutes > $requiredMinutes) {
                        $extra = $netMinutes - $requiredMinutes;
                        $overtime = $this->minutesToTime($extra);
                    } else {
                        $overtime = '--:--';
                    }

                    // Early Out
                    $offOutThreshold = \Carbon\Carbon::parse($offOutTime);

                    if ($out->lt($offOutThreshold)) {
                        $earlyMinutes = $out->diffInMinutes($offOutThreshold);
                        $early_out = $earlyMinutes . ' min';
                    }
                }
            }

            // Out Status
            $out_status = '--';

            if (!empty($row->emp_out_time)) {

                $out = \Carbon\Carbon::parse($row->emp_out_time);
                $offOutThreshold = \Carbon\Carbon::parse($offOutTime);

                if ($out->lt($offOutThreshold)) {
                    $diff = $out->diffInMinutes($offOutThreshold);
                    $out_status = 'Early: ' . $diff . 'm';
                } elseif ($out->gt($offOutThreshold)) {
                    $diff = $out->diffInMinutes($offOutThreshold);
                    $out_status = 'Late: ' . $diff . 'm';
                }
            }

            // Office Work Hours
            $offInThreshold = \Carbon\Carbon::parse($offInTime);
            $offOutThreshold = \Carbon\Carbon::parse($offOutTime);

            $officeDurationMinutes = $offInThreshold->diffInMinutes($offOutThreshold);

            $netOfficeMinutes = $officeDurationMinutes > $lunchBreak
                ? $officeDurationMinutes - $lunchBreak
                : $officeDurationMinutes;

            $office_work_hours = $this->minutesToTime($netOfficeMinutes);

            return [
                'emp_name'     => $row->emp_name,
                'employee_id'  => $row->employee_id,
                'designation'  => $row->designation_name,
                'department'   => $row->department_name,
                'division'     => $row->division_name,
                'status'       => $status,
                'date'         => $row->add_time ? date('d M y', strtotime($row->add_time)) : '---',

                'off_in'       => $this->formatAmPm($offInTime),
                'off_out'      => $this->formatAmPm($offOutTime),
                'att_in'       => $this->formatAmPm($row->emp_in_time),
                'att_out'      => $this->formatAmPm($row->emp_out_time),

                'out_status'   => $out_status,
                'late_in'      => $late_in,
                'early_out'    => $early_out,

                'working_time' => $working_time,
                'overtime'     => $overtime,
                'office_work_hours' => $office_work_hours,
            ];
        }) ->sortBy(function ($row) {
            return strtotime($row['date']); // Sort by date ascending
        })
            ->values();

        return response()->json(['reportData' => $attendances]);
    }
    public function generate(Request $request)
    {
        $query = DB::table('attendances');

        // Filters
        if ($request->office) $query->where('operational_office_name', $request->office);
        if ($request->department) $query->where('department_name', $request->department);
        if ($request->division) $query->where('division_name', $request->division);

        // Filter by Person ID (Autocomplete) or Employee ID
        if ($request->person_id) {
            $query->where('contact_id', $request->person_id);
        } elseif ($request->employee_id) {
            $query->where('contact_id', $request->employee_id);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('add_time', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }

        // Ramadan range for 2025
        $ramadanStart = \Carbon\Carbon::parse('2025-02-18');
        $ramadanEnd   = \Carbon\Carbon::parse('2025-03-19');

        $attendances = $query->get()->map(function ($row) use ($ramadanStart, $ramadanEnd) {
            $attendanceDate = \Carbon\Carbon::parse($row->add_time);
            $isRamadan = $attendanceDate->between($ramadanStart, $ramadanEnd);
            $lunchBreak = $isRamadan ? 30 : 60;

            // Office Schedule
            $offInTime  = !empty($row->office_in_time) ? $row->office_in_time : '09:00:00';
            $offOutTime = !empty($row->office_out_time) ? $row->office_out_time : '18:00:00';

            // Calculate Required Office Net Minutes
            $offInThreshold = \Carbon\Carbon::parse($offInTime);
            $offOutThreshold = \Carbon\Carbon::parse($offOutTime);
            $officeDurationMinutes = $offInThreshold->diffInMinutes($offOutThreshold);
            $netOfficeMinutes = $officeDurationMinutes > $lunchBreak ? $officeDurationMinutes - $lunchBreak : $officeDurationMinutes;

            // Attendance Status
            $status = 'Absent';
            if ($row->is_leave) $status = 'Leave (' . $row->leave_type . ')';
            elseif ($row->is_offday) $status = 'Off Day';
            elseif (!empty($row->emp_in_time)) $status = 'Present';

            $working_time = '--:--';
            $overtime     = '--:--';
            $less_working = '--:--';
            $early_out    = '--';
            $late_in      = '--';
            $out_status   = '--';

            if (!empty($row->emp_in_time)) {
                $in = \Carbon\Carbon::parse($row->emp_in_time);

                // Late In Calculation
                if ($in->gt($offInThreshold)) {
                    $late_in = $in->diffInMinutes($offInThreshold) . ' min';
                }

                if (!empty($row->emp_out_time)) {
                    $out = \Carbon\Carbon::parse($row->emp_out_time);
                    $totalMinutes = $in->diffInMinutes($out);

                    // Actual Net Work Calculation
                    $netMinutes = $totalMinutes > $lunchBreak ? $totalMinutes - $lunchBreak : $totalMinutes;
                    $working_time = $this->minutesToTime($netMinutes);

                    // Overtime vs Less Working
                    if ($netMinutes > $netOfficeMinutes) {
                        $overtime = $this->minutesToTime($netMinutes - $netOfficeMinutes);
                    } elseif ($netMinutes < $netOfficeMinutes) {
                        $less_working = $this->minutesToTime($netOfficeMinutes - $netMinutes);
                    }

                    // Out Status & Early Out
                    if ($out->lt($offOutThreshold)) {
                        $diff = $out->diffInMinutes($offOutThreshold);
                        $out_status = 'Early: ' . $diff . 'm';
                        $early_out = $diff . ' min';
                    } elseif ($out->gt($offOutThreshold)) {
                        $diff = $out->diffInMinutes($offOutThreshold);
                        $out_status = 'Late: ' . $diff . 'm';
                    }
                }
            }

            return [
                'emp_name'          => $row->emp_name,
                'employee_id'       => $row->employee_id,
                'designation'       => $row->designation_name,
                'department'        => $row->department_name,
                'division'          => $row->division_name,
                'status'            => $status,
                'date'              => $row->add_time ? date('d M y', strtotime($row->add_time)) : '---',
                'off_in'            => $this->formatAmPm($offInTime),
                'off_out'           => $this->formatAmPm($offOutTime),
                'att_in'            => $this->formatAmPm($row->emp_in_time),
                'att_out'           => $this->formatAmPm($row->emp_out_time),
                'late_in'           => $late_in,
                'early_out'         => $early_out,
                'out_status'        => $out_status,
                'working_time'      => $working_time,
                'overtime'          => $overtime,
                'less_working'      => $less_working,
                'office_work_hours' => $this->minutesToTime($netOfficeMinutes),
            ];
        })->sortBy(fn($row) => strtotime($row['date']))->values();

        return response()->json(['reportData' => $attendances]);
    }
    /**
     * Format minutes back to HH:mm
     */
    private function minutesToTime($totalMinutes) {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Format string to AM/PM
     */
    private function formatAmPm($time) {
        if (!$time || $time == '--:--' || $time == '00:00:00') return '--:--';
        return date('h:i A', strtotime($time));
    }

    private function timeToMinutes($time) {
        if(!$time || $time == '--:--') return 0;
        $parts = explode(':', $time);
        return ($parts[0] * 60) + $parts[1];
    }

}
