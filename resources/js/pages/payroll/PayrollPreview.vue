<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    X, Printer, CheckCircle,
    FileText, Loader2, Search, Building2, Send
} from 'lucide-vue-next'

const props = defineProps<{
    show: boolean
    isPosting: boolean
    payrollResults: any[]
    formErrors: Record<string, string>
    payrollMonth: string
    office: string
    postOption: string
}>()

const emit = defineEmits(['close', 'confirm', 'viewPaySlip', 'postIndividual'])

const searchQuery = ref('')
const processingId = ref<number | null>(null) // Track which row is being posted individually

// Helpers
const formatCurr = (value: number) => {
    if (!value || value === 0) return '—';
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value).replace(/^/, '৳ ');
};

const formatMonth = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

// Filtered Results Logic
const filteredResults = computed(() => {
    if (!searchQuery.value) return props.payrollResults;
    const q = searchQuery.value.toLowerCase();
    return props.payrollResults.filter(emp =>
        emp.name?.toLowerCase().includes(q) ||
        emp.empId?.toString().toLowerCase().includes(q) ||
        emp.division?.toLowerCase().includes(q) ||
        emp.department?.toLowerCase().includes(q)
    );
});

// Computed Totals for Footer
const totalNet = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.net_salary), 0));
const totalBasic = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.basic_salary), 0));
const totalGross = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.gross_salary), 0));

