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
            'preparedBy'        => 'nullable',
            'approvedBy'        => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'postOption'        => 'required|in:draft,posted,approved',
            'indianComponents'  => 'nullable|array',
            'indianComponents.*'=> 'string|in:pf,esi,tds,professionalTax,lwf,gratuity,bonus',
        ]);

        $query = Employee::query();

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        $employees = $query->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->withErrors(['employees' => 'No employees found matching the selected filters']);
        }

        // In real app you would fetch from DB
        // For demo we're using the same static data structure as in React version
        $bonuses = $this->getSampleBonuses();
        $deductions = $this->getSampleDeductions();
        $taxBrackets = $this->getTaxBrackets();

        $payrollData = $employees->map(function ($employee) use ($bonuses, $deductions, $taxBrackets, $request) {
            // 1. Gather Earnings
            $basic      = $employee->basic_salary ?? 0;
            $houseRent  = $employee->house_rent_allowance ?? 0;
            $medical    = $employee->medical_allowance ?? 0;
            $transport  = $employee->transport_allowance ?? 0;
            $otherAllow = $employee->other_allowances ?? 0;

            // External bonuses (from your getSampleBonuses method)
            $empBonuses = collect($bonuses)->where('empId', $employee->empId)->sum('amount');

            // Total Gross Salary
            $grossSalary = $basic + $houseRent + $medical + $transport + $otherAllow + $empBonuses;

            // 2. Gather Deductions
            $taxAmount = $this->calculateTax($grossSalary, $taxBrackets);

            // Indian statutory deductions (PF, ESI, etc.)
            $indianDeductions = $this->calculateIndianDeductions(
                $grossSalary,
                $basic,
                $request->input('indianComponents', []),
                $employee->empId
            );
            $totalIndianDeductions = collect($indianDeductions)->sum();

            // External deductions (from your getSampleDeductions method)
            $empDeductions = collect($deductions)->where('empId', $employee->empId)->sum('amount');

            // 3. Final Net Salary Calculation
            // Net = (Gross) - (Tax) - (Statutory/PF) - (Other Deductions)
            $netSalary = $grossSalary - $taxAmount - $totalIndianDeductions - $empDeductions;

            return [
                'id'                => $employee->id,
                'empId'             => $employee->empId,
                'name'              => $employee->person_name,
                'designation'       => $employee->designation_name ?? 'N/A',
                'department'        => $employee->department_name,
                'division'          => $employee->division_name,
                'office'            => $employee->office,

                // Earnings
                'basic_salary'      => $basic,
                'house_rent'        => $houseRent,
                'medical'           => $medical,
                'transport'         => $transport,
                'other_allowance'   => $otherAllow + $empBonuses,

                // Deductions
                'tax_deduction'     => $taxAmount,
                'pf_deduction'      => $totalIndianDeductions, // Or specifically $indianDeductions['pf'] if exists
                'other_deduction'   => $empDeductions,

                // Final Totals
                'gross_salary'      => $grossSalary,
                'net_salary'        => $netSalary,

                // Metadata for modal/details
                'bonusDetails'      => collect($bonuses)->where('empId', $employee->empId)->values(),
                'deductionDetails'  => collect($deductions)->where('empId', $employee->empId)->values(),
            ];
        })->values();

        return Inertia::render('payroll/generate', [
            'payrollData' => $payrollData,
            'form' => [
                'department' => $request->department,
                'office' => $request->office,
                'payrollDate' => $request->payrollDate,
                'payrollMonth' => $request->payrollMonth,
                'preparedBy' => Auth::id(),
                'approvedBy' => $request->approvedBy,
                'notes' => $request->notes,
            ],
            'postStatus' => $request->postOption,
            'selectedIndianOptions' => $request->input('indianComponents', []),
            'indianPostOptions' => $this->getIndianStatutoryOptions(),
            'mode' => 'view'
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
}
