<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PaySlip_{{ $employee->person_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .print-shadow-none { shadow: none; border: 1px solid #eee; }
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 p-2 sm:p-8">

<div class="max-w-4xl mx-auto mb-4 no-print flex justify-between items-center px-4">
    <span class="text-xs text-gray-500 italic font-medium">Space Optimized Layout - A4 Ready</span>
    <button onclick="window.print()" class="bg-slate-800 text-white px-4 py-1.5 rounded text-sm font-bold hover:bg-black transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg> Print
    </button>
</div>

<div class="max-w-4xl mx-auto bg-white p-6 sm:p-10 border border-gray-200 print-shadow-none shadow-sm relative">

    <div class="flex justify-between border-b-2 border-slate-900 pb-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 uppercase leading-none">Your Company Ltd.</h1>
            <p class="text-[10px] text-gray-600 mt-1 leading-tight">
                House #00, Road #00, Sector #00, Uttara, Dhaka-1230<br>
                Phone: +880 1XXXXXXXXX | Email: info@company.com.bd
            </p>
        </div>
        <div class="text-right">
            <h2 class="text-lg font-bold text-slate-500 uppercase tracking-tighter">Salary Advice</h2>
            <p class="text-xs font-black bg-slate-100 px-2 py-1 rounded mt-1 inline-block uppercase">{{ $month }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-x-10 gap-y-1 my-6 text-[11px] border-b border-gray-100 pb-4">
        <div class="space-y-1">
            <div class="flex justify-between"><span class="text-gray-500">Employee ID:</span> <span class="font-bold">{{ $employee->empId }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Name:</span> <span class="font-bold uppercase">{{ $employee->person_name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Designation:</span> <span class="font-medium">{{ $employee->designation_name }}</span></div>
        </div>
        <div class="space-y-1">
            <div class="flex justify-between"><span class="text-gray-500">Department:</span> <span class="font-medium">{{ $employee->department_name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Bank Name:</span> <span class="font-medium">{{ $employee->bank_name ?? 'N/A' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Account No:</span> <span class="font-mono font-bold">{{ $employee->account_number ?? 'N/A' }}</span></div>
        </div>
    </div>

    <div class="flex border border-slate-300 text-[11px]">
        <div class="w-1/2 border-r border-slate-300">
            <div class="bg-slate-50 border-b border-slate-300 p-1.5 font-bold text-center uppercase text-slate-700 tracking-wider">Earnings</div>
            <div class="p-3 space-y-2">
                <div class="flex justify-between"><span>Basic Salary</span> <span class="font-mono">{{ number_format($employee->basic_salary, 2) }}</span></div>
                <div class="flex justify-between"><span>Medical Allowance</span> <span class="font-mono">{{ number_format($employee->medical_allowance, 2) }}</span></div>
                <div class="flex justify-between"><span>House Rent</span> <span class="font-mono">{{ number_format($employee->house_rent_allowance, 2) }}</span></div>
                <div class="flex justify-between"><span>Transport</span> <span class="font-mono">{{ number_format($employee->transport_allowance, 2) }}</span></div>
                <div class="flex justify-between font-bold border-t border-dashed pt-2 mt-2 text-slate-900">
                    <span>Gross Earnings</span> <span>৳ {{ number_format($gross, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="w-1/2">
            <div class="bg-slate-50 border-b border-slate-300 p-1.5 font-bold text-center uppercase text-slate-700 tracking-wider">Deductions</div>
            <div class="p-3 space-y-2">
                <div class="flex justify-between"><span>PF Contribution</span> <span class="font-mono">{{ number_format($employee->pf_deduction, 2) }}</span></div>
                <div class="flex justify-between"><span>Income Tax</span> <span class="font-mono">{{ number_format($employee->tax_deduction ?? 0, 2) }}</span></div>
                <div class="flex justify-between"><span>Other Deductions</span> <span class="font-mono">{{ number_format($employee->other_deduction ?? 0, 2) }}</span></div>
                <div class="flex justify-between font-bold border-t border-dashed pt-2 mt-2 text-red-700">
                    <span>Total Deductions</span> <span>৳ {{ number_format($deductions, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 border border-slate-900 flex items-stretch">
        <div class="bg-slate-900 text-white px-4 flex items-center text-[10px] font-bold uppercase tracking-tighter">
            Net Payable
        </div>
        <div class="flex-1 flex justify-between items-center px-4 py-3 bg-slate-50">
            <span class="text-[9px] text-gray-500 font-medium italic">Amount in BDT: (Towards bank account {{ $employee->account_number }})</span>
            <span class="text-2xl font-black text-slate-900">৳ {{ number_format($net, 2) }}</span>
        </div>
    </div>

    <div class="mt-16 flex justify-between px-4">
        <div class="text-center">
            <div class="w-40 border-t border-slate-400"></div>
            <p class="text-[9px] font-bold uppercase mt-1 text-gray-600">Employee Signature</p>
        </div>
        <div class="text-center">
            <div class="w-40 border-t border-slate-400"></div>
            <p class="text-[9px] font-bold uppercase mt-1 text-gray-600">Authorized Signatory</p>
        </div>
    </div>

    <div class="mt-10 pt-4 border-t border-gray-100 flex justify-between items-center text-[8px] text-gray-400 uppercase tracking-widest">
        <span>Generated on: {{ $date }}</span>
        <span>System Generated - No Seal Required</span>
    </div>
</div>

</body>
</html>
