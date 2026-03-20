<script setup lang="ts">
import { ref, computed } from 'vue'
import Swal from 'sweetalert2'

import {
    X, Printer, CheckCircle,
    FileText, Loader2, Search, Building2, Send, MessageSquare, Clock
} from 'lucide-vue-next'

const props = defineProps<{
    show: boolean
    isPosting: boolean
    payrollResults: any[]
    formErrors: Record<string, string>
    payrollMonth: string
    office: string
    postOption: string
    is_bonus: boolean // Controls visibility of the column
}>()

const emit = defineEmits(['close', 'confirm', 'viewPaySlip', 'postIndividual'])

const searchQuery = ref('')
const processingId = ref<number | null>(null)

// --- HELPERS ---
const formatCurr = (value: number) => {
    if (!value || value === 0) return '৳ 0.00';
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value).replace(/^/, '৳ ');
};

const formatMonth = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

// --- EDITABLE LOGIC ---
const updateCalculations = (emp: any) => {
    const basic = Number(emp.basic_salary) || 0;
    const house = Number(emp.house_rent) || 0;
    const med = Number(emp.medical) || 0;
    const trans = Number(emp.transport) || 0;
    const other = Number(emp.other_allowance) || 0;
    const arrear = Number(emp.arrear) || 0;
    const bonus = Number(emp.bonus_amount) || 0;
    const otAmount = Number(emp.ot_amount) || 0; // Integrated OT Amount

    const tax = Number(emp.tax_deduction) || 0;
    const pf = Number(emp.pf_deduction) || 0;
    const otherDed = Number(emp.other_deduction) || 0;

    // Gross now includes OT Amount
    emp.gross_salary = basic + house + med + trans + other + arrear + bonus + otAmount;
    emp.net_salary = emp.gross_salary - (tax + pf + otherDed);

    if(!emp.remarks && (bonus !== 0 || otAmount !== 0)) {
        emp.remarks = "Manual Adjustment";
    }
};

// --- FILTERED RESULTS ---
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

// --- DYNAMIC TOTALS ---
const totalBasic = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.basic_salary), 0));
const totalOTHours = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.ot_hours || 0), 0));
const totalOTAmount = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.ot_amount || 0), 0));
const totalBonus = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.bonus_amount), 0));
const totalGross = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.gross_salary), 0));
const totalNet   = computed(() => filteredResults.value.reduce((a, b) => a + Number(b.net_salary), 0));

