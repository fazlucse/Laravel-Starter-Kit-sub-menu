<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->input('search');

        $history = PayrollBatch::query()
            ->when($search, function ($query, $search) {
                $query->where('com_name', 'like', "%{$search}%")
                    ->orWhere('payroll_month', 'like', "%{$search}%");
            })
            ->orderByDesc('payroll_month')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('payroll/payroll', [
            'payrollHistory' => $history,
            'departments' => Employee::distinct()->pluck('department_name')->filter()->values(),
            'offices' => Employee::distinct()->pluck('company_name')->filter()->values(),
            'filters' => $request->only(['search']),
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

        return Inertia::render('payroll/generate', [
            'departments' => $departments,
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
    public function generate(Request $request)
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
        if ($request->filled('department')) $query->where('department', $request->department);
        if ($request->filled('office')) $query->where('office', $request->office);

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

        return Inertia::render('payroll/generate', [
            'payrollData' => $payrollData,
            'form' => [
                'department' => $request->department,
                'office' => $request->office,
                'payrollDate' => $request->payrollDate,
                'payrollMonth' => $request->payrollMonth,
            ]
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
     * Delete a draft batch
     */
    public function destroy($id)
    {
        $batch = PayrollBatch::findOrFail($id);

        if ($batch->is_locked) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete a locked/approved batch.']);
        }

        // This will automatically delete items in payroll_tbl due to onDelete('cascade')
        $batch->delete();

        return redirect()->back()->with('success', 'Payroll batch deleted.');
    }
    public function generatePaySlip(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Calculate Earnings
        $basic = $employee->basic_salary ?? 0;
        $med   = $employee->medical_allowance ?? 0;
        $house = $employee->house_rent_allowance ?? 0;
        $trans = $employee->transport_allowance ?? 0;
        $other = $employee->other_allowances ?? 0;

        // This is the variable the template is missing
        $gross = $basic + $med + $house + $trans + $other;

        // Calculate Deductions
        $pf    = $employee->pf_deduction ?? 0;
        $tax   = $employee->tax_deduction ?? 0;
        $ded   = $employee->other_deductions ?? 0;

        $totalDeductions = $pf + $tax + $ded;
        $net = $gross - $totalDeductions;

        return view('payroll.payslip_print', [
            'employee'   => $employee,
            'month'      => \Carbon\Carbon::parse($request->query('month'))->format('F, Y'),
            'gross'      => $gross, // <--- MUST MATCH THE BLADE VARIABLE $gross
            'deductions' => $totalDeductions,
            'net'        => $net,
            'date'       => now()->format('d/m/Y')
        ]);
    }
    public function storeBatch(Request $request)
    {
        $payrollMonthStr = $request->payrollMonth . '-01'; // Format: 2026-02-01
        $payrollData = collect($request->payroll_data);
        $currentUser = \Auth::id();

        // 1. Calculate Totals for the Batch Header
        $totalStaff = $payrollData->count();
        $totalGross = $payrollData->sum('gross_salary');
        $totalNet   = $payrollData->sum('net_salary');

        \DB::beginTransaction();
        try {
            // 2. Insert into payroll_batches (The Header)
            $batchId = \DB::table('payroll_batches')->insertGetId([
                'payroll_month'          => $payrollMonthStr,
                'com_id'                 => 1, // Change this to your actual Company ID logic
                'com_name'               => 'Your Company Ltd.',
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

            // 3. Insert into payrolls (The Individual Entries)
            foreach ($payrollData as $data) {
                $data['empId'] = $data['id'];
                $exists = \DB::table('payrolls')
                    ->where('emp_id', $data['empId'])
                    ->where('month', $payrollMonthStr)
                    ->exists();

                if ($exists) continue;

                \DB::table('payrolls')->insert([
                    'batch_id'                => $batchId, // Link to the header ID created above
                    'month'                   => $payrollMonthStr,
                    'emp_id'                  => $data['empId'],
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
            return redirect()->back()->with('success', 'Payroll Batch #' . $batchId . ' has been posted.');

        } catch (\Exception $e) {
            \DB::rollBack();
            // Return detailed error for debugging
            return redirect()->back()->withErrors(['error' => 'Posting failed: ' . $e->getMessage()]);
        }
    }
}
