<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    X, Printer, Search, Building2,
    Clock, UserCheck, CalendarDays, FileDown
} from 'lucide-vue-next'
import {useExport} from "../../composables/useExport";

const props = defineProps<{
    show: boolean
    reportData: any[]
    filters: any
}>()

const emit = defineEmits(['close'])
const searchQuery = ref('')
const { print, isProcessing } = useExport();
const isPrinting = ref(false)
// Filter logic for the internal search bar
const filteredResults = computed(() => {
    if (!searchQuery.value) return props.reportData;
    const q = searchQuery.value.toLowerCase();
    return props.reportData.filter(row =>
        row.emp_name?.toLowerCase().includes(q) ||
        row.employee_id?.toString().toLowerCase().includes(q)
    );
});

// Helper to sum time strings (format HH:mm) into a total duration string
const sumTimeStrings = (data: any[], key: string) => {
    let totalMinutes = 0;
    data.forEach(row => {
        const timeStr = row[key];
        if (timeStr && timeStr !== '--:--' && timeStr !== '--') {
            const parts = timeStr.split(':').map(Number);
            if (parts.length === 2) {
                totalMinutes += (parts[0] * 60) + parts[1];
            }
        }
    });
    const h = Math.floor(totalMinutes / 60);
    const m = totalMinutes % 60;
    return `${h}h ${m}m`;
};

// Grand Totals for Footer
const totalOvertime = computed(() => sumTimeStrings(filteredResults.value, 'overtime'));
const totalLessWorking = computed(() => sumTimeStrings(filteredResults.value, 'less_working'));
const totalExtraWorking = computed(() => {
    const parseToMins = (timeStr) => {
        if (!timeStr || timeStr.includes('--')) return 0;
        // Handles "5h 20m" format from sumTimeStrings
        const h = parseInt(timeStr.match(/(\d+)h/) ? timeStr.match(/(\d+)h/)[1] : 0);
        const m = parseInt(timeStr.match(/(\d+)m/) ? timeStr.match(/(\d+)m/)[1] : 0);
        return (h * 60) + m;
    };

    const otMins = parseToMins(totalOvertime.value);
    const lessMins = parseToMins(totalLessWorking.value);
    const diff = otMins - lessMins;

    const isNegative = diff < 0;
    const absDiff = Math.abs(diff);
    const h = Math.floor(absDiff / 60);
    const m = absDiff % 60;

    return `${isNegative ? '-' : ''}${h}h ${m}m`;
});
const adjustedResults = computed(() => {
    return filteredResults.value.map(row => {
        const parseTime = (timeStr: string) => {
            if (!timeStr || timeStr === '--' || timeStr === '--:--') return 0;
            const parts = timeStr.split(':').map(Number);
            if (parts.length !== 2 || isNaN(parts[0]) || isNaN(parts[1])) return 0;
            return parts[0] + parts[1] / 60;
        };

        const formatTime = (hours: number) => {
            if (!hours || hours <= 0) return '--';
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);
            return `${h}:${m.toString().padStart(2, '0')}`;
        };

        const workingHours = parseTime(row.working_time);
        const overtimeHours = parseTime(row.overtime);
        const lessWorkingHours = parseTime(row.less_working);

        return {
            ...row,
            working_time: formatTime(workingHours),
            overtime: formatTime(overtimeHours),
            less_working: formatTime(lessWorkingHours),
        };
    });
});

const formatDate = (date: string) => {
    if (!date) return '---';
    return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
};

const rowDate = (dateStr: string) => {
    if (!dateStr) return '---';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
};