const handleIndividualPost = (emp: any) => {
    const modalElement = document.querySelector('.fixed.inset-0') as HTMLElement;
    const modalZIndex = modalElement ? parseInt(window.getComputedStyle(modalElement).zIndex) : 100;

    Swal.fire({
        title: 'POST INDIVIDUAL?',
        html: `
            <div class="text-left p-3 bg-gray-800 rounded-lg border border-gray-700">
                <div class="text-[10px] text-blue-400 font-black uppercase">Staff</div>
                <div class="text-sm font-bold text-white mb-2">${emp.name}</div>
                <div class="text-[10px] text-blue-400 font-black uppercase">Month</div>
                <div class="text-sm font-bold text-white">${formatMonth(props.payrollMonth)}</div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm Post',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#374151',
        background: '#111827',
        color: '#ffffff',
        reverseButtons: true,
        didOpen: () => {
            const container = Swal.getContainer();
            if (container) container.style.zIndex = (modalZIndex + 20).toString();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processingId.value = emp.id;
            emit('postIndividual', emp, () => {
                processingId.value = null;
            });
        }
    });
}

const handleConfirm = () => {
    const modalElement = document.querySelector('.fixed.inset-0') as HTMLElement;
    const modalZIndex = modalElement ? parseInt(window.getComputedStyle(modalElement).zIndex) : 100;

    Swal.fire({
        title: 'CONFIRM BATCH PAYROLL',
        html: `
            <div class="text-xs text-gray-400 uppercase font-bold mb-2">Period: ${formatMonth(props.payrollMonth)}</div>
            <p class="text-sm font-bold">Post official records for ${props.payrollResults.length} staff?</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Post All',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#374151',
        background: '#111827',
        color: '#ffffff',
        reverseButtons: true,
        didOpen: () => {
            const container = Swal.getContainer();
            if (container) container.style.zIndex = (modalZIndex + 20).toString();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            emit('confirm');
        }
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-white dark:bg-gray-950 flex flex-col overflow-hidden font-sans text-gray-900 dark:text-gray-100">

        <div v-if="isPosting" class="absolute inset-0 z-[110] bg-white/80 dark:bg-gray-900/80 backdrop-blur-md flex flex-col items-center justify-center text-center">
            <Loader2 class="w-16 h-16 text-blue-600 animate-spin mb-4" />
            <h3 class="text-2xl font-black uppercase tracking-widest">Finalizing Records...</h3>
        </div>

        <div class="bg-gray-900 text-white p-4 shadow-2xl shrink-0">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-600 p-3 rounded-xl"><Building2 class="w-6 h-6 text-white" /></div>
                    <div>
                        <h2 class="text-lg font-black uppercase tracking-tighter">{{ office || 'Payroll Preview' }}</h2>
                        <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">{{ formatMonth(payrollMonth) }}</p>
                    </div>
                </div>

                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><Search class="w-4 h-4" /></span>
                    <input v-model="searchQuery" type="text" placeholder="Search preview list..." class="w-full bg-gray-800 border-none rounded-lg py-2 pl-10 text-xs text-white focus:ring-2 focus:ring-blue-500 cursor-text" />
                </div>

                <div class="flex gap-2">
                    <button @click="emit('close')" class="px-5 py-2 bg-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition cursor-pointer">
                        <X class="w-4 h-4" />
                    </button>
                    <button
                        v-if="postOption !== 'draft'"
                        @click="handleConfirm"
                        :disabled="isPosting"
                        class="px-6 py-2 bg-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-blue-500 transition shadow-lg cursor-pointer disabled:opacity-50"
                    >
                        <CheckCircle v-if="!isPosting" class="w-4 h-4" />
                        <Loader2 v-else class="w-4 h-4 animate-spin" />
                        {{ isPosting ? 'Processing...' : 'Confirm & Post All' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="Object.keys(formErrors).length > 0" class="m-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shrink-0">
            <div class="flex items-center gap-2 mb-2">
                <X class="w-5 h-5" />
                <p class="font-black uppercase tracking-widest text-xs">Processing Error</p>
            </div>
            <ul class="list-disc list-inside text-xs font-bold uppercase tracking-tight">
                <li v-for="(err, key) in formErrors" :key="key">{{ err }}</li>
            </ul>
        </div>

        <div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-950">
            <table class="w-full border-separate border-spacing-0 text-left text-[11px] lg:text-[12px]">
                <thead class="sticky top-0 bg-gray-100 dark:bg-gray-900 z-20 border-b-2 border-gray-300 dark:border-gray-700">
                <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                    <th class="p-4 font-black uppercase w-12 text-center text-gray-500">SL</th>
                    <th class="p-4 font-black uppercase text-gray-500">Staff Info</th>
                    <th class="p-4 font-black uppercase text-right text-gray-500">Basic</th>
                    <th class="p-4 font-black uppercase text-right text-gray-500">House</th>
                    <th class="p-4 font-black uppercase text-right text-gray-500">Med.</th>
                    <th class="p-4 font-black uppercase text-right text-gray-500">Trans.</th>
                    <th class="p-4 font-black uppercase text-right text-emerald-600">Arrear</th>
                    <th class="p-4 font-black uppercase text-right text-gray-500">Oth. Ben</th>

                    <th class="p-4 font-black uppercase text-center text-gray-500">Work Hrs</th>
                    <th class="p-4 font-black uppercase text-center text-orange-600">OT Hrs</th>
                    <th class="p-4 font-black uppercase text-right text-orange-600">OT Amt</th>

                    <th v-if="is_bonus" class="p-4 font-black uppercase text-right bg-blue-50 dark:bg-blue-900/20 text-blue-600 min-w-[150px]">
                        Bonus & Remarks
                    </th>

                    <th class="p-4 font-black uppercase text-right bg-green-50 dark:bg-green-900/10 text-green-700 font-black">Gross</th>
                    <th class="p-4 font-black uppercase text-right text-red-600">Tax</th>
                    <th class="p-4 font-black uppercase text-right text-red-600">PF</th>
                    <th class="p-4 font-black uppercase text-right text-red-600">Oth. Ded</th>
                    <th class="p-4 font-black uppercase text-right bg-blue-600 text-white w-32 shadow-lg">Net Pay</th>
                    <th class="p-4 font-black uppercase text-center w-28 text-gray-500">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                <tr v-for="(emp, idx) in filteredResults" :key="emp.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="p-4 text-center font-bold text-gray-400 border-r dark:border-gray-700">{{ idx + 1 }}</td>
                    <td class="p-4 border-r dark:border-gray-700">
                        <div class="font-black uppercase text-xs leading-tight text-blue-900 dark:text-blue-300">{{ emp.name }}</div>
                        <div class="text-[9px] font-mono text-gray-500 font-bold uppercase mt-1">ID: {{ emp.empId }}</div>
                    </td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.basic_salary) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.house_rent) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.medical) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.transport) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700 text-emerald-600 font-bold">{{ formatCurr(emp.arrear) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.other_allowance) }}</td>

                    <td class="p-4 text-center font-mono border-r dark:border-gray-700 text-gray-500">
                        {{ emp.normal_hours || 0 }}h
                    </td>
                    <td class="p-4 text-center font-mono border-r dark:border-gray-700 text-orange-600 font-bold">
                        {{ emp.ot_hours || 0 }}h
                    </td>
                    <td class="p-4 text-right border-r dark:border-gray-700 bg-orange-50/30 dark:bg-orange-900/10">
                        <div class="flex items-center justify-end relative">
                            <span class="absolute left-0 text-[10px] font-bold text-orange-400">৳</span>
                            <input
                                type="number"
                                v-model.number="emp.ot_amount"
                                @input="updateCalculations(emp)"
                                class="w-full bg-transparent text-right font-black text-orange-700 dark:text-orange-400 outline-none border-b border-orange-200 dark:border-orange-800 focus:border-orange-600 transition-colors py-1 pl-4"
                                placeholder="0.00"
                            />
                        </div>
                    </td>

                    <td v-if="is_bonus" class="p-3 border-r dark:border-gray-700 bg-blue-50/30 dark:bg-blue-900/10 min-w-[150px]">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-end relative">
                                <span class="absolute left-0 text-[10px] font-bold text-blue-400">৳</span>
                                <input
                                    type="number"
                                    v-model.number="emp.bonus_amount"
                                    @input="updateCalculations(emp)"
                                    class="w-full bg-transparent text-right font-black text-blue-700 dark:text-blue-400 outline-none border-b-2 border-blue-200 dark:border-blue-800 focus:border-blue-600 transition-colors py-1 pl-4"
                                    placeholder="0.00"
                                />
                            </div>
                            <div class="flex items-center gap-1.5 opacity-70 focus-within:opacity-100">
                                <MessageSquare class="w-3 h-3 text-blue-500" />
                                <input
                                    v-model="emp.remarks"
                                    type="text"
                                    placeholder="Adjustment reason..."
                                    class="w-full bg-transparent text-[9px] font-bold uppercase tracking-tighter outline-none border-none placeholder:text-gray-400 text-blue-800 dark:text-blue-200"
                                />
                            </div>
                        </div>
                    </td>

                    <td class="p-4 text-right font-black font-mono text-slate-800 dark:text-slate-200 bg-green-50/30 dark:bg-green-900/5 border-r dark:border-gray-700">{{ formatCurr(emp.gross_salary) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.tax_deduction) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.pf_deduction) }}</td>
                    <td class="p-4 text-right font-mono border-r dark:border-gray-700">{{ formatCurr(emp.other_deduction) }}</td>
                    <td class="p-4 text-right font-black font-mono bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 text-sm border-r dark:border-gray-700">{{ formatCurr(emp.net_salary) }}</td>

                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-1">
                            <button v-if="postOption !== 'draft'" @click="handleIndividualPost(emp)" :disabled="processingId === emp.id" class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-lg text-blue-600 cursor-pointer transition">
                                <Loader2 v-if="processingId === emp.id" class="w-4 h-4 animate-spin" />
                                <Send v-else class="w-4 h-4" />
                            </button>
                            <button @click="emit('viewPaySlip', emp)" class="p-1.5 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-lg text-indigo-600 cursor-pointer">
                                <FileText class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
                <tfoot class="sticky bottom-0 bg-gray-900 text-white font-black text-xs z-20">
                <tr>
                    <td colspan="2" class="p-4 text-right uppercase text-gray-400 tracking-widest">Grand Total</td>
                    <td class="p-4 text-right font-mono text-yellow-400">{{ formatCurr(totalBasic) }}</td>

                    <td colspan="5" class="border-r border-gray-800"></td>

                    <td class="p-4 text-center text-gray-500 italic border-r border-gray-800">--</td>
                    <td class="p-4 text-center font-mono text-orange-400 border-r border-gray-800">{{ totalOTHours }}h</td>
                    <td class="p-4 text-right font-mono text-orange-400 border-r border-gray-800 bg-orange-900/20">{{ formatCurr(totalOTAmount) }}</td>

                    <td v-if="is_bonus" class="p-4 text-right font-mono text-blue-400 border-r border-gray-800 bg-blue-900/20">
                        {{ formatCurr(totalBonus) }}
                    </td>

                    <td class="p-4 text-right font-mono text-green-400 border-r border-gray-800 bg-green-900/20">{{ formatCurr(totalGross) }}</td>
                    <td colspan="3" class="border-r border-gray-800"></td>
                    <td class="p-4 text-right font-mono text-yellow-400 text-sm bg-blue-800/30">{{ formatCurr(totalNet) }}</td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="p-6 bg-white dark:bg-gray-900 border-t flex flex-col sm:flex-row justify-between items-center gap-6 shadow-2xl shrink-0">
            <div class="flex gap-12">
                <div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Staff</p><p class="text-xl font-black">{{ filteredResults.length }}</p></div>
                <div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Disbursement</p><p class="text-xl font-black text-blue-600">{{ formatCurr(totalNet) }}</p></div>
            </div>
            <button class="w-full sm:w-auto px-10 py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black hover:bg-black uppercase tracking-widest flex items-center justify-center gap-3 cursor-pointer">
                <Printer class="w-5 h-5" /> Export Detailed Report
            </button>
        </div>
    </div>
</template>

<style scoped>
.fixed { z-index: 9999; }
table { border-collapse: separate; border-spacing: 0; }
th, td { white-space: nowrap; }
button { cursor: pointer; }
.cursor-text { cursor: text !important; }

/* Remove arrows from number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>
