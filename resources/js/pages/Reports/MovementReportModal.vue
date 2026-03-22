<script setup lang="ts">
import { ref, computed } from 'vue'
import { X, Printer, Search, FileDown, MapPin, Navigation, Clock, Wallet } from 'lucide-vue-next'
import {useExport} from "../../composables/useExport";

const props = defineProps<{ show: boolean, reportData: any[], filters: any }>()
const emit = defineEmits(['close'])
const searchQuery = ref('')
const { print, isProcessing } = useExport();
const isPrinting = ref(false)
/**
 * Filter logic: Searches across Employee Name and Destination fields
 */
const filteredResults = computed(() => {
    if (!searchQuery.value) return props.reportData;
    const q = searchQuery.value.toLowerCase();
    return props.reportData.filter(row =>
        row.employee?.toLowerCase().includes(q) ||
        row.destination?.toLowerCase().includes(q)
    );
});

/**
 * Splits date and time into two lines for high-density layout
 */
const formatDateTimeSplit = (dateStr: string) => {
    if (!dateStr) return { date: '---', time: '' };
    const dateObj = new Date(dateStr);

    const date = dateObj.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: '2-digit'
    }).toUpperCase();

    const time = dateObj.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).toUpperCase();

    return { date, time };
};

const formatCSVDate = (date: string) => {
    const d = formatDateTimeSplit(date);
    return `${d.date} ${d.time}`;
};

