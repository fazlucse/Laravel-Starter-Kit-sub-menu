<script setup lang="ts">
import { X, Printer, FileText, Calculator, Download } from 'lucide-vue-next'
import {computed, ref} from 'vue'
import { useExport } from '@/composables/useExport';

const props = defineProps<{
    show: boolean,
    reportData: any[],
    filters: any
}>()

const emit = defineEmits(['close'])
const { print, isProcessing } = useExport();
const isPrinting = ref(false)
// --- HELPERS ---
const formatCurr = (v: number) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 2
    }).format(v);
}

// --- CSV EXPORT LOGIC ---
const downloadCSV = () => {
    if (props.reportData.length === 0) return;

    // Define Headers
    const headers = [
        "Date", "Status", "Employee Name", "ID",
        "Shift", "Lunch", "Punch In/Out", "Stay Time",
        "Work Hr", "OT Hr", "Rate/Hr", "Work Tk",
        "H.Rent", "Medical", "Transport", "Others", "OT Tk", "Total Pay"
    ];

    // Map Data Rows
    const rows = props.reportData.map(row => [
        `"${row.date}"`,
        row.day_status || (row.is_off_day ? 'Off Day' : 'Present'),
        `"${row.name}"`, // Quote names to handle commas
        row.emp_id,
        `"${row.office_in_time} - ${row.office_out_time}"`,
        row.lunch_break,
        `"${row.in_time} - ${row.out_time}"`,
        row.total_work_str,
        row.work_hours,
        row.ot_hours,
        row.hourly_rate,
        row.worked_salary_tk,
        row.house_rent_tk,
        row.medical_tk,
        row.transport_tk,
        row.other_tk,
        row.ot_amount,
        row.total_today
    ]);

    // Add Totals Row
    const totalsRow = [
        "TOTALS", "", "", "", "", "", "", "",
        totalWorkHrs.value.toFixed(2),
        totalOTHrs.value.toFixed(2),
        "",
        totalWorkedTk.value.toFixed(2),
        totalHouseRent.value.toFixed(2),
        totalMedical.value.toFixed(2),
        totalTransport.value.toFixed(2),
        totalOthers.value.toFixed(2),
        totalOTAmount.value.toFixed(2),
        grandTotal.value.toFixed(2)
    ];

    // Combine into CSV String
    const csvContent = [headers, ...rows, totalsRow]
        .map(e => e.join(","))
        .join("\n");

    // Create Download Link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `Salary_Report_${props.filters.date_from}_to_${props.filters.date_to}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// --- SUMMARY TOTALS ---
const totalWorkHrsVal = computed(() => props.reportData.reduce((a, b) => a + Number(b.work_hours || 0), 0))
const totalOTHrsVal   = computed(() => props.reportData.reduce((a, b) => a + Number(b.ot_hours || 0), 0))
// const totalWorkHrs   = computed(() => props.reportData.reduce((a, b) => a + Number(b.work_hours || 0), 0))
// const totalOTHrs     = computed(() => props.reportData.reduce((a, b) => a + Number(b.ot_hours || 0), 0))
const totalWorkHrs = computed(() => formatTotalTime(totalWorkHrsVal.value))
const totalOTHrs   = computed(() => formatTotalTime(totalOTHrsVal.value))
const totalWorkedTk  = computed(() => props.reportData.reduce((a, b) => a + Number(b.worked_salary_tk || 0), 0))
const totalMedical   = computed(() => props.reportData.reduce((a, b) => a + Number(b.medical_tk || 0), 0))
const totalTransport = computed(() => props.reportData.reduce((a, b) => a + Number(b.transport_tk || 0), 0))
const totalOthers    = computed(() => props.reportData.reduce((a, b) => a + Number(b.other_tk || 0), 0))
const totalHouseRent = computed(() => props.reportData.reduce((a, b) => a + Number(b.house_rent_tk || 0), 0))
const totalOTAmount  = computed(() => props.reportData.reduce((a, b) => a + Number(b.ot_amount || 0), 0))
const grandTotal     = computed(() => props.reportData.reduce((a, b) => a + Number(b.total_today || 0), 0))

const handlePrint = async () =>  {
    // Define the Header for every printed page (Company Name & Date Range)
    const headerHtml = `
        <div style="text-align: center; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">
            <h1 style="font-size: 20px; font-weight: 900; margin: 0;">OFFICIAL SALARY & OT REPORT</h1>
            <p style="font-size: 12px; margin: 5px 0;">Period: ${props.filters.date_from} to ${props.filters.date_to}</p>
        </div>
    `;

    isPrinting.value = true
    await print('print_salary', headerHtml,'','landscape')
    isPrinting.value = false
};
const formatTotalTime = (decimalHours: number) => {
    const totalMinutes = Math.round(decimalHours * 60);
    const h = Math.floor(totalMinutes / 60);
    const m = totalMinutes % 60;
    return `${h}:${String(m).padStart(2, '0')}`;
};
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
                <button @click="downloadCSV" class="px-5 py-2.5 bg-emerald-700 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-emerald-600 transition active:scale-95 shadow-lg cursor-pointer">
                    <Download class="w-4 h-4" /> CSV Export
                </button>
                <button @click="handlePrint" class="px-5 py-2.5 bg-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-blue-500 transition active:scale-95 shadow-lg cursor-pointer">
                    <Printer class="w-4 h-4" /> Print
                </button>
                <button @click="emit('close')" class="px-4 py-2.5 bg-gray-800 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition cursor-pointer border border-gray-700">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div id="print_salary" class="flex-1  overflow-auto p-0 bg-gray-50 dark:bg-gray-950 print:p-0">
            <div class="max-w-[1800px] mx-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl print:shadow-none print:border-none">

                <table class="w-full text-left border-collapse text-[10px] lg:text-[11px]">
                    <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0 z-20 border-b-2 border-gray-200 dark:border-gray-700">
                    <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                        <th class="p-3 font-black uppercase text-gray-500 w-12 text-center">S.N.</th> <th class="p-3 font-black uppercase text-gray-500 w-24">Date</th>
                        <th class="p-3 font-black uppercase text-gray-500">Employee</th>
                        <th class="p-3 font-black uppercase text-gray-500 text-center">Office In/Out</th>
                        <th class="p-3 font-black uppercase text-gray-500 text-center">Lunch</th>
                        <th class="p-3 font-black uppercase text-center text-gray-500">Punch In/Out</th>
                        <th class="p-3 font-black uppercase text-center text-gray-500">Total Stay</th>
                        <th class="p-3 font-black uppercase text-center text-gray-500 bg-blue-50/30">Work Hr</th>
                        <th class="p-3 font-black uppercase text-center text-orange-600 bg-orange-50/30">OT Hr</th>
                        <th class="p-3 font-black uppercase text-right text-gray-400 italic">Rate/Hr</th>
                        <th class="p-3 font-black uppercase text-right text-blue-700">Work Tk</th>
                        <th class="p-3 font-black uppercase text-right text-gray-500">H. Rent</th>
                        <th class="p-3 font-black uppercase text-right text-gray-500">Medical</th>
                        <th class="p-3 font-black uppercase text-right text-gray-500">Trans.</th>
                        <th class="p-3 font-black uppercase text-right text-gray-500">Others</th>
                        <th class="p-3 font-black uppercase text-right text-orange-600  right-28 bg-gray-100 dark:bg-gray-800 z-30">OT Tk</th>
                        <th class="p-3 text-right bg-blue-600 text-white text-xs  right-0 z-30 border-l border-blue-500">Total Pay</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(row, idx) in reportData" :key="idx"
                        class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition divide-x divide-gray-100 dark:divide-gray-800">
                        <td class=" text-center font-mono text-black">{{ idx + 1 }}</td>

                        <td class="p-2 text-[12px] font-mono  text-black dark:text-gray-300 whitespace-nowrap">
                            {{ row.date }}
                            <div v-if="row.is_off_day" class="text-[7px] text-green-600 font-black uppercase">{{ row.day_status }}</div>
                        </td>

                        <td class="p-2">
                            <div class="text-[12px] font-mono whitespace-nowrap  text-black dark:text-gray-300 uppercase leading-none">{{ row.name }}</div>
                            <div class="text-[8px] font-mono text-black mt-1 uppercase tracking-tighter">ID: {{ row.emp_id }}</div>
                        </td>

                        <td class="p-2 text-[12px] font-mono text-black dark:text-gray-300 whitespace-nowrap text-center">
                            {{ row.office_in_time }} - {{ row.office_out_time }}
                        </td>

                        <td class=" text-center text-[11px] font-mono text-black dark:text-gray-300">
                            {{ row.lunch_break }}
                        </td>

                        <td class="p-2 text-center">
                            <div class="text-[11px] font-mono text-black dark:text-gray-300 ">
                                {{ row.in_time }} - {{ row.out_time }}
                            </div>
                        </td>

                        <td class=" text-center font-mono text-gray-500">{{ row.total_work_str }}</td>
                        <td class=" text-center font-bold text-blue-700 bg-blue-50/10">{{ row.actual_work_str }}</td>
                        <td class=" text-center font-black text-orange-600 bg-orange-50/10">{{ row.ot_hours_str }}</td>

                        <td class=" text-right font-mono text-gray-400 italic">{{ formatCurr(row.hourly_rate) }}</td>
                        <td class=" text-right font-mono font-bold text-blue-700">{{ formatCurr(row.worked_salary_tk) }}</td>

                        <td class=" text-right font-mono text-black dark:text-gray-400">{{ formatCurr(row.house_rent_tk) }}</td>
                        <td class=" text-right font-mono text-black dark:text-gray-400">{{ formatCurr(row.medical_tk) }}</td>
                        <td class=" text-right font-mono text-black dark:text-gray-400">{{ formatCurr(row.transport_tk) }}</td>
                        <td class=" text-right font-mono text-black dark:text-gray-400">{{ formatCurr(row.other_tk) }}</td>

                        <td class=" text-right font-mono  font-bold text-orange-600  right-28 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50 z-10 border-l border-gray-100 dark:border-gray-800">
                            {{ formatCurr(row.ot_amount) }}
                        </td>

                        <td class=" text-right font-black  bg-gray-600 font-mono bg-white dark:bg-gray-900 text-gray-900 dark:text-white  right-0 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50 z-10 border-l border-gray-200 dark:border-gray-700">
                            {{ formatCurr(row.total_today) }}
                        </td>
                    </tr>
                    </tbody>

                    <tbody class="bg-gray-900 text-white font-black text-[11px] sticky bottom-0 z-30">
                    <tr class="divide-x divide-gray-800">
                        <td colspan="7" class="p-4 text-right uppercase tracking-widest text-gray-500">Totals</td>
                        <td class="p-4 text-center text-blue-400">{{ totalWorkHrs }}h</td>
                        <td class="p-4 text-center text-orange-400">{{ totalOTHrs }}h</td>
                        <td class="p-4 bg-gray-800"></td>
                        <td class="p-4 text-right text-blue-400">{{ formatCurr(totalWorkedTk) }}</td>

                        <td class="p-4 text-right text-gray-400">{{ formatCurr(totalHouseRent) }}</td>
                        <td class="p-4 text-right text-gray-400">{{ formatCurr(totalMedical) }}</td>
                        <td class="p-4 text-right text-gray-400">{{ formatCurr(totalTransport) }}</td>
                        <td class="p-4 text-right  bg-gray-600 text-gray-400">{{ formatCurr(totalOthers) }}</td>

                        <td class="p-4 text-right text-orange-400  right-28 bg-gray-900 z-30 border-l border-gray-800">{{ formatCurr(totalOTAmount) }}</td>
                        <td class="p-4 text-right bg-blue-600 text-white text-xs  right-0 z-30 border-l border-blue-500">{{ formatCurr(grandTotal) }}</td>
                    </tr>
                    </tbody>
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
    table { border-collapse: collapse !important; width: 100% !important; font-size: 8px !important; }
    th, td { border: 1px solid #e5e7eb !important; padding: 4px !important; position: static !important; }
    tfoot { display: table-footer-group !important; color: black !important; }
    tfoot td { background-color: #f3f4f6 !important; color: black !important; border-top: 2px solid black !important; }
    .sticky { position: static !important; }
}
</style>