// Handle individual post click
const handleIndividualPost = (emp: any) => {
    processingId.value = emp.id;
    emit('postIndividual', emp, () => {
        processingId.value = null; // Callback to reset loading state
    });
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white dark:bg-gray-950 flex flex-col overflow-hidden font-sans">

        <div v-if="isPosting" class="absolute inset-0 z-[110] bg-white/80 dark:bg-gray-900/80 backdrop-blur-md flex flex-col items-center justify-center text-center">
            <Loader2 class="w-16 h-16 text-blue-600 animate-spin mb-4" />
            <h3 class="text-2xl font-black uppercase tracking-widest text-gray-900 dark:text-white">Storing Payroll Batch...</h3>
            <p class="text-sm text-gray-500 mt-2">Please do not close this window.</p>
        </div>

        <div class="bg-gray-900 text-white p-4 shadow-2xl">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-600 p-3 rounded-xl shadow-inner"><Building2 class="w-6 h-6 text-white" /></div>
                    <div>
                        <h2 class="text-lg font-black uppercase leading-tight tracking-tighter">Payroll Report: {{ office || 'All Locations' }}</h2>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Period: {{ formatMonth(payrollMonth) }}</p>
                    </div>
                </div>

                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><Search class="w-4 h-4" /></span>
                    <input v-model="searchQuery" type="text" placeholder="Filter Preview (Name, ID, Division)..." class="w-full bg-gray-800 border-none rounded-lg py-2 pl-10 text-xs text-white focus:ring-2 focus:ring-blue-500 cursor-text" />
                </div>

                <div class="flex gap-2">
                    <button @click="emit('close')" class="px-5 py-2 bg-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-gray-600 transition cursor-pointer">
                        <X class="w-4 h-4" />
                    </button>
                    <button v-if="postOption !== 'draft'" @click="emit('confirm')" :disabled="isPosting" class="px-6 py-2 bg-green-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-green-500 transition shadow-lg cursor-pointer disabled:cursor-not-allowed">
                        <CheckCircle class="w-4 h-4" /> Confirm & Post All
                    </button>
                </div>
            </div>
        </div>

        <div v-if="Object.keys(formErrors).length > 0" class="m-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
            <p class="font-bold text-sm">Review Required:</p>
            <ul class="list-disc list-inside text-xs"><li v-for="err in formErrors" :key="err">{{ err }}</li></ul>
        </div>

        <div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-950">
            <table class="w-full border-separate border-spacing-0 text-left text-[11px] lg:text-[12px] bg-white dark:bg-gray-800 shadow-xl">
                <thead class="sticky top-0 bg-gray-100 dark:bg-gray-900 z-20 border-b-2 border-gray-300 dark:border-gray-700 shadow-sm">
                <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                    <th class="p-4 font-black uppercase w-12 text-center">SL</th>
                    <th class="p-4 font-black uppercase">Staff & ID</th>
                    <th class="p-4 font-black uppercase text-right">Basic</th>
                    <th class="p-4 font-black uppercase text-right">House</th>
                    <th class="p-4 font-black uppercase text-right">Med.</th>
                    <th class="p-4 font-black uppercase text-right">Trans.</th>
                    <th class="p-4 font-black uppercase text-right text-emerald-600 dark:text-emerald-400">Arrear</th>
                    <th class="p-4 font-black uppercase text-right">Oth. Ben</th>
                    <th class="p-4 font-black uppercase text-right text-blue-600">Bonus</th>
                    <th class="p-4 font-black uppercase text-right bg-green-50 dark:bg-green-900/10 text-green-700">Gross</th>
                    <th class="p-4 font-black uppercase text-right text-red-600">Tax</th>
                    <th class="p-4 font-black uppercase text-right text-red-600">PF</th>
                    <th class="p-4 font-black uppercase text-right text-red-600">Oth. Ded</th>
                    <th class="p-4 font-black uppercase text-right bg-blue-600 text-white w-32">Net Pay</th>
                    <th class="p-4 font-black uppercase text-center w-28">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(emp, idx) in filteredResults" :key="emp.id" class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition">
                    <td class="p-4 text-center font-bold text-gray-400 border-r dark:border-gray-700">{{ idx + 1 }}</td>
                    <td class="p-4 border-r dark:border-gray-700">
                        <div class="font-black uppercase text-xs leading-tight text-blue-900 dark:text-blue-300">{{ emp.name }}</div>
                        <div class="text-[10px] font-mono text-gray-500 font-bold uppercase tracking-widest mt-1">ID: {{ emp.empId }}</div>
                        <div class="text-[9px] text-gray-400 italic">{{ emp.division || 'N/A' }}</div>
                    </td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.basic_salary) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.house_rent) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.medical) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.transport) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700 text-emerald-600 font-bold">{{ formatCurr(emp.arrear) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.other_allowance) }}</td>
                    <td class="p-4 text-right font-mono text-blue-600 font-black italic border-r dark:border-gray-700">{{ formatCurr(emp.totalBonus) }}</td>
                    <td class="p-4 text-right font-black font-mono text-slate-800 dark:text-slate-200 bg-green-50/30 dark:bg-green-900/5 border-r dark:border-gray-700">{{ formatCurr(emp.gross_salary) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.tax_deduction) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.pf_deduction) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.other_deduction) }}</td>
                    <td class="p-4 text-right font-black font-mono bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 text-sm italic underline decoration-blue-500/50 underline-offset-4">{{ formatCurr(emp.net_salary) }}</td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-1">
                            <button
                                v-if="postOption !== 'draft'"
                                @click="handleIndividualPost(emp)"
                                :disabled="processingId === emp.id"
                                class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-lg transition cursor-pointer text-blue-600"
                                title="Post Individual"
                            >
                                <Loader2 v-if="processingId === emp.id" class="w-4 h-4 animate-spin" />
                                <Send v-else class="w-4 h-4" />
                            </button>
                            <button
                                @click="emit('viewPaySlip', emp)"
                                class="p-1.5 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-lg transition cursor-pointer text-indigo-600"
                                title="View Slip"
                            >
                                <FileText class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
                <tfoot class="sticky bottom-0 bg-gray-900 text-white font-black text-xs z-20">
                <tr>
                    <td colspan="2" class="p-4 text-right uppercase text-gray-400 tracking-widest">Filtered Summary</td>
                    <td class="p-4 text-right font-mono text-yellow-400">{{ formatCurr(totalBasic) }}</td>
                    <td colspan="6"></td>
                    <td class="p-4 text-right font-mono text-green-400">{{ formatCurr(totalGross) }}</td>
                    <td colspan="3"></td>
                    <td class="p-4 text-right font-mono text-yellow-400 text-sm">{{ formatCurr(totalNet) }}</td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="p-6 bg-white dark:bg-gray-900 border-t flex flex-col sm:flex-row justify-between items-center gap-6 shadow-2xl">
            <div class="flex gap-12">
                <div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Staff</p><p class="text-xl font-black text-gray-900 dark:text-white">{{ filteredResults.length }}</p></div>
                <div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Disbursement</p><p class="text-xl font-black text-blue-600">{{ formatCurr(totalNet) }}</p></div>
            </div>
            <button class="w-full sm:w-auto px-10 py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black transition hover:bg-black uppercase tracking-widest flex items-center justify-center gap-3 cursor-pointer">
                <Printer class="w-5 h-5" /> Export Detailed Report
            </button>
        </div>
    </div>
</template>

<style scoped>
.fixed { z-index: 9999; }
table { border-collapse: separate; border-spacing: 0; }
th, td { white-space: nowrap; }
button, select, input {
    cursor: pointer;
}
.cursor-text {
    cursor: text !important;
}
</style>
