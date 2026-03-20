<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Division;
use App\Models\InfoMaster;
use App\Traits\LogsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollOTController extends Controller
{
    /**
     * Display a listing of payroll batches (History)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $search = $request->input('search');

        $history = \App\Models\PayrollBatch::query()
            ->when($search, function ($query, $search) {
                $query->where('com_name', 'like', "%{$search}%")
                    ->orWhere('payroll_month', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderByDesc('payroll_month')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('payroll-ot/payroll', [
            'payrollHistory' => $history,
            'offices'      => Company::getByType('company'),
            'divisions'    => Division::getDivision(),
            'departments'  => Department::getDepartment(),
            'designations' => InfoMaster::getByType('designation'),
            'filters'        => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('payroll-ot/generate', [
            'offices'      => Company::getByType('company'),
            'divisions'    => Division::getDivision(),
            'departments'  => Department::getDepartment(),
            'designations' => InfoMaster::getByType('designation'),
            'indianPostOptions' => $this->getIndianStatutoryOptions(),
            'taxBrackets' => $this->getTaxBrackets(),
        ]);
    }

    public function show($id)
    {
        $batch = \DB::table('payroll_batches')
            ->leftJoin('users', 'payroll_batches.prepared_by', '=', 'users.id')
            ->select('payroll_batches.*', 'users.name as prepared_by_name')
            ->where('payroll_batches.id', $id)
            ->first();

        if (!$batch) {
            return redirect()->route('payroll.index')->withErrors(['error' => 'Payroll batch not found.']);
        }

        $items = \DB::table('payrolls')
            ->where('batch_id', $id)
            ->orderBy('emp_name', 'asc')
            ->get();

        return Inertia::render('payroll-ot/show', [
            'batch' => $batch,
            'items' => $items
        ]);
    }

    /**
     * UPDATED GENERATE METHOD: Integrated Hourly/OT Logic
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'is_salary'      => 'required|boolean',
            'is_bonus'       => 'required|boolean',
            'is_individual'  => 'nullable|boolean',
            'employee_id'    => 'nullable|integer',
            'department'     => 'nullable|integer',
            'division'       => 'nullable|integer',
            'office'         => 'nullable|integer',
            'payrollMonth'   => 'required|date_format:Y-m',
            'bonus_type'     => 'nullable|string',
            'bonus_date'     => 'nullable|date',
            'postOption'     => 'required|in:draft,posted,approved',
        ]);

        $query = Employee::query();
        if ($request->boolean('is_individual') && $request->filled('employee_id')) {
            $query->where('person_id', $request->employee_id);
        } else {
            if ($request->filled('department')) $query->where('department_id', $request->department);
            if ($request->filled('division')) $query->where('division_id', $request->division);
            if ($request->filled('office')) $query->where('company_id', $request->office);
        }

        $employees = $query->get();
        $isSalaryEnabled = $request->boolean('is_salary');
        $isBonusEnabled  = $request->boolean('is_bonus');
        $factoryDays     = 30;
        $payrollMonthStr = $request->payrollMonth;
        $startOfMonth    = \Carbon\Carbon::parse($payrollMonthStr)->startOfMonth();
        $endOfMonth      = \Carbon\Carbon::parse($payrollMonthStr)->endOfMonth();
        $taxBrackets     = $this->getTaxBrackets();

        $payrollData = $employees->map(function ($employee) use ($request, $startOfMonth, $endOfMonth, $factoryDays, $isSalaryEnabled, $isBonusEnabled, $taxBrackets) {

            $basic = 0; $houseRent = 0; $medical = 0; $transport = 0; $otherAllow = 0;
            $bonusAmount = 0; $lwpDays = 0; $otTk = 0; $otHours = 0;
            $workedDays = $factoryDays;

            if ($isSalaryEnabled) {
                // --- INTEGRATED HOURLY & OT CALCULATION ---
                $attendance = $this->calculateWorkedHours($employee->employee_id, $employee->basic_salary, $startOfMonth, $endOfMonth);
                $otTk = $attendance['ot_tk'];
                $work_Time = $attendance['normal'];
                $otHours = $attendance['ot'];
                $lwpDays = $this->getEmployeeLwpDays($employee->id, $startOfMonth, $endOfMonth);

                $effectiveDate = $employee->effective_date ? \Carbon\Carbon::parse($employee->effective_date) : null;
                $rawDate = $employee->effective_date ?? "";
                if (!$rawDate || $rawDate === '0000-00-00' || trim($rawDate) === '') {
                    $employee['gross_salary']=0;
                    return $employee;
                }

                if ($effectiveDate && $effectiveDate->isSameMonth($startOfMonth)) {
                    $workedDays = $factoryDays - $effectiveDate->day + 1;
                } elseif ($effectiveDate && $effectiveDate->isAfter($endOfMonth)) {
                    $workedDays = 0;
                }

                $payableDays = max(0, $workedDays - $lwpDays);
                $prorationFactor = $payableDays / $factoryDays;

                $basic      = ($employee->basic_salary ?? 0) * $prorationFactor;
                $houseRent  = ($employee->house_rent_allowance ?? 0) * $prorationFactor;
                $medical    = ($employee->medical_allowance ?? 0) * $prorationFactor;
                $transport  = ($employee->transport_allowance ?? 0) * $prorationFactor;
                $otherAllow = ($employee->other_allowances ?? 0) * $prorationFactor;
            }

            if ($isBonusEnabled) {
                $bonusAmount = ($request->bonus_type === 'festival') ? ($employee->basic_salary ?? 0) : 0;
            }

            // TOTAL GROSS INCLUDING OT TK
            $grossSalary = $basic + $houseRent + $medical + $transport + $otherAllow + $bonusAmount + $otTk;

            $taxAmount = 0;
            if ($isSalaryEnabled && $employee->is_tax_deduction == 1) {
                $taxAmount = $this->calculateTax($grossSalary, $taxBrackets);
            }

            $netSalary = $grossSalary - $taxAmount;

            return [
                'id'                => $employee->id,
                'empId'             => $employee->empId,
                'name'              => $employee->person_name,
                'designation'       => $employee->designation_name ?? 'N/A',
                'department'        => $employee->department_name,
                'division'          => $employee->division_name,
                'lwp_days'          => $lwpDays,
                'payable_days'      => $isSalaryEnabled ? $payableDays : 0,
                'normal_hours'       => $work_Time,
                'ot_hours'          => $otHours,
                'ot_amount'         => $otTk,
                'basic_salary'      => round($basic, 2),
                'bonus_amount'      => round($bonusAmount, 2),
                'house_rent'        => round($houseRent, 2),
                'medical'           => round($medical, 2),
                'transport'         => round($transport, 2),
                'other_allowance'   => round($otherAllow, 2),
                'gross_salary'      => round($grossSalary, 2),
                'tax_deduction'     => round($taxAmount, 2),
                'net_salary'        => round($netSalary, 2),
                'is_salary_record'  => $isSalaryEnabled,
                'is_bonus_record'   => $isBonusEnabled,
            ];
        })->filter(fn($item) => isset($item['gross_salary']) && $item['gross_salary'] > 0)->values();

        return Inertia::render('payroll-ot/generate', [
            'payrollData' => $payrollData,
            'offices'      => Company::getByType('company'),
            'divisions'    => Division::getDivision(),
            'departments'  => Department::getDepartment(),
            'designations' => InfoMaster::getByType('designation'),
        ]);
    }

    /**
     * UPDATED CORE LOGIC: Dynamic Office Hours, Lunch Deduction, and OT TK
     */
    private function calculateWorkedHours($employeeId, $basicSalary, $startDate, $endDate)
    {
        $attendances = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('add_time', [$startDate, $endDate])
            ->get();

        $totalNormalHours = 0;
        $totalOvertimeHours = 0;

        // Define Office Shift (9 AM - 6 PM)
        $officeStartStr = "09:00:00";
        $officeEndStr   = "18:00:00";

        foreach ($attendances as $record) {
            // Updated to use your specific column names: emp_in_time and emp_out_time
            if (!$record->emp_in_time || !$record->emp_out_time) continue;

            $in = \Carbon\Carbon::parse($record->emp_in_time);
            $out = \Carbon\Carbon::parse($record->emp_out_time);
            $shiftStart = $in->copy()->setTimeFromTimeString($officeStartStr);
            $shiftEnd   = $in->copy()->setTimeFromTimeString($officeEndStr);

            $totalPresenceMinutes = $in->diffInMinutes($out);

            // 1. DEDUCT 1 HOUR LUNCH (If presence > 5 hours / 300 mins)
            $deductLunch = ($totalPresenceMinutes > 300);

            // 2. NORMAL HOURS (Intersection of Presence and Shift Window)
            $effectiveNormalIn  = $in->isBefore($shiftStart) ? $shiftStart : $in;
            $effectiveNormalOut = $out->isAfter($shiftEnd)   ? $shiftEnd   : $out;

            if ($effectiveNormalOut->isAfter($effectiveNormalIn)) {
                $normalMinutes = $effectiveNormalIn->diffInMinutes($effectiveNormalOut);
                if ($deductLunch) $normalMinutes -= 60;

                // Convert to hours and add to total
                $totalNormalHours += max(0, $normalMinutes / 60);
            }

            // 3. OVERTIME (Any time after 6 PM)
            if ($out->isAfter($shiftEnd)) {
                $otMinutes = $shiftEnd->diffInMinutes($out);
                $totalOvertimeHours += ($otMinutes / 60);
            }
        }

        // 4. OT TK CALCULATION
        // Hourly Rate = (Basic / 26 days / 8 hours)
        $hourlyRate = ($basicSalary > 0) ? ($basicSalary / 30 / 8) : 0;

        // Apply 2.0x Multiplier for OT
        $otTk = round($totalOvertimeHours * ($hourlyRate * 2), 2);

        // RETURN WITH ROUNDING to 2 decimal places to fix the 192.26666... issue
        return [
            'normal' => round($totalNormalHours, 2),
            'ot'     => round($totalOvertimeHours, 2),
            'ot_tk'  => $otTk
        ];
    }
    // --- REMAINDER OF BASE STRUCTURE (KEEPING ALL EXISTING METHODS) ---

    public function storeBatch(Request $request)
    {
        $payrollMonthStr = $request->payrollMonth . '-01';
        $payrollData = collect($request->payroll_data);
        $currentUser = \Auth::id();

        $existingEmpIds = \DB::table('payrolls')
            ->whereIn('emp_id', $payrollData->pluck('id'))
            ->where('month', $payrollMonthStr)
            ->pluck('emp_id')
            ->toArray();

        $newPayrollEntries = $payrollData->filter(fn($item) => !in_array($item['id'], $existingEmpIds));

        if ($newPayrollEntries->isEmpty()) {
            return redirect()->back()->withErrors(['error' => "Process Stopped: Duplicates found for " . \Carbon\Carbon::parse($payrollMonthStr)->format('F Y')]);
        }

        $totalStaff = $newPayrollEntries->count();
        $totalGross = $newPayrollEntries->sum('gross_salary');
        $totalNet   = $newPayrollEntries->sum('net_salary');

        \DB::beginTransaction();
        try {
            $batchId = \DB::table('payroll_batches')->insertGetId([
                'payroll_month'          => $payrollMonthStr,
                'com_id'                 => 1,
                'com_name'               => $newPayrollEntries->first()['com_name'] ?? 'Company',
                'status'                 => $request->postOption,
                'is_locked'              => $request->postOption === 'approved' ? 1 : 0,
                'total_staff'            => $totalStaff,
                'total_gross_amount'     => $totalGross,
                'total_payable_amount'   => $totalNet,
                'total_net_disbursement' => $totalNet,
                'prepared_by'            => $currentUser,
                'prepared_date'          => now(),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            foreach ($newPayrollEntries as $data) {
                \DB::table('payrolls')->insert([
                    'batch_id'                => $batchId,
                    'month'                   => $payrollMonthStr,
                    'emp_id'                  => $data['id'],
                    'emp_name'                => $data['name'],
                    'gross_salary_calculated' => $data['gross_salary'],
                    'basic_salary'            => $data['basic_salary'],
                    'house_rent'              => $data['house_rent'] ?? 0,
                    'medical_allowance'       => $data['medical'] ?? 0,
                    'transport_allowance'     => $data['transport'] ?? 0,
                    'other_benefits'          => $data['other_allowance'] ?? 0,
                    'bonus'                   => $data['bonus_amount'] ?? 0,
                    'tax_deduction'           => $data['tax_deduction'] ?? 0,
                    'bank_amount'             => $data['net_salary'],
                    'department_name'         => $data['department'] ?? 'N/A',
                    'entryby'                 => $currentUser,
                    'entrytime'               => now(),
                    'created_at'              => now(),
                ]);
            }
            \DB::commit();
            return redirect()->route('payroll.index')->with('success', "Batch #$batchId created.");
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function getEmployeeLwpDays($employeeId, $startDate, $endDate)
    {
        return \DB::table('leave_request_details')
            ->where('employee_id', $employeeId)
            ->where('leave_reason', 'LWP (Leave Without Pay)')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate]);
            })
            ->sum('total_days');
    }

    private function calculateTax($gross, $brackets)
    {
        foreach ($brackets as $bracket) {
            if ($gross >= $bracket['min'] && $gross <= $bracket['max']) {
                return $gross * $bracket['rate'] / 100;
            }
        }
        return 0;
    }

    private function getTaxBrackets() { return [['min' => 0, 'max' => 3000, 'rate' => 5], ['min' => 3001, 'max' => 6000, 'rate' => 10], ['min' => 6001, 'max' => 999999, 'rate' => 15]]; }
    private function getIndianStatutoryOptions() { return [['value' => 'pf', 'label' => 'PF', 'rate' => 12]]; }

    public function approve($id) {
        DB::beginTransaction();
        $batch = PayrollBatch::findOrFail($id);
        $batch->update(['status' => 'approved', 'is_locked' => true]);
        DB::table('payrolls')->where('batch_id', $id)->update(['locked' => 1]);
        DB::commit(); return back();
    }

    public function destroy($id) {
        $batch = PayrollBatch::findOrFail($id);
        if($batch->is_locked) return back()->withErrors(['error' => 'Locked']);
        DB::table('payrolls')->where('batch_id', $id)->delete();
        $batch->delete(); return redirect()->route('payroll.index');
    }
}
