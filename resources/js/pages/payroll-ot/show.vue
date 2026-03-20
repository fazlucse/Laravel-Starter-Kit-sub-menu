<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import {
    ArrowLeft, Printer, FileText,
    Building2, Calendar, Users
} from 'lucide-vue-next'

const props = defineProps<{
    batch: any,
    items: any[]
}>()

// Helper: Format Currency
const formatCurr = (value: number) => {
    if (!value || value === 0) return '—';
    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
    return `৳ ${formatted}`;
}

// Helper: Format Month
const formatMonth = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    });
}

const printBatch = () => {
    window.print();
}

const viewPaySlip = (empId: any) => {
    window.open(`/payroll/payslip/${empId}?month=${props.batch.payroll_month}`, '_blank');
}
</script>

<template>
    <AppLayout
        title="Batch Details"
        :breadcrumbs="[{ title: 'Payroll', href: '/payroll' }, { title: 'Batch Details' }]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)] bg-gray-50 dark:bg-gray-950">

            <div class="p-3 sm:p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm z-20">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <Link href="/payroll" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition">
                            <ArrowLeft class="w-5 h-5 text-gray-500" />
                        </Link>
                        <div>
                            <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                                Batch #{{ batch.id.toString().padStart(5, '0') }}
                            </h1>
                            <div class="flex flex-wrap gap-3 mt-0.5">
                                <span class="flex items-center gap-1 text-[9px] font-black text-blue-600 uppercase">
                                    <Calendar class="w-2.5 h-2.5" /> {{ formatMonth(batch.payroll_month) }}
                                </span>
                                <span class="flex items-center gap-1 text-[9px] font-black text-gray-500 uppercase">
                                    <Building2 class="w-2.5 h-2.5" /> {{ batch.com_name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span
                            :class="batch.is_locked ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200'"
                            class="px-3 py-1 rounded-full text-[9px] font-black uppercase border flex-1 sm:flex-none text-center"
                        >
                            {{ batch.status }}
                        </span>
                        <button @click="printBatch" class="bg-gray-900 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 text-[10px] font-black hover:bg-black transition flex-1 sm:flex-none uppercase tracking-widest">
                            <Printer class="w-3.5 h-3.5" /> Print Summary
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto p-3 sm:p-4 space-y-4">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Staff</p>
                        <p class="text-sm font-black dark:text-white">{{ batch.total_staff }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm border-l-4 border-l-blue-600">
                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Net Disbursement</p>
                        <p class="text-sm font-black text-blue-600">{{ formatCurr(batch.total_net_disbursement) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[9px] font-black text-red-500 uppercase tracking-widest mb-1">Total Deductions</p>
                        <p class="text-sm font-black text-red-500">{{ formatCurr(items.reduce((a, b) => a + (Number(b.tax_deduction) + Number(b.pf_deduction) + Number(b.others_deduction)), 0)) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Prepared By</p>
                        <p class="text-xs font-bold dark:text-white truncate">{{ batch.prepared_by_name || 'System Admin' }}</p>
                    </div>
                </div>

                <div class="lg:hidden space-y-3">
                    <div v-for="(emp, i) in items" :key="emp.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                        <div class="flex justify-between items-start border-b dark:border-gray-700 pb-2 mb-2">
                            <div>
                                <h3 class="text-xs font-black uppercase text-gray-900 dark:text-white">{{ emp.emp_name }}</h3>
                                <p class="text-[9px] text-gray-400 font-mono italic">{{ emp.department_name }}</p>
                            </div>
                            <button @click="viewPaySlip(emp.emp_id)" class="text-blue-600 p-1"><FileText class="w-4 h-4" /></button>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[10px]">
                            <div class="flex justify-between"><span class="text-gray-400">Basic:</span> <span class="font-bold">{{ formatCurr(emp.basic_salary) }}</span></div>
                            <div class="flex justify-between"><span class="text-blue-500">Bonus:</span> <span class="font-bold text-blue-600">{{ formatCurr(emp.bonus) }}</span></div>
                            <div class="flex justify-between"><span class="text-red-400">Tax:</span> <span class="font-bold text-red-500">{{ formatCurr(emp.tax_deduction) }}</span></div>
                            <div class="flex justify-between"><span class="text-red-400">PF:</span> <span class="font-bold text-red-500">{{ formatCurr(emp.pf_deduction) }}</span></div>
                            <div class="col-span-2 bg-slate-50 dark:bg-slate-900 p-2 mt-2 rounded flex justify-between items-center border border-slate-100 dark:border-slate-800">
                                <span class="font-black text-[9px] uppercase">Net Payable:</span>
                                <span class="font-black text-sm text-blue-700">{{ formatCurr(emp.bank_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden h-full">
                    <div class="overflow-auto h-full">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 border-collapse text-[10px]">
                            <thead class="sticky top-0 bg-gray-100 dark:bg-gray-900 z-30 shadow-sm font-black uppercase tracking-widest text-gray-500">
                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <th class="p-2 text-center w-8">SL</th>
                                <th class="p-2 text-left min-w-[150px]">Staff Information</th>
                                <th class="p-2 text-right">Basic</th>
                                <th class="p-2 text-right">House</th>
                                <th class="p-2 text-right">Med.</th>
                                <th class="p-2 text-right">Trans.</th>
                                <th class="p-2 text-right text-blue-600">Bonus</th>
                                <th class="p-2 text-right text-red-500">Tax</th>
                                <th class="p-2 text-right text-red-500">PF</th>
                                <th class="p-2 text-right text-red-500">Oth.Ded</th>
                                <th class="p-2 text-right bg-blue-600 text-white">Net Pay</th>
                                <th class="p-2 text-center w-12">Slip</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="(emp, i) in items" :key="emp.id" class="hover:bg-blue-50/30 dark:hover:bg-gray-700/50 transition">
                                <td class="p-2 text-center font-bold text-gray-400 border-r">{{ i + 1 }}</td>
                                <td class="p-2">
                                    <div class="font-black uppercase leading-tight dark:text-white">{{ emp.emp_name }}</div>
                                    <div class="text-[9px] text-gray-400 font-mono">{{ emp.emp_id }}</div>
                                </td>
                                <td class="p-2 text-right ">{{ formatCurr(emp.basic_salary) }}</td>
                                <td class="p-2 text-right ">{{ formatCurr(emp.house_rent) }}</td>
                                <td class="p-2 text-right ">{{ formatCurr(emp.medical_allowance) }}</td>
                                <td class="p-2 text-right">{{ formatCurr(emp.transport_allowance) }}</td>
                                <td class="p-2 text-right  text-blue-600 font-bold">{{ formatCurr(emp.bonus) }}</td>
                                <td class="p-2 text-right  text-red-600">{{ formatCurr(emp.tax_deduction) }}</td>
                                <td class="p-2 text-right  text-red-600">{{ formatCurr(emp.pf_deduction) }}</td>
                                <td class="p-2 text-right  text-red-600">{{ formatCurr(emp.others_deduction) }}</td>
                                <td class="p-2 text-right font-black  bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300">
                                    {{ formatCurr(emp.bank_amount) }}
                                </td>
                                <td class="p-2 text-center">
                                    <button @click="viewPaySlip(emp.id)" class="p-1 text-blue-600 hover:scale-110 transition">
                                        <FileText class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot class="bg-slate-900 text-white font-black uppercase text-[10px] sticky bottom-0 z-20 shadow-[0_-2px_10px_rgba(0,0,0,0.1)]">
                            <tr class="divide-x divide-slate-800">
                                <td colspan="2" class="p-2 text-right tracking-widest text-slate-400">Grand Totals</td>
                                <td class="p-2 text-right font-mono text-yellow-400">{{ formatCurr(items.reduce((a, b) => a + Number(b.basic_salary), 0)) }}</td>
                                <td colspan="7" class="p-2 text-right text-slate-400">Total Net:</td>
                                <td class="p-2 text-right font-mono text-yellow-400 font-black text-xs italic">{{ formatCurr(items.reduce((a, b) => a + Number(b.bank_amount), 0)) }}</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', 'Courier New', Courier, monospace; }
@media print {
    .p-3.sm\:p-4, .Link, button, .bg-gray-50, .shadow-sm { display: none !important; }
    .overflow-auto { overflow: visible !important; height: auto !important; }
    .lg\:block { display: block !important; }
    .lg\:hidden { display: none !important; }
    table { font-size: 10pt; width: 100%; }
    th, td { border: 0.5px solid #ccc; padding: 2px; }
}
</style>
