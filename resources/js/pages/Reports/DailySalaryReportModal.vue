<script setup lang="ts">
import { X, Printer, FileText, Clock, Calculator, Percent } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
    show: boolean,
    reportData: any[],
    filters: any
}>()

const emit = defineEmits(['close'])

// --- HELPERS ---
const formatCurr = (v: number) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 2
    }).format(v);
}

// --- SUMMARY TOTALS ---
const totalWorkHrs   = computed(() => props.reportData.reduce((a, b) => a + Number(b.work_hours || 0), 0))
const totalOTHrs     = computed(() => props.reportData.reduce((a, b) => a + Number(b.ot_hours || 0), 0))
const totalWorkedTk  = computed(() => props.reportData.reduce((a, b) => a + Number(b.hourly_salary_tk || 0), 0))
const totalOTAmount  = computed(() => props.reportData.reduce((a, b) => a + Number(b.ot_amount || 0), 0))
const grandTotal     = computed(() => props.reportData.reduce((a, b) => a + Number(b.total_today || 0), 0))

const printReport = () => window.print()
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white dark:bg-gray-950 flex flex-col overflow-hidden font-sans">

        <div class="bg-gray-900 text-white p-4 flex justify-between items-center shrink-0 print:hidden shadow-2xl">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-500/20">
                    <Calculator class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest leading-none">Hourly Earnings & OT Report</h2>
                    <p class="text-[10px] text-blue-400 font-bold uppercase mt-1.5 tracking-tighter">
                        Range: {{ filters.date_from }} <span class="text-gray-600">to</span> {{ filters.date_to }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button @click="printReport" class="px-6 py-2.5 bg-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-blue-500 transition active:scale-95 shadow-lg cursor-pointer">
                    <Printer class="w-4 h-4" /> Print Document
                </button>
                <button @click="emit('close')" class="px-4 py-2.5 bg-gray-800 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition cursor-pointer border border-gray-700">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-auto p-6 bg-gray-50 dark:bg-gray-950 print:p-0">
            <div class="max-w-[1400px] mx-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl print:shadow-none print:border-none">

                <table class="w-full text-left border-collapse text-[10px] lg:text-[11px]">
                    <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0 z-20 border-b-2 border-gray-200 dark:border-gray-700">
                    <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                        <th class="p-3 font-black uppercase text-gray-500 w-24">Date</th>
                        <th class="p-3 font-black uppercase text-gray-500">Employee</th>
                        <th class="p-3 font-black uppercase text-center text-gray-500">In/Out</th>
                        <th class="p-3 font-black uppercase text-center text-gray-500 bg-blue-50/30 dark:bg-blue-900/10">Work Hr</th>
                        <th class="p-3 font-black uppercase text-center text-orange-600 bg-orange-50/30">OT Hr</th>
                        <th class="p-3 font-black uppercase text-right text-gray-400 italic">Rate/Hr</th>
                        <th class="p-3 font-black uppercase text-right text-blue-700">Worked Tk</th>
                        <th class="p-3 font-black uppercase text-right text-orange-600">OT Tk</th>
                        <th class="p-3 font-black uppercase text-right bg-gray-900 text-white w-28">Total Pay</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(row, idx) in reportData" :key="idx" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition divide-x divide-gray-100 dark:divide-gray-800">
                        <td class="p-3 font-bold text-gray-400 whitespace-nowrap">{{ row.date }}</td>
                        <td class="p-3">
                            <div class="font-black text-blue-900 dark:text-blue-300 uppercase leading-none">{{ row.name }}</div>
                            <div class="text-[8px] font-mono font-bold text-gray-400 mt-1 uppercase tracking-tighter">ID: {{ row.emp_id }}</div>
                        </td>
                        <td class="p-3 text-center">
                            <div class="text-[9px] font-mono text-gray-500 whitespace-nowrap">
                                {{ row.in_time }} - {{ row.out_time }}
                            </div>
                        </td>
                        <td class="p-3 text-center font-bold text-blue-700 bg-blue-50/10">{{ row.work_hours }}h</td>
                        <td class="p-3 text-center font-black text-orange-600 bg-orange-50/10">{{ row.ot_hours }}h</td>
                        <td class="p-3 text-right font-mono text-gray-400 italic">{{ formatCurr(row.hourly_rate) }}</td>
                        <td class="p-3 text-right font-mono font-bold text-blue-700">{{ formatCurr(row.hourly_salary_tk) }}</td>
                        <td class="p-3 text-right font-mono font-bold text-orange-600">{{ formatCurr(row.ot_amount) }}</td>
                        <td class="p-3 text-right font-black font-mono bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white">
                            {{ formatCurr(row.total_today) }}
                        </td>
                    </tr>
                    </tbody>

                    <tfoot class="bg-gray-900 text-white font-black text-[11px] sticky bottom-0 z-30 shadow-[0_-10px_20px_rgba(0,0,0,0.2)]">
                    <tr class="divide-x divide-gray-800">
                        <td colspan="3" class="p-4 text-right uppercase tracking-widest text-gray-500">Accumulated Totals</td>
                        <td class="p-4 text-center text-blue-400">{{ totalWorkHrs.toFixed(2) }}h</td>
                        <td class="p-4 text-center text-orange-400">{{ totalOTHrs.toFixed(2) }}h</td>
                        <td class="p-4 bg-gray-800"></td>
                        <td class="p-4 text-right text-blue-400">{{ formatCurr(totalWorkedTk) }}</td>
                        <td class="p-4 text-right text-orange-400">{{ formatCurr(totalOTAmount) }}</td>
                        <td class="p-4 text-right bg-blue-600 text-white text-xs">{{ formatCurr(grandTotal) }}</td>
                    </tr>
                    </tfoot>
                </table>

                <div v-if="reportData.length === 0" class="p-32 text-center flex flex-col items-center">
                    <FileText class="w-16 h-16 text-gray-200 mb-4" />
                    <p class="text-gray-400 font-black uppercase tracking-widest text-[10px]">No records found for selection</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .print\:hidden { display: none !important; }
    .fixed { position: absolute !important; inset: 0 !important; background: white !important; overflow: visible !important; }
    .flex-1 { overflow: visible !important; display: block !important; }
    table { border-collapse: collapse !important; width: 100% !important; font-size: 9px !important; }
    th, td { border: 1px solid #e5e7eb !important; padding: 4px !important; }
    tfoot { display: table-footer-group !important; color: black !important; }
    tfoot td { background-color: #f3f4f6 !important; color: black !important; border-top: 2px solid black !important; }
}
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
</style>