const exportCSV = () => {
    const headers = ["SL", "Employee", "Division", "Origin", "Destination", "Departure", "Arrival", "Total Bill", "Status"];
    const rows = filteredResults.value.map((row, idx) => [
        idx + 1,
        row.employee,
        row.division,
        row.origin,
        row.destination,
        formatCSVDate(row.start_at),
        formatCSVDate(row.end_at),
        row.total_bill,
        row.status
    ]);

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
    const link = document.createElement("a");
    link.setAttribute("href", encodeURI(csvContent));
    link.setAttribute("download", `Movement_Report.csv`);
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
    await print('movement_report', '','','landscape')
    isPrinting.value = false
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white flex flex-col overflow-hidden text-slate-900 font-sans">

        <div class="bg-slate-900 text-white p-4 no-print flex flex-col lg:flex-row justify-between items-center gap-4 shadow-xl">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-500/20"><Navigation class="w-6 h-6" /></div>
                <div>
                    <h2 class="text-lg font-black uppercase tracking-tighter leading-none">Movement Logs</h2>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                        Division: {{ filters.division || 'All' }} | {{ filteredResults.length }} Records Found
                    </p>
                </div>
            </div>

            <div class="relative w-full lg:w-96">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Quick Filter Employee or Destination..."
                    class="w-full bg-slate-800 border-none rounded-xl py-2.5 pl-11 pr-4 text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-600 outline-none transition-all shadow-inner"
                />
                <Search class="absolute left-4 top-3 w-4 h-4 text-slate-500" />
            </div>

            <div class="flex gap-2">
                <button @click="exportCSV" class="px-6 py-2 bg-emerald-600 rounded-lg text-[10px] font-black uppercase flex items-center gap-2 cursor-pointer shadow-lg shadow-emerald-600/20 hover:bg-emerald-500 transition-colors">
                    <FileDown class="w-4 h-4" /> CSV
                </button>
                <button @click="handlePrint" class="px-6 py-2 bg-blue-600 rounded-lg text-[10px] font-black uppercase flex items-center gap-2 cursor-pointer shadow-lg shadow-blue-600/20 hover:bg-blue-500 transition-colors">
                    <Printer class="w-4 h-4" /> Print
                </button>
                <button @click="emit('close')" class="px-4 py-2 bg-slate-700 rounded-lg cursor-pointer hover:bg-slate-600 transition-colors"><X class="w-4 h-4" /></button>
            </div>
        </div>

        <div id="movement_report" class="flex-1 overflow-auto  print:p-0">
            <div class="min-w-[1200px] print:min-w-full  border-2 border-slate-100  shadow-sm">
                <table class="w-full text-[11px] border-collapse bg-white">
                    <thead class="sticky top-0 z-50 bg-slate-100 uppercase font-black text-slate-600 shadow-md">
                    <tr class="divide-x divide-slate-200">
                        <th class="p-3 w-10 text-center">SL</th>
                        <th class="p-3 text-left">Staff Details</th>
                        <th class="p-3 text-left">Route</th>
                        <th class="p-3 w-28 text-center text-blue-600">Departure</th>
                        <th class="p-3 w-28 text-center text-red-600">Arrival</th>
                        <th class="p-3 w-28 text-right">Bill</th>
                        <th class="p-3 w-24 text-center">Status</th>
                        <th class="p-3 text-left">Auth</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <tr v-for="(row, idx) in filteredResults" :key="row.id" class="hover:bg-blue-50/40 transition-colors group">
                        <td class="p-3 text-center font-bold text-slate-300 border-r group-hover:text-blue-500">{{ idx + 1 }}</td>
                        <td class="p-3 border-r leading-tight">
                            <div class="font-black text-slate-800 uppercase">{{ row.employee }}</div>
                            <div class="text-[9px] text-blue-600 font-bold uppercase">{{ row.division }}</div>
                        </td>
                        <td class="p-3 border-r leading-tight">
                            <div class="text-[10px] font-bold text-slate-500 uppercase flex items-center gap-1">
                                <MapPin class="w-2.5 h-2.5"/> {{ row.origin }}
                            </div>
                            <div class="font-black text-slate-900 uppercase mt-1 flex items-center gap-1">
                                <Navigation class="w-2.5 h-2.5 text-emerald-500"/> {{ row.destination }}
                            </div>
                        </td>
                        <td class="p-3 text-center border-r bg-blue-50/20">
                            <div class="font-bold text-slate-500 text-[11px] leading-none">{{ formatDateTimeSplit(row.start_at).date }}</div>
                            <div class="font-black text-blue-700 text-[9px] mt-1">{{ formatDateTimeSplit(row.start_at).time }}</div>
                        </td>
                        <td class="p-3 text-center border-r bg-red-50/20">
                            <div class="font-bold text-slate-500 text-[11px] leading-none">{{ formatDateTimeSplit(row.end_at).date }}</div>
                            <div class="font-black text-red-700 text-[9px] mt-1">{{ formatDateTimeSplit(row.end_at).time }}</div>
                        </td>
                        <td class="p-3 text-right border-r font-black text-slate-900 bg-slate-50/30">
                            {{ row.total_bill.toLocaleString() }} <span class="text-[9px] text-slate-400 ml-1">{{ row.currency }}</span>
                        </td>
                        <td class="p-3 text-center border-r">
                                <span class="px-2.5 py-1 rounded-md font-black text-[8px] uppercase border tracking-tighter" :class="row.status === 'Approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-amber-50 text-amber-600 border-amber-200'">
                                    {{ row.status }}
                                </span>
                        </td>
                        <td class="p-3 font-bold text-slate-500 uppercase text-[9px] truncate max-w-[100px]">
                            {{ row.approved_by || 'PENDING' }}
                        </td>
                    </tr>
                    </tbody>

                    <tfoot class=" bg-slate-900 text-white uppercase font-black">
                    <tr class="divide-x divide-slate-700">
                        <td colspan="5" class="p-4 text-right text-[10px] tracking-widest border-t border-slate-700 bg-slate-900">Total Filtered Conveyance Bill</td>
                        <td class="p-4 text-right text-[13px] border-t border-slate-700 bg-blue-700 shadow-[inset_0_2px_0_rgba(255,255,255,0.1)]">
                            {{ filteredResults.reduce((acc, curr) => acc + curr.total_bill, 0).toLocaleString() }}
                            <span class="text-[8px] ml-1 opacity-60 italic">BDT</span>
                        </td>
                        <td colspan="2" class="p-4 bg-slate-900 border-t border-slate-700"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="p-6 bg-white border-t flex justify-between items-center no-print shadow-[0_-15px_30px_rgba(0,0,0,0.05)] z-[60]">
            <div class="flex gap-14">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1.5 tracking-widest">Dataset Logs</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">{{ filteredResults.length }}</p>
                </div>
                <div class="w-px h-10 bg-slate-200"></div>
                <div>
                    <p class="text-[9px] font-black text-blue-600 uppercase leading-none mb-1.5 tracking-widest">Cumulative Bill</p>
                    <p class="text-2xl font-black text-blue-700 leading-none">
                        {{ filteredResults.reduce((acc, curr) => acc + curr.total_bill, 0).toLocaleString() }}
                        <span class="text-xs font-bold text-slate-400 ml-1">BDT</span>
                    </p>
                </div>
            </div>
            <div class="text-[9px] font-bold text-slate-300 uppercase italic">
                Generated: {{ new Date().toLocaleString() }}
            </div>
        </div>
    </div>
</template>

<style scoped>
th, td { white-space: nowrap; }
.overflow-auto::-webkit-scrollbar { width: 6px; height: 6px; }
.overflow-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-auto::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

@media print {
    .no-print, [data-sidebar], aside, nav, .bg-slate-900, .peer, .group { display: none !important; }
    .fixed { position: absolute !important; width: 100% !important; height: auto !important; overflow: visible !important; }
    body * { visibility: hidden; }
    .fixed, .fixed * { visibility: visible !important; }
    thead { display: table-header-group !important; position: static !important; }
    tfoot { display: table-footer-group !important; position: static !important; }
    @page { size: landscape; margin: 0.5cm; }
}
</style>
