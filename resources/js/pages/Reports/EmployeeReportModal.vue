<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    X, Printer, Search, Mail, Phone, CalendarDays, FileDown,
    Users2, Image as ImageIcon, Droplet, Clock, ShieldCheck, UserCog,
    MapPin, Briefcase, GraduationCap, dark, UserCheck, Wallet
} from 'lucide-vue-next'

const props = defineProps<{
    show: boolean,
    reportData: any[],
    filters: any
}>()

const emit = defineEmits(['close'])
const searchQuery = ref('')

const filteredResults = computed(() => {
    if (!searchQuery.value) return props.reportData;
    const q = searchQuery.value.toLowerCase();
    return props.reportData.filter(row =>
        row.person_name?.toLowerCase().includes(q) ||
        row.employee_id?.toString().toLowerCase().includes(q) ||
        row.department_name?.toLowerCase().includes(q)
    );
});

const formatDate = (dateStr: string) => {
    if (!dateStr || dateStr === '0000-00-00') return '---';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
};

const getInitials = (name: string) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};
const exportCSV = () => {
    // 1. Define all columns from your SQL schema
    const headers = [
        "SL", "Employee ID", "Code", "Full Name", "Gender", "Designation",
        "Department", "Division", "Reporting Manager", "Dept Head",
        "Official Email", "Official Phone", "Blood Group", "Office Time",
        "Joining Date", "Confirmation Date", "Effective Date",
        "Salary Status", "Tax Deduction", "Employment Status"
    ];

    // 2. Map data specifically to match the headers
    const rows = filteredResults.value.map((row, idx) => [
        idx + 1,
        row.employee_id,
        row.employee_code,
        (row.person_name || '').replace(/,/g, ''), // Remove commas to prevent CSV breakage
        row.gender,
        row.designation_name,
        row.department_name,
        row.division_name,
        row.reporting_manager_name || 'N/A',
        row.department_head_name || 'N/A',
        row.official_email || 'N/A',
        row.official_phone || 'N/A',
        row.blood_group || 'N/A',
        `${row.office_in_time} - ${row.office_out_time}`,
        row.joining_date,
        row.confirmed_date,
        row.effective_date || '---',
        row.is_salary_stop ? 'Stopped' : 'Active',
        row.is_tax_deduction ? 'Yes' : 'No',
        row.employee_status
    ]);

    // 3. Generate and Download
    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" // Add BOM for Excel UTF-8 support
        + headers.join(",") + "\n"
        + rows.map(e => e.join(",")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Master_Employee_Report_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const printReport = () => {
    // 1. Manually find the scroll container and reset it
    const scrollContainer = document.querySelector('.overflow-auto');
    if (scrollContainer) {
        scrollContainer.scrollTop = 0;
    }

    // 2. Delay slightly to allow the browser to recalculate layout
    setTimeout(() => {
        window.print();
    }, 100);
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white dark:bg-gray-950 flex flex-col overflow-hidden font-sans text-slate-900">

        <div class="bg-gray-900 text-white p-4 no-print flex-shrink-0 z-[110] border-b border-gray-800">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-500/20"><Users2 class="w-6 h-6 text-white" /></div>
                    <div>
                        <h2 class="text-lg font-black uppercase tracking-tighter leading-none">Employee Report</h2>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.3em] mt-1 italic">Extended Multi-Column Report</p>
                    </div>
                </div>

                <div class="relative w-full lg:w-96">
                    <input v-model="searchQuery" type="text" placeholder="Quick Filter Records..." class="w-full bg-gray-800 border-none rounded-xl py-2.5 pl-11 text-xs text-white focus:ring-2 focus:ring-blue-600 outline-none transition-all" />
                    <Search class="absolute left-4 top-3 w-4 h-4 text-gray-500" />
                </div>

                <div class="flex gap-2">
                    <button @click="printReport" class="px-6 py-2 bg-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest text-white hover:bg-blue-500 cursor-pointer shadow-lg shadow-blue-600/20 transition-all">Print</button>
                    <button @click="emit('close')" class="px-6 py-2 bg-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest text-white hover:bg-gray-600 cursor-pointer transition-all"><X class="w-4 h-4" /></button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-950 print:p-0 relative">
            <div class="max-w-full bg-white dark:bg-gray-900  border-2 border-gray-200 dark:border-gray-800  min-w-[2800px]">

                <table class="w-full text-left text-[11px] border-collapse ">
                    <thead class="sticky top-0 z-50 bg-gray-100 dark:bg-gray-900 shadow-sm border-b-2 border-gray-300 dark:border-gray-700">
                    <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                        <th class="p-4 w-12 text-center bg-gray-50 dark:bg-gray-900">SL</th>
                        <th class="p-4 w-20 text-center bg-gray-50 dark:bg-gray-900">Photo</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900">Employee Info</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900">Division</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900">Department</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900">Designation</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900 text-blue-600">Supervisor</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900 text-indigo-600">Dept Head</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900">Contact Details</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900 text-pink-600">Birthday</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900">Blood</th>
                        <th class="p-4 bg-gray-50 dark:bg-gray-900">Skills</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900">Office Time</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900 text-green-600">Joining</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900 text-blue-500">Confirmed</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900 text-red-500">Effective</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900">Salary</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900">Tax</th>
                        <th class="p-4 text-center bg-gray-50 dark:bg-gray-900">Status</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(emp, idx) in filteredResults" :key="emp.id" class="hover:bg-blue-50/50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="p-4 text-center font-bold text-gray-400 border-r dark:border-gray-800">{{ idx + 1 }}</td>

                        <td class="p-0 border-r dark:border-gray-800 text-center">
                            <div class="w-11 h-11 rounded-full border-2 border-gray-200 dark:border-gray-700 overflow-hidden mx-auto flex items-center justify-center font-black dark:bg-gray-800 shadow-sm">
                                <img v-if="emp.photo" :src="emp.photo" class="w-full h-full object-cover" />
                                <span v-else class="text-blue-600 text-[12px]">{{ getInitials(emp.person_name) }}</span>
                            </div>
                        </td>

                        <td class="p-2 border-r dark:border-gray-800">
                            <div class="font-black uppercase text-xs text-slate-800 dark:text-white leading-tight">{{ emp.person_name }}</div>
                            <div class="text-[9px] font-mono text-blue-600 font-bold mt-1 uppercase tracking-tighter">ID: {{ emp.employee_id }} | {{ emp.employee_code }}</div>
                        </td>

                        <td class="p-2 border-r dark:border-gray-800 font-bold uppercase  dark:text-white ">{{ emp.division_name }}</td>
                        <td class="p-2 border-r dark:border-gray-800 font-bold uppercase  dark:text-white ">{{ emp.department_name }}</td>
                        <td class="p-2 border-r dark:border-gray-800  dark:text-white  font-bold uppercase text-slate-500">{{ emp.designation_name }}</td>

                        <td class="p-2 border-r dark:border-gray-800 font-black text-blue-800 dark:text-blue-400 uppercase text-[10px]">{{ emp.reporting_manager_name || '---' }}</td>
                        <td class="p-2 border-r dark:border-gray-800 font-bold text-indigo-700 dark:text-indigo-400 uppercase text-[10px]">{{ emp.department_head_name || '---' }}</td>

                        <td class="p-2 border-r dark:border-gray-800">
                            <div class="font-bold flex items-center gap-1.5"><Mail class="w-3 h-3 text-gray-400"/> {{ emp.official_email || '---' }}</div>
                            <div class="font-bold flex items-center gap-1.5 mt-1"><Phone class="w-3 h-3 text-gray-400"/> {{ emp.official_phone || '---' }}</div>
                        </td>

                        <td class="p-2 text-center border-r dark:border-gray-800 font-mono font-bold text-pink-600">{{ formatDate(emp.dob) }}</td>
                        <td class="p-2 text-center border-r dark:border-gray-800 font-black text-red-600 uppercase">{{ emp.blood_group || '--' }}</td>
                        <td class="p-2 border-r dark:border-gray-800 italic font-medium text-slate-500 line-clamp-1 max-w-[150px]">{{ emp.skills || '---' }}</td>

                        <td class="p-2 text-center border-r dark:border-gray-800 font-mono font-black text-slate-700 dark:text-white">
                            {{ emp.office_in_time }} - {{ emp.office_out_time }}
                            <div class="text-[8px] text-red-500 uppercase block mt-1">Grace: {{ emp.late_time }}m</div>
                        </td>

                        <td class="p-2 text-center border-r dark:border-gray-800 font-mono font-black text-green-700">{{ formatDate(emp.joining_date) }}</td>
                        <td class="p-2 text-center border-r dark:border-gray-800 font-mono font-black text-blue-600">{{ formatDate(emp.confirmation_date) }}</td>
                        <td class="p-2 text-center border-r dark:border-gray-800 font-mono font-black text-red-500 italic">{{ formatDate(emp.effective_date) }}</td>

                        <td class="p-2 text-center border-r dark:border-gray-800">
                                <span :class="emp.is_salary_stop ? 'text-red-600 font-black' : 'text-green-600 font-bold'" class="uppercase text-[9px]">
                                    {{ emp.is_salary_stop ? 'STOPPED' : 'ACTIVE' }}
                                </span>
                        </td>
                        <td class="p-2 text-center border-r dark:border-gray-800">
                                <span :class="emp.is_tax_deduction ? 'text-indigo-600 font-black' : 'text-gray-400'" class="uppercase text-[9px]">
                                    {{ emp.is_tax_deduction ? 'TAX ON' : 'TAX OFF' }}
                                </span>
                        </td>

                        <td class="p-2 text-center">
                                <span class="px-3 py-1 rounded-lg font-black text-[9px] uppercase tracking-tighter border bg-gray-50 dark:bg-gray-800 dark:text-gray-200">
                                    {{ emp.employee_status }}
                                </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-5 bg-white dark:bg-gray-900 border-t flex justify-between items-center no-print shadow-2xl z-[110]">
            <div class="flex items-center gap-12">
                <div><p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Directory</p><p class="text-xl font-black text-slate-800 dark:text-white">{{ filteredResults.length }}</p></div>
                <div class="w-px h-8 bg-gray-200 dark:bg-gray-800"></div>
                <div><p class="text-[9px] font-black text-red-500 uppercase tracking-widest leading-none mb-1">Salary Hold</p><p class="text-xl font-black text-red-600">{{ filteredResults.filter(r => r.is_salary_stop).length }}</p></div>
                <div><p class="text-[9px] font-black text-blue-600 uppercase tracking-widest leading-none mb-1">Active Staff</p><p class="text-xl font-black text-blue-600">{{ filteredResults.filter(r => r.employee_status === 'Active').length }}</p></div>
            </div>
            <button @click="exportCSV" class="px-10 py-3.5 bg-gray-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest flex items-center gap-3 cursor-pointer shadow-xl hover:bg-black transition-all active:scale-95">
                <FileDown class="w-5 h-5" /> Export Extended Master CSV
            </button>
        </div>
    </div>
</template>


<style scoped>
th, td { white-space: nowrap; }
thead th { position: sticky; top: 0; z-index: 50; box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1); }
@media print {
    /* 1. Hide the entire application root and all layout elements */
    #app,
    #app > div,
    aside,
    nav,
    header,
    [data-sidebar],
    .group,
    .peer {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* 2. Force the Modal to be the ONLY visible element on the page */
    /* This overrides the 'fixed' positioning so it starts at the top of the paper */
    .fixed.inset-0 {
        display: block !important;
        visibility: visible !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: auto !important;
        background: white !important;
        z-index: 99999 !important;
    }

    /* 3. Ensure the Modal and all its children are visible */
    .fixed.inset-0 * {
        visibility: visible !important;
    }

    /* 4. Reset the 2800px table width to fill the paper width */
    .min-w-\[2800px\] {
        min-width: 100% !important;
        width: 100% !important;
    }

    /* 5. Hide the Modal's UI buttons and search bar */
    .no-print,
    button,
    input {
        display: none !important;
    }

    /* 6. Ensure the scrollable area expands for all rows */
    .flex-1 {
        overflow: visible !important;
        display: block !important;
    }

    /* 7. Setup for Landscape printing */
    @page {
        size: landscape;
        margin: 1cm;
    }
}
</style>
