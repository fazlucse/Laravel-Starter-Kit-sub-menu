<?php

namespace App\Http\Controllers;

use App\Traits\LogsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Employee; // Assuming you have an Employee model
use App\Models\PayrollBatch; // Assuming you have an Employee model
use App\Models\Payroll; // Assuming you have an Employee model
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of payroll batches (History)
     */
    /**
     * Display a listing of payroll batches (History)
     */
    public function index(Request $request)
    {
        // 1. Handle dynamic per_page selection
        $perPage = (int) $request->query('per_page', 10);
        $search = $request->input('search');

        // 2. Query Payroll Batches with dynamic filters
        $history = \App\Models\PayrollBatch::query()
            ->when($search, function ($query, $search) {
                $query->where('com_name', 'like', "%{$search}%")
                    ->orWhere('payroll_month', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderByDesc('payroll_month')
            ->orderByDesc('id') // Secondary sort to keep latest batches on top
            ->paginate($perPage)
            ->withQueryString();

        // 3. Fetch Departments and Offices for the generation form
        $departments = \App\Models\Employee::distinct()
            ->pluck('department_name')
            ->filter()
            ->values();

        $offices = \App\Models\Employee::distinct()
            ->pluck('company_name')
            ->filter()
            ->values();

        return Inertia::render('payroll/payroll', [
            'payrollHistory' => $history,
            'departments'    => $departments,
            'offices'        => $offices,
            'filters'        => $request->only(['search', 'per_page']),
        ]);
    }
    /**
     * Show the payroll generation form page
     */
    public function create()
    {
        // You can fetch departments/offices dynamically if needed
        $departments = Employee::distinct()->pluck('department_name')->filter()->values();
        $offices = Employee::distinct()->pluck('company_name')->filter()->values();
        $divisions = Employee::distinct()->pluck('division_name')->filter()->values();

        return Inertia::render('payroll/generate', [
            'departments' => $departments,
            'divisions' => $divisions,
            'offices' => $offices,
            'indianPostOptions' => $this->getIndianStatutoryOptions(),
            'taxBrackets' => $this->getTaxBrackets(),
        ]);
    }

    /**
     * Display the specific payroll batch details.
     */
    public function show($id)
    {
        // 1. Fetch the Batch Header
        $batch = \DB::table('payroll_batches')
            ->leftJoin('users', 'payroll_batches.prepared_by', '=', 'users.id')
            ->select('payroll_batches.*', 'users.name as prepared_by_name')
            ->where('payroll_batches.id', $id)
            ->first();

        if (!$batch) {
            return redirect()->route('payroll.index')->withErrors(['error' => 'Payroll batch not found.']);
        }

        // 2. Fetch all individual employee records for this batch
        $items = \DB::table('payrolls')
            ->where('batch_id', $id)
            ->orderBy('emp_name', 'asc')
            ->get();

        // 3. Render the show.vue page
        return Inertia::render('payroll/show', [
            'batch' => $batch,
            'items' => $items
        ]);
    }

    /**
     * Generate and STORE payroll batch and individual records
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department'   => 'nullable|string',
            'office'       => 'nullable|string',
            'payrollMonth' => 'required|date_format:Y-m',
            'postOption'   => 'required|in:draft,posted',
            'approvedBy'   => 'nullable|string',
        ]);

        // 1. Fetch relevant employees
        $query = Employee::query();
        if ($request->filled('department')) $query->where('department_name', $request->department);
        if ($request->filled('office')) $query->where('company_name', $request->office);

        $employees = $query->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'No employees found for selection.']);
        }

        return DB::transaction(function () use ($request, $employees) {
            // 2. Create the Batch Header
            $batch = PayrollBatch::create([
                'payroll_month' => Carbon::parse($request->payrollMonth)->startOfMonth(),
                'com_id'        => 0, // Map to your company ID logic
                'com_name'      => $request->office ?? 'All Offices',
                'status'        => $request->postOption,
                'total_staff'   => $employees->count(),
                'prepared_by'   => auth()->id() ?? 1,
                'prepared_date' => now(),
            ]);

            $batchGross = 0;
            $batchNet = 0;
            $batchPayable = 0;

            // 3. Process each employee using your DDL logic
            foreach ($employees as $emp) {
                // Calculation Logic
                $basic = $emp->baseSalary;
                $bonus = 0; // Fetch from bonus table if exists
                $gross = $basic + $bonus;

                // Statutory Deductions (example)
                $tax = $this->calculateTax($gross, $this->getTaxBrackets());
                $pf = $basic * 0.07; // 7% PF logic from your DDL

                $net = $gross - ($tax + $pf);

                // Insert into Sub-table (payroll_tbl)
                Payroll::create([
                    'batch_id' => $batch->id,
                    'hr_ref'   => $emp->id,
                    'month'    => $batch->payroll_month,
                    'emp_id'   => $emp->employee_id,
                    'emp_name' => $emp->person_name,
                    'basic_salary' => $basic,
                    'gross_salary_calculated' => $gross,
                    'tax_deduction' => $tax,
                    'pf_deduction' => $pf,
                    'bank_amount' => $net,
                    'com_id' => $emp->company_id ?? 1,
                    'com_name' => $emp->company_name,
                    'department_name' => $emp->department_name,
                    'entryby' => auth()->id() ?? 1,
                    'entrytime' => now(),
                    'exchange_date' => now(),
                    // Add other DDL fields as needed...
                ]);

                $batchGross += $gross;
                $batchNet += $net;
            }

            // 4. Update Batch with calculated totals
            $batch->update([
                'total_gross_amount'      => $batchGross,
                'total_net_disbursement'  => $batchNet,
                'total_payable_amount'    => $batchGross, // Usually Gross + Employer PF
            ]);

            return redirect()->back()->with('success', 'Payroll Batch Generated Successfully');
        });
    }
    /**
     * Generate and display payroll report
     */
    public function generate2(Request $request)
    {
        $validated = $request->validate([
            'department'        => 'nullable|string',
            'office'            => 'nullable|string',
            'payrollDate'       => 'required|date',
            'payrollMonth'      => 'required|date_format:Y-m',
            'postOption'        => 'required|in:draft,posted,approved',
            'indianComponents'  => 'nullable|array',
        ]);

        $query = Employee::query();
        if ($request->filled('department')) $query->where('department_name', $request->department);
        if ($request->filled('office')) $query->where('company_name', $request->office);

        $employees = $query->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->withErrors(['employees' => 'No employees found']);
        }

        // --- FACTORY CALCULATION SETTINGS ---
        $payrollMonthStr = $request->payrollMonth;
        $startOfMonth = \Carbon\Carbon::parse($payrollMonthStr)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($payrollMonthStr)->endOfMonth();

        // Hardcoded to 30 for Factory standard
        $factoryDays = 30;

        $bonuses = $this->getSampleBonuses();
        $deductions = $this->getSampleDeductions();
        $taxBrackets = $this->getTaxBrackets();

        $payrollData = $employees->map(function ($employee) use ($bonuses, $deductions, $taxBrackets, $request, $startOfMonth, $endOfMonth, $factoryDays) {

            // 1. FACTORY PRORATION LOGIC
            $effectiveDate = $employee->effective_date ? \Carbon\Carbon::parse($employee->effective_date) : null;
            $prorationFactor = 1.0;
            $workedDays = $factoryDays;

            if ($effectiveDate && $effectiveDate->isSameMonth($startOfMonth)) {
                // Formula: 30 - Start Day + 1
                $workedDays = $factoryDays - $effectiveDate->day + 1;
                // Guard: ensure days don't exceed 30 (for months with 28/29 days) or drop below 0
                $workedDays = max(0, min($factoryDays, $workedDays));
                $prorationFactor = $workedDays / $factoryDays;
            }
            elseif ($effectiveDate && $effectiveDate->isAfter($endOfMonth)) {
                $prorationFactor = 0;
                $workedDays = 0;
            }

            // 2. APPLY PRORATION TO EARNINGS
            $basic      = ($employee->basic_salary ?? 0) * $prorationFactor;
            $houseRent  = ($employee->house_rent_allowance ?? 0) * $prorationFactor;
            $medical    = ($employee->medical_allowance ?? 0) * $prorationFactor;
            $transport  = ($employee->transport_allowance ?? 0) * $prorationFactor;
            $otherAllow = ($employee->other_allowances ?? 0) * $prorationFactor;

            $empBonuses = collect($bonuses)->where('empId', $employee->empId)->sum('amount');
            $grossSalary = $basic + $houseRent + $medical + $transport + $otherAllow + $empBonuses;

            // 3. CONDITIONAL TAX DEDUCTION
            $taxAmount = 0;
            if ($employee->is_tax_deduction == 1) {
                $taxAmount = $this->calculateTax($grossSalary, $taxBrackets);
            }

            // 4. STATUTORY & EXTERNAL DEDUCTIONS
            $indianDeductions = $this->calculateIndianDeductions(
                $grossSalary,
                $basic,
                $request->input('indianComponents', []),
                $employee->empId
            );
            $totalIndianDeductions = collect($indianDeductions)->sum();
            $empDeductions = collect($deductions)->where('empId', $employee->empId)->sum('amount');

            // 5. FINAL NET CALCULATION
            $netSalary = $grossSalary - $taxAmount - $totalIndianDeductions - $empDeductions;

            return [
                'id'                => $employee->id,
                'empId'             => $employee->empId,
                'name'              => $employee->person_name,
                'designation'       => $employee->designation_name ?? 'N/A',
                'department'        => $employee->department_name,
                'division'          => $employee->division_name,

                // Factory Info
                'worked_days'       => $workedDays,
                'total_days'        => $factoryDays,
                'is_prorated'       => $prorationFactor < 1,

                // Earnings
                'basic_salary'      => $basic,
                'house_rent'        => $houseRent,
                'medical'           => $medical,
                'transport'         => $transport,
                'other_allowance'   => $otherAllow + $empBonuses,

                // Deductions
                'tax_deduction'     => $taxAmount,
                'pf_deduction'      => $totalIndianDeductions,
                'other_deduction'   => $empDeductions,

                // Totals
                'gross_salary'      => $grossSalary,
                'net_salary'        => $netSalary,
            ];
        })->filter(function ($item) {
            return $item['net_salary'] > 0;
        })->values();

        $departments = Employee::distinct()->pluck('department_name')->filter()->values();
        $divisions = Employee::distinct()->pluck('division_name')->filter()->values();
        $offices = Employee::distinct()->pluck('company_name')->filter()->values();
        return Inertia::render('payroll/generate', [
            'payrollData' => $payrollData,
            'form' => [
                'department' => $request->department,
                'office' => $request->office,
                'payrollDate' => $request->payrollDate,
                'payrollMonth' => $request->payrollMonth,
            ],
            'departments' => $departments,
            'divisions' => $divisions,
            'offices' => $offices,
        ]);
    }
    public function generate(Request $request)
    {
        // 1. VALIDATION
        $validated = $request->validate([
            'is_salary'      => 'required|boolean',
            'is_bonus'       => 'required|boolean',
            'is_individual'  => 'nullable|boolean',
            'employee_search'=> 'nullable|string',
            'department'     => 'nullable|string',
            'division'       => 'nullable|string',
            'office'         => 'nullable|string',
            'payrollMonth'   => 'required|date_format:Y-m',
            'bonus_type'     => 'nullable|string',
            'bonus_date'     => 'nullable|date',
            'postOption'     => 'required|in:draft,posted,approved',
        ]);

        // 2. QUERY EMPLOYEES
        $query = Employee::query();
        if ($request->boolean('is_individual') && $request->filled('employee_search')) {
            $query->where(function($q) use ($request) {
                $q->where('empId', $request->employee_search)
                    ->orWhere('person_name', 'like', '%' . $request->employee_search . '%');
            });
        } else {
            if ($request->filled('department')) $query->where('department_name', $request->department);
            if ($request->filled('division')) $query->where('division_name', $request->division);
            if ($request->filled('office')) $query->where('company_name', $request->office);
        }

        $employees = $query->get();

        // 3. CALCULATION SETTINGS
        $isSalaryEnabled = $request->boolean('is_salary');
        $isBonusEnabled  = $request->boolean('is_bonus');
        $factoryDays     = 30; // Factory Standard
        $payrollMonthStr = $request->payrollMonth;
        $startOfMonth    = \Carbon\Carbon::parse($payrollMonthStr)->startOfMonth();
        $endOfMonth      = \Carbon\Carbon::parse($payrollMonthStr)->endOfMonth();

        $taxBrackets     = $this->getTaxBrackets();

//        $standardWorkHoursPerDay = 8; // 9am-6pm minus 1hr lunch
//        $totalExpectedHoursMonth = $factoryDays * $standardWorkHoursPerDay; // 240 hours
//
        // 4. PROCESS PAYROLL DATA
        $payrollData = $employees->map(function ($employee) use ($request, $startOfMonth, $endOfMonth, $factoryDays, $isSalaryEnabled, $isBonusEnabled, $taxBrackets) {

            // --- INITIALIZE VARIABLES ---
            $basic = 0; $houseRent = 0; $medical = 0; $transport = 0; $otherAllow = 0;
            $bonusAmount = 0;
            $lwpDays = 0;
            $workedDays = $factoryDays;

            // --- SALARY LOGIC (Prorated for Joiners + LWP) ---
            if ($isSalaryEnabled) {

//                $hoursData = $this->calculateWorkedHours($employee->id, $startOfMonth, $endOfMonth);
//                $prorationFactor = $hoursData['normal'] / $totalExpectedHoursMonth;
                // Calling your custom LWP check function
                // Replace 'getEmployeeLwpDays' with your actual function name
                $lwpDays = $this->getEmployeeLwpDays($employee->id, $startOfMonth, $endOfMonth);

                // Handle New Joiner Proration
                $effectiveDate = $employee->effective_date ? \Carbon\Carbon::parse($employee->effective_date) : null;
                $rawDate = $employee->effective_date ?? "";
                if (!$rawDate || $rawDate === '0000-00-00' || trim($rawDate) === '') {
                    $employee['gross_salary']=0;
                    return  $employee; // This person is completely skipped from payroll
                }
                if ($effectiveDate && $effectiveDate->isSameMonth($startOfMonth)) {
                    $workedDays = $factoryDays - $effectiveDate->day + 1;
                } elseif ($effectiveDate && $effectiveDate->isAfter($endOfMonth)) {
                    $workedDays = 0;
                }

                // Final Payable Days (Worked - Unpaid)
                $payableDays = max(0, $workedDays - $lwpDays);
                $prorationFactor = $payableDays / $factoryDays;

                $basic      = ($employee->basic_salary ?? 0) * $prorationFactor;
                $houseRent  = ($employee->house_rent_allowance ?? 0) * $prorationFactor;
                $medical    = ($employee->medical_allowance ?? 0) * $prorationFactor;
                $transport  = ($employee->transport_allowance ?? 0) * $prorationFactor;
                $otherAllow = ($employee->other_allowances ?? 0) * $prorationFactor;
            }

            // --- BONUS LOGIC ---
            if ($isBonusEnabled) {
                if ($request->bonus_type === 'festival') {
                    // Usually festival bonus is 100% of basic, unaffected by LWP
                    $bonusAmount = $employee->basic_salary ?? 0;
                } else {
                    $bonusAmount = 0; // Editable in Vue for other types
                }
            }
            // --- TOTALS ---
            $grossSalary = $basic + $houseRent + $medical + $transport + $otherAllow + $bonusAmount;
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

                // Attendance Summary
                'lwp_days'          => $lwpDays,
                'payable_days'      => $isSalaryEnabled ? $payableDays : 0,

                // Values
                'basic_salary'      => round($basic, 2),
                'bonus_amount'      => round($bonusAmount, 2),
                'house_rent'        => round($houseRent, 2),
                'medical'           => round($medical, 2),
                'transport'         => round($transport, 2),
                'other_allowance'   => round($otherAllow, 2),

                // Totals
                'gross_salary'      => round($grossSalary, 2),
                'tax_deduction'     => round($taxAmount, 2),
                'net_salary'        => round($netSalary, 2),

                // Meta Flags
                'is_salary_record'  => $isSalaryEnabled,
                'is_bonus_record'   => $isBonusEnabled,
            ];
        })->filter(fn($item) => $item['gross_salary'] > 0)->values();

        return Inertia::render('payroll/generate', [
            'payrollData' => $payrollData,
            'departments' => Employee::distinct()->pluck('department_name')->filter()->values(),
            'divisions'   => Employee::distinct()->pluck('division_name')->filter()->values(),
            'offices'     => Employee::distinct()->pluck('company_name')->filter()->values(),
        ]);
    }
    /**
     * Download payroll as CSV
     */
    public function export(Request $request)
    {
        // You can reuse most of the generate logic here
        // ... implement CSV generation (using Laravel Excel or simple CSV)
        // Return response()->streamDownload(...)
    }

    /**
     * Update payroll status (draft → posted → approved)
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:draft,posted,approved',
            // 'payroll_id' => 'required|exists:payrolls,id'  // if you store payrolls
        ]);

        // In real application:
        // $payroll = Payroll::findOrFail($request->payroll_id);
        // $payroll->update(['status' => $request->status]);

        return redirect()->back()->with('success', "Payroll status updated to {$request->status}");
    }

    // ────────────────────────────────────────────────────────────────────────────────
    // Helper Methods
    // ────────────────────────────────────────────────────────────────────────────────

    private function getIndianStatutoryOptions()
    {
        return [
            ['value' => 'pf', 'label' => 'PF (Provident Fund)', 'rate' => 12],
            ['value' => 'esi', 'label' => 'ESI (Employee State Insurance)', 'rate' => 0.75],
            ['value' => 'tds', 'label' => 'TDS (Tax Deducted at Source)', 'rate' => 0],
            ['value' => 'professionalTax', 'label' => 'Professional Tax', 'rate' => 200, 'fixed' => true],
            ['value' => 'lwf', 'label' => 'LWF (Labour Welfare Fund)', 'rate' => 20, 'fixed' => true],
            ['value' => 'gratuity', 'label' => 'Gratuity', 'rate' => 4.81],
            ['value' => 'bonus', 'label' => 'Performance Bonus (Additional)', 'rate' => 8.33],
        ];
    }

    private function getTaxBrackets()
    {
        return [
            ['min' => 0,     'max' => 3000,   'rate' => 5],
            ['min' => 3001,  'max' => 6000,   'rate' => 10],
            ['min' => 6001,  'max' => 999999, 'rate' => 15],
        ];
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

    private function calculateIndianDeductions($gross, $basic, array $selected, $empId)
    {
        $options = collect($this->getIndianStatutoryOptions())->keyBy('value');
        $result = [];

        foreach ($selected as $key) {
            if (!$options->has($key)) continue;

            $option = $options[$key];

            if (isset($option['fixed']) && $option['fixed']) {
                $result[$key] = $option['rate'];
            } elseif ($key === 'tds') {
                $result[$key] = $this->calculateTax($gross, $this->getTaxBrackets());
            } else {
                $result[$key] = $basic * $option['rate'] / 100;
            }
        }

        return $result;
    }

    // Sample data (in real app → database)
    private function getSampleBonuses()
    {
        return [
            ['empId' => 'E001', 'type' => 'Performance', 'amount' => 500],
            ['empId' => 'E002', 'type' => 'Attendance', 'amount' => 300],
            ['empId' => 'E004', 'type' => 'Performance', 'amount' => 600],
        ];
    }

    private function getSampleDeductions()
    {
        return [
            ['empId' => 'E001', 'type' => 'Insurance', 'amount' => 200],
            ['empId' => 'E002', 'type' => 'Insurance', 'amount' => 200],
            ['empId' => 'E003', 'type' => 'Loan', 'amount' => 150],
            ['empId' => 'E004', 'type' => 'Insurance', 'amount' => 200],
        ];
    }
    /**
     * Approve a payroll batch and lock all sub-records
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $batch = PayrollBatch::findOrFail($id);

            if ($batch->status === 'approved') {
                return redirect()->back()->withErrors(['error' => 'Batch is already approved.']);
            }

            // 1. Update Batch Header
            $batch->update([
                'status'        => 'approved',
                'approved_by'   => auth()->id(),
                'approved_date' => now(),
                'is_locked'     => true,
            ]);

            // 2. Lock all individual staff records (Sub-table)
            // This mirrors your DDL requirement: KEY `locked` (`locked`)
            $batch->items()->update(['locked' => 1]);

            DB::commit();
            return redirect()->back()->with('success', 'Payroll Batch approved and locked.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Approval failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a draft batch and all associated payroll records.
     */
    public function destroy($id)
    {
        // 1. Find the batch or fail
        $batch = \App\Models\PayrollBatch::findOrFail($id);

        // 2. Safety Check: Only allow deletion of unlocked/draft batches
        // In your DDL, is_locked=1 means it's finalized
        if ($batch->is_locked || $batch->status === 'approved') {
            return redirect()->back()->withErrors([
                'error' => 'Security Violation: Cannot delete an approved or locked payroll batch.'
            ]);
        }

        \DB::beginTransaction();
        try {
            LogsActions::logDelete($batch, $request->comments ?? 'Payroll record deleted');

            \DB::table('bonuses')
                ->where('payroll_id', $id)
                ->delete();
            // 3. Delete individual employee records linked to this batch
            \DB::table('payrolls')->where('batch_id', $id)->delete();

            // 4. Delete the batch header
            $batch->delete();

            \DB::commit();
            return redirect()->route('payroll.index')->with('success', 'Payroll batch and associated records deleted successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Deletion failed: ' . $e->getMessage()
            ]);
        }
    }
    public function generatePaySlip(Request $request, $id)
    {
        $payroll = \DB::table('payrolls')->where('id', $id)->first();
        if (!$payroll) {
            return back()->with('error', 'Payroll record not found.');
        }

        // Earnings from Database Columns
        $basic   = $payroll->basic_salary ?? 0;
        $house   = $payroll->house_rent ?? 0;
        $med     = $payroll->medical_allowance ?? 0;
        $trans   = $payroll->transport_allowance ?? 0;
        $other   = $payroll->other_benefits ?? 0;
        $bonus   = $payroll->bonus ?? 0;

        // Arrears (if you have it in pay_rolls table, add it here)
        $arrear  = $payroll->arrear ?? 0;

        // Calculate Gross
        $gross = $basic + $house + $med + $trans + $other + $bonus + $arrear;

        // Deductions (Summing up all your specific deduction columns)
        $totalDeductions =
            ($payroll->tax_deduction ?? 0) +
            ($payroll->pf_deduction ?? 0) +
            ($payroll->loan_deduction ?? 0) +
            ($payroll->pf_loan_deduction ?? 0) +
            ($payroll->mobile_deduction ?? 0) +
            ($payroll->training_deduction ?? 0) +
            ($payroll->lwp_deduction ?? 0) +
            ($payroll->canteen_bill_deduction ?? 0) +
            ($payroll->cafe_bill_deduction ?? 0) +
            ($payroll->ips_deduction ?? 0) +
            ($payroll->ipd_deduction ?? 0) +
            ($payroll->lb_deduction ?? 0) +
            ($payroll->others_deduction ?? 0);

        // Net Salary
        $net = $gross - $totalDeductions;

        return view('payroll.payslip_print', [
            'payroll'    => $payroll, // Passing the whole record for name, id, dept, etc.
            'month'      => \Carbon\Carbon::parse($payroll->month)->format('F, Y'),
            'gross'      => $gross,
            'deductions' => $totalDeductions,
            'net'        => $net,
            'date'       => now()->format('d/m/Y')
        ]);
    }
    public function storeBatchbk(Request $request)
    {
        $payrollMonthStr = $request->payrollMonth . '-01';
        $payrollData = collect($request->payroll_data);
        $currentUser = \Auth::id();

        // 1. FILTER OUT DUPLICATES
        // Get IDs of people who ALREADY have payroll for this month
        $existingEmpIds = \DB::table('payrolls')
            ->whereIn('emp_id', $payrollData->pluck('id'))
            ->where('month', $payrollMonthStr)
            ->pluck('emp_id')
            ->toArray();

        // Only keep employees who are NOT in the existing list
        $newPayrollEntries = $payrollData->filter(function ($item) use ($existingEmpIds) {
            return !in_array($item['id'], $existingEmpIds);
        });

        // 2. CHECK IF ANYONE IS LEFT TO PROCESS
        if ($newPayrollEntries->isEmpty()) {
            return redirect()->back()->withErrors([
                'error' => "Process Stopped: All selected employees already have payroll generated for " . Carbon::parse($payrollMonthStr)->format('F Y') . "."
            ]);
        }

        // 3. Calculate Totals ONLY for the new entries
        $totalStaff = $newPayrollEntries->count();
        $totalGross = $newPayrollEntries->sum('gross_salary');
        $totalNet   = $newPayrollEntries->sum('net_salary');

        \DB::beginTransaction();
        try {
            // 4. Insert Batch Header
            $batchId = \DB::table('payroll_batches')->insertGetId([
                'payroll_month'          => $payrollMonthStr,
                'com_id'                 => 1,
                'com_name'               => $newPayrollEntries->first()['com_name'] ?? 'Company Name',
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

            // 5. Insert only the NEW entries
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
                    'bonus'                   => $data['totalBonus'] ?? 0,
                    'tax_deduction'           => $data['tax_deduction'] ?? 0,
                    'pf_deduction'            => $data['pf_deduction'] ?? 0,
                    'others_deduction'        => $data['other_deduction'] ?? 0,
                    'bank_amount'             => $data['net_salary'],
                    'cash_amount'             => 0,
                    'department_name'         => $data['department'],
                    'designation_name'        => $data['designation'] ?? 'N/A',
                    'division_name'           => $data['division'] ?? 'N/A',
                    'entryby'                 => $currentUser,
                    'entrytime'               => now(),
                    'locked'                  => $request->postOption === 'approved' ? 1 : 0,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
            }

            \DB::commit();

            $msg = "Success: Batch #$batchId created for $totalStaff employees.";
            if (count($existingEmpIds) > 0) {
                $msg .= " (" . count($existingEmpIds) . " duplicates were skipped).";
            }

            return redirect()->route('payroll.index')->with('success', $msg);

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Database Error: ' . $e->getMessage()]);
        }
    }
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

        $newPayrollEntries = $payrollData->filter(function ($item) use ($existingEmpIds) {
            return !in_array($item['id'], $existingEmpIds);
        });

        // Stop if everyone in the list has already been processed
        if ($newPayrollEntries->isEmpty()) {
            return redirect()->back()->withErrors([
                'error' => "Process Stopped: All selected employees already have payroll generated for " . \Carbon\Carbon::parse($payrollMonthStr)->format('F Y') . "."
            ]);
        }

        // 3. PRE-CALCULATE BATCH TOTALS
        $totalStaff = $newPayrollEntries->count();
        $totalGross = $newPayrollEntries->sum('gross_salary');
        $totalNet   = $newPayrollEntries->sum('net_salary');

        \DB::beginTransaction();
        try {
            $batchId = \DB::table('payroll_batches')->insertGetId([
                'payroll_month'          => $payrollMonthStr,
                'com_id'                 => 1, // Change this to $request->com_id if dynamic
                'com_name'               => $newPayrollEntries->first()['com_name'] ?? 'Your Company Name',
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

                $payrollId = \DB::table('payrolls')->insertGetId([
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
                    'bonus'                   => $data['bonus_amount'] ?? 0, // Total bonus stored for payslip
                    'tax_deduction'           => $data['tax_deduction'] ?? 0,
                    'pf_deduction'            => $data['pf_deduction'] ?? 0,
                    'others_deduction'        => $data['other_deduction'] ?? 0,
                    'bank_amount'             => $data['net_salary'],
                    'cash_amount'             => 0,
                    'department_name'         => $data['department'] ?? 'N/A',
                    'designation_name'        => $data['designation'] ?? 'N/A',
                    'division_name'           => $data['division'] ?? 'N/A',
                    'entryby'                 => $currentUser,
                    'entrytime'               => now(),
                    'locked'                  => $request->postOption === 'approved' ? 1 : 0,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
                if (isset($data['bonus_amount']) && $data['bonus_amount'] > 0) {
                    \DB::table('bonuses')->updateOrInsert(
                        [
                            'employee_id'   => $data['id'],
                            'payroll_month' => $request->payrollMonth, // Format: Y-m
                            'bonus_type'    => $data['bonus_type'] ?? 'Festival Bonus',
                        ],
                        [
                            'payroll_id'    => $batchId, // THE LINKING KEY
                            'remarks'       => $data['remarks'] ?? null,
                            'amount'        => $data['bonus_amount'],
                            'bonus_date'    => $data['bonus_date'] ?? $request->bonus_date, // Individual or global date
                            'is_paid'       => $request->postOption === 'approved' ? 1 : 0,
                            'processed_at'  => now(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]
                    );
                }
            }

            \DB::commit();

            $msg = "Success: Batch #$batchId created for $totalStaff employees.";
            if (count($existingEmpIds) > 0) {
                $msg .= " (" . count($existingEmpIds) . " duplicates were automatically skipped).";
            }

            return redirect()->route('payroll.index')->with('success', $msg);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Payroll Batch Store Failed: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Critical Database Error: ' . $e->getMessage()]);
        }
    }
    /**
     * Calculate total Leave Without Pay (LWP) days for a specific employee in a date range.
     */
    private function getEmployeeLwpDays($employeeId, $startDate, $endDate)
    {
        return \DB::table('leave_request_details')
            ->where('employee_id', $employeeId)
            ->where('leave_reason', 'LWP (Leave Without Pay)') // Matches the value: 'lwp'
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate]);
            })
            ->sum('total_days');
    }

    private function calculateWorkedHours($employeeId, $startDate, $endDate)
    {
        // Fetch attendance for the month
        // Assuming you have an Attendance model with 'clock_in' and 'clock_out' (datetime)
        $attendances = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('clock_in', [$startDate, $endDate])
            ->get();

        $totalNormalHours = 0;
        $totalOvertimeHours = 0;

        foreach ($attendances as $record) {
            if (!$record->clock_in || !$record->clock_out) continue;

            $in = \Carbon\Carbon::parse($record->clock_in);
            $out = \Carbon\Carbon::parse($record->clock_out);

            // Define Shift
            $shiftStart = $in->copy()->setTime(9, 0, 0);
            $shiftEnd   = $in->copy()->setTime(18, 0, 0); // 6 PM

            // 1. Calculate Total Presence
            $totalPresenceMinutes = $in->diffInMinutes($out);

            // 2. Deduct 1 hour lunch break (60 mins) if they worked more than 5 hours
            if ($totalPresenceMinutes > 300) {
                $totalPresenceMinutes -= 60;
            }

            // 3. Calculate Normal Hours (capped at 18:00)
            // If they stay later than 18:00, normal time stops at 18:00
            $effectiveNormalOut = $out->isAfter($shiftEnd) ? $shiftEnd : $out;
            $normalMinutes = max(0, $in->diffInMinutes($effectiveNormalOut));

            // Subtract lunch from normal minutes if applicable
            $actualNormalMins = ($totalPresenceMinutes < $normalMinutes) ? $totalPresenceMinutes : $normalMinutes;
            $totalNormalHours += ($actualNormalMins / 60);

            // 4. Calculate Overtime (Time after 18:00)
            if ($out->isAfter($shiftEnd)) {
                $totalOvertimeHours += ($shiftEnd->diffInMinutes($out) / 60);
            }
        }

        return [
            'normal' => $totalNormalHours,
            'ot'     => $totalOvertimeHours
        ];
    }
}
