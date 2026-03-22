<script setup lang="ts">
import { ref, computed } from 'vue'
import { X, Search, FileDown, Users2 } from 'lucide-vue-next'
import {useExport} from "../../composables/useExport";

const props = defineProps<{
    show: boolean,
    reportData: any[],
    filters: any
}>()

const emit = defineEmits(['close'])
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement>()
const { print, isProcessing } = useExport();
const isPrinting = ref(false)
// Filter rows based on search input
const filteredResults = computed(() => {
    if (!searchQuery.value) return props.reportData
    const q = searchQuery.value.toLowerCase()
    return props.reportData.filter(row =>
        row.person_name?.toLowerCase().includes(q) ||
        row.employee_id?.toString().includes(q) ||
        row.department_name?.toLowerCase().includes(q) ||
        row.leave_type?.toLowerCase().includes(q)
    )
})

// Total leave days
const totalDays = computed(() =>
    filteredResults.value.reduce((sum, row) => sum + (parseFloat(row.total_days) || 0), 0).toFixed(2)
)

// Total per leave type
const leaveTotals = computed(() => {
    const totals: Record<string, number> = {}
    filteredResults.value.forEach(row => {
        if (row.leave_type) {
            totals[row.leave_type] = (totals[row.leave_type] || 0) + (parseFloat(row.total_days) || 0)
        }
    })
    return totals
})

// Format date
const formatDate = (dateStr: string) => {
    if (!dateStr || dateStr === '0000-00-00') return '---'
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' })
}

// Export CSV
const exportCSV = () => {
    const headers = ["SL", "Employee ID", "Full Name", "Department", "Leave Type", "From", "To", "Days", "Status"]
    const rows = filteredResults.value.map((row, idx) => [
        idx + 1,
        row.employee_id,
        (row.person_name || '').replace(/,/g, ''),
        row.department_name,
        row.leave_type,
        row.from_date,
        row.to_date,
        row.total_days,
        row.status
    ])
    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + headers.join(",") + "\n" + rows.map(r => r.join(",")).join("\n")
    const encodedUri = encodeURI(csvContent)
    const link = document.createElement("a")
    link.setAttribute("href", encodedUri)
    link.setAttribute("download", `Leave_Report_${new Date().toISOString().slice(0,10)}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

// Print report
const handlePrint = async () =>  {
    // Define the Header for every printed page (Company Name & Date Range)
    const headerHtml = `
        <div style="text-align: center; border-bottom: 2px solid black; padding-bottom: 10px; margin-bottom: 20px;">
            <h1 style="font-size: 20px; font-weight: 900; margin: 0;">OFFICIAL SALARY & OT REPORT</h1>
            <p style="font-size: 12px; margin: 5px 0;">Period: ${props.filters.date_from} to ${props.filters.date_to}</p>
        </div>
    `;

    isPrinting.value = true
    await print('leave_report', '','','landscape')
    isPrinting.value = false
};

// Focus search input
const focusSearch = () => {
    searchInput.value?.focus()
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white flex flex-col overflow-hidden font-sans text-slate-900">

        <!-- Header -->
        <div class="bg-gray-900 text-white p-4 flex justify-between items-center no-print border-b">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-xl cursor-pointer">
                    <Users2 class="w-6 h-6 text-white"/>
                </div>
                <h2 class="font-black uppercase tracking-tight">Leave Report</h2>
            </div>

            <div class="flex items-center gap-2 relative">
                <!-- Search input -->
                <div class="relative">
                    <input
                        ref="searchInput"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Quick Filter..."
                        class="rounded-xl py-1 pl-8 pr-3 text-sm text-gray-900 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <button @click="focusSearch" class="absolute left-2 top-1/2 -translate-y-1/2 cursor-pointer">
                        <Search class="w-4 h-4 text-gray-500"/>
                    </button>
                </div>

                <button @click="handlePrint" class="bg-green-600 px-4 py-1 rounded text-white text-xs font-bold cursor-pointer">Print</button>
                <button @click="emit('close')" class="bg-red-600 px-3 py-1 rounded text-white text-xs font-bold cursor-pointer">
                    <X class="w-4 h-4 inline"/>
                </button>
            </div>
        </div>

        <!-- Print Header -->

        <div id="leave_report" class="flex-1 overflow-auto bg-gray-50 p-4 flex flex-col items-center">

            <!-- Centered report header -->
            <div class="print-header mb-2 text-center font-black text-xl">
                Leave Report
            </div>
            <table class="w-full max-w-6xl text-sm border-collapse border bg-white">
                <thead class="bg-gray-100 sticky top-0">
                <tr>
                    <th class="border p-2">SL</th>
                    <th class="border p-2">Employee</th>
                    <th class="border p-2">Department</th>
                    <th class="border p-2">Leave Type</th>
                    <th class="border p-2">From</th>
                    <th class="border p-2">To</th>
                    <th class="border p-2">Days</th>
                    <th class="border p-2">Status</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(row, idx) in filteredResults" :key="row.id" class="hover:bg-gray-100">
                    <td class="border p-2 text-center">{{ idx + 1 }}</td>
                    <td class="border p-2">{{ row.person_name }} ({{ row.employee_id }})</td>
                    <td class="border p-2">{{ row.department_name }}</td>
                    <td class="border p-2">{{ row.leave_type }}</td>
                    <td class="border p-2">{{ formatDate(row.from_date) }}</td>
                    <td class="border p-2">{{ formatDate(row.to_date) }}</td>
                    <td class="border p-2 text-center">{{ row.total_days }}</td>
                    <td class="border p-2 text-center">{{ row.status }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer / Totals -->
        <div class="p-4 border-t flex justify-between items-center no-print flex-wrap gap-4">
            <div>
                <p class="font-bold text-sm">Total Leave Days: <b>{{ totalDays }}</b></p>
                <div class="flex gap-4 flex-wrap mt-1">
          <span v-for="(val, key) in leaveTotals" :key="key" class="bg-gray-200 px-2 py-1 rounded text-xs font-semibold">
            {{ key }}: {{ val.toFixed(2) }}
          </span>
                </div>
            </div>
            <button @click="exportCSV" class="bg-gray-900 px-4 py-2 rounded text-white text-xs font-bold flex items-center gap-2 cursor-pointer">
                <FileDown class="w-4 h-4"/> Export CSV
            </button>
        </div>
    </div>
</template>

<style scoped>
th, td { white-space: nowrap; }
thead th { position: sticky; top: 0; z-index: 50; background: #f3f4f6; }
/* All buttons pointer */
button { cursor: pointer; }

/* Print styles */
@media print {
    .no-print { display: none !important; }

    /* Expand container to full height to print all rows */
    .overflow-auto { overflow: visible !important; height: auto !important; display: flex; justify-content: center; }

    /* Repeat table header on each page */
    table thead { display: table-header-group; }

    /* Custom header for every page */
    .print-header {
        display: block;
        width: 100%;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 2px;
    }

    table { background-color: white; }

    /* Center table */
    .overflow-auto table { margin: auto; }

    /* Landscape page */
    @page { size: landscape; margin: 1cm; }
}
</style>