const exportCSV = () => {
    const headers = ["SL", "Name", "ID", "Status", "Date", "Office In", "Office Out", "Office Hours", "Actual In", "Actual Out", "Late In", "Out Status", "Work Time", "Overtime", "Less Work"];
    const rows = adjustedResults.value.map((row, idx) => [
        idx + 1,
        (row.emp_name || '').replace(/,/g, ''),
        row.employee_id,
        (row.status || '').replace(/,/g, ''),
        rowDate(row.date) || '---',
        row.off_in,
        row.off_out,
        row.office_work_hours,
        row.att_in,
        row.att_out,
        row.late_in,
        row.out_status,
        row.working_time,
        row.overtime,
        row.less_working
    ]);

    const csvContent = "data:text/csv;charset=utf-8,"
        + headers.join(",") + "\n"
        + rows.map(e => e.join(",")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Attendance_Report_${props.filters.date_from}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
const handlePrint = async () =>  {
    // Define the Header for every printed page (Company Name & Date Range)
    const headerHtml = `
        <div style="text-align: center; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">
            <h1 style="font-size: 20px; font-weight: 900; margin: 0;">OFFICIAL SALARY & OT REPORT</h1>
            <p style="font-size: 12px; margin: 5px 0;">Period: ${props.filters.date_from} to ${props.filters.date_to}</p>
        </div>
    `;

    isPrinting.value = true
    await print('attendance_report', '','','landscape')
    isPrinting.value = false
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white dark:bg-gray-950 flex flex-col overflow-hidden font-sans text-slate-900">

        <div class="bg-gray-900 text-white p-4 shadow-2xl no-print flex-shrink-0">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-600 p-3 rounded-xl shadow-inner"><CalendarDays class="w-6 h-6 text-white" /></div>
                    <div>
                        <h2 class="text-lg font-black uppercase leading-tight tracking-tighter text-white">Attendance Report: {{ filters.office || 'All Offices' }}</h2>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                            Range: {{ formatDate(filters.date_from) }} — {{ formatDate(filters.date_to) }}
                        </p>
                    </div>
                </div>

                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500"><Search class="w-4 h-4" /></span>
                    <input v-model="searchQuery" type="text" placeholder="Filter Name or ID..." class="w-full bg-gray-800 border-none rounded-lg py-2 pl-10 text-xs text-white focus:ring-2 focus:ring-blue-500 outline-none" />
                </div>

                <div class="flex gap-2">
                    <button @click="handlePrint" class="px-5 py-2 bg-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-blue-500 transition cursor-pointer text-white">
                        <Printer class="w-4 h-4" /> Print
                    </button>
                    <button @click="emit('close')" class="px-5 py-2 bg-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-gray-600 transition cursor-pointer text-white">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <div id="attendance_report" class="flex-1 attendance_price overflow-auto bg-gray-50 dark:bg-gray-950 print:p-0 relative">
            <div class="max-w-full bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 shadow-xl print:border-none print:shadow-none">
                <table class="w-full text-left text-[11px] border-collapse">
                    <thead class="sticky top-0 z-50 bg-gray-100 dark:bg-gray-900 shadow-sm border-b-2 border-gray-300 dark:border-gray-700">
                    <tr class="divide-x divide-gray-200 dark:divide-gray-700 uppercase font-black text-gray-600 dark:text-gray-300">
                        <th class="p-4 w-12 text-center bg-gray-100 dark:bg-gray-900">SL</th>
                        <th class="p-4 bg-gray-100 dark:bg-gray-900">Staff Info</th>
                        <th class="p-4 bg-gray-100 dark:bg-gray-900">Dept/Div</th>
                        <th class="p-4 text-center bg-gray-100 dark:bg-gray-900">Status</th>
                        <th class="p-4 text-center text-indigo-600 dark:text-indigo-400 bg-gray-100 dark:bg-gray-900">Date</th>
                        <th class="p-4 text-center bg-gray-100 dark:bg-gray-900">Office Schedule</th>
                        <th class="p-4 text-center text-slate-500 bg-gray-100 dark:bg-gray-900">Office Hrs</th>
                        <th class="p-4 text-center bg-gray-100 dark:bg-gray-900 text-blue-600">Actual In/Out</th>
                        <th class="p-4 text-center text-red-600 bg-gray-100 dark:bg-gray-900 border-x">Late In</th>
                        <th class="p-4 text-center text-orange-600 bg-gray-100 dark:bg-gray-900 border-r">Out Status</th>
                        <th class="p-4 text-center text-green-600 bg-gray-100 dark:bg-gray-900">Work Time</th>
                        <th class="p-4 text-center text-blue-600 bg-gray-100 dark:bg-gray-900">Overtime</th>
                        <th class="p-4 text-center text-amber-600 bg-gray-100 dark:bg-gray-900">Less Work</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(row, idx) in adjustedResults" :key="idx"
                        class="bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-gray-700 transition"
                    >
                        <td class="p-4 text-center font-bold text-gray-400 border-r dark:border-gray-700">{{ idx + 1 }}</td>
                        <td class="p-4 border-r dark:border-gray-700">
                            <div class="font-black uppercase text-xs leading-tight text-blue-900 dark:text-blue-300">{{ row.emp_name }}</div>
                            <div class="text-[10px] font-mono text-gray-500 font-bold mt-1">ID: {{ row.employee_id }}</div>
                        </td>
                        <td class="p-4 border-r dark:border-gray-700">
                            <div class="font-bold text-[10px] text-gray-700 dark:text-gray-300 uppercase leading-tight">{{ row.department }}</div>
                            <div class="text-[9px] text-gray-400 leading-tight">{{ row.division }}</div>
                        </td>
                        <td class="p-4 text-center border-r dark:border-gray-700">
                                <span :class="{
                                    'bg-green-100 text-green-700': row.status === 'Present',
                                    'bg-red-100 text-red-700': row.status === 'Absent',
                                    'bg-yellow-100 text-yellow-700': row.status.includes('Leave'),
                                    'bg-gray-100 text-gray-700': row.status === 'Off Day'
                                }" class="px-3 py-1 rounded-full font-black text-[9px] uppercase tracking-tighter">
                                    {{ row.status }}
                                </span>
                        </td>
                        <td class="p-4 text-center border-r dark:border-gray-700 font-mono font-bold text-gray-600">
                            {{ rowDate(row.date) || rowDate(row.add_time) }}
                        </td>
                        <td class="p-4 text-center font-mono border-r dark:border-gray-700 text-gray-400 italic">
                            {{ row.off_in }} — {{ row.off_out }}
                        </td>
                        <td class="p-4 text-center border-r dark:border-gray-700 font-bold text-slate-500">
                            {{ row.office_work_hours }}
                        </td>
                        <td class="p-4 text-center font-mono border-r dark:border-gray-700">
                            <div class="text-blue-600 font-black">{{ row.att_in }}</div>
                            <div class="text-slate-500 text-[10px] font-bold">{{ row.att_out }}</div>
                        </td>
                        <td class="p-4 text-center font-black text-red-600 border-r dark:border-gray-700 italic">
                            {{ row.late_in }}
                        </td>
                        <td class="p-4 text-center border-r dark:border-gray-700 italic font-bold" :class="row.out_status.includes('Early') ? 'text-orange-600' : 'text-gray-500'">
                            {{ row.out_status }}
                        </td>
                        <td class="p-4 text-center font-black border-r dark:border-gray-700 text-gray-700 dark:text-gray-300">
                            {{ row.working_time }}
                        </td>
                        <td class="p-4 text-center font-black text-blue-700 dark:text-blue-400 italic border-r dark:border-gray-700">
                            {{ row.overtime }}
                        </td>
                        <td class="p-4 text-center font-black text-amber-600 italic">
                            {{ row.less_working }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border-t flex flex-wrap justify-between items-center gap-4 shadow-2xl no-print flex-shrink-0">
            <div class="grid grid-cols-2 md:grid-cols-9 gap-x-8 gap-y-2">
                <div><p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-none">Total</p><p class="text-lg font-black dark:text-white">{{ adjustedResults.length }}</p></div>
                <div><p class="text-[9px] font-bold text-green-600 uppercase tracking-widest leading-none">Present</p><p class="text-lg font-black text-green-600">{{ adjustedResults.filter(r => r.status === 'Present').length }}</p></div>
                <div><p class="text-[9px] font-bold text-red-600 uppercase tracking-widest leading-none">Absent</p><p class="text-lg font-black text-red-600">{{ adjustedResults.filter(r => r.status === 'Absent').length }}</p></div>
                <div><p class="text-[9px] font-bold text-yellow-600 uppercase tracking-widest leading-none">Leave</p><p class="text-lg font-black text-yellow-600">{{ adjustedResults.filter(r => r.status.includes('Leave')).length }}</p></div>
                <div><p class="text-[9px] font-bold text-red-400 uppercase tracking-widest leading-none">Late In</p><p class="text-lg font-black text-red-400">{{ adjustedResults.filter(r => r.late_in !== '--').length }}</p></div>

                <div class="border-l pl-4 border-gray-200 dark:border-gray-700"><p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest leading-none">Total OT</p><p class="text-lg font-black text-blue-600">{{ totalOvertime }}</p></div>

                <div><p class="text-[9px] font-bold text-amber-600 uppercase tracking-widest leading-none">Total Less</p><p class="text-lg font-black text-amber-600">{{ totalLessWorking }}</p></div>

                <div class="border-l pl-4 border-gray-200 dark:border-gray-700">
                    <p class="text-[9px] font-bold uppercase tracking-widest leading-none" :class="totalExtraWorking.startsWith('-') ? 'text-red-500' : 'text-indigo-600'">Net Balance</p>
                    <p class="text-lg font-black" :class="totalExtraWorking.startsWith('-') ? 'text-red-500' : 'text-indigo-600'">{{ totalExtraWorking }}</p>
                </div>
            </div>

            <button @click="exportCSV" type="button" class="px-8 py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black transition hover:bg-black active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2 cursor-pointer shadow-lg">
                <FileDown class="w-4 h-4" /> Export CSV
            </button>
        </div>
    </div>
</template>

<style scoped>
th, td { white-space: nowrap; }
thead th { position: sticky; top: 0; z-index: 50; box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1); }
</style>
