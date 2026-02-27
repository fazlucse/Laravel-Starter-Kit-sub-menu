<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'
import {
    Calculator, X, Printer, CheckCircle,
    Send, FileText, Loader2
} from 'lucide-vue-next'

const props = defineProps<{
    departments?: string[],
    offices?: string[],
}>()

const showPreviewModal = ref(false)
const isPosting = ref(false)
const payrollResults = ref<any[]>([])
const processingId = ref<number | null>(null)

const form = useForm({
    department: '',
    office: '',
    payrollDate: new Date().toISOString().split('T')[0],
    payrollMonth: new Date().toISOString().slice(0, 7),
    approvedBy: '',
    notes: '',
    postOption: 'draft',
})

const submitForm = () => {
    form.post('/payroll/generate', {
        preserveState: true,
        onSuccess: (page) => {
            payrollResults.value = page.props.payrollData as any[] || []
            showPreviewModal.value = true
        },
    })
}

const confirmAndPostAll = () => {
    if (!confirm(`Finalize payroll for ${payrollResults.value.length} employees?`)) return;

    isPosting.value = true;
    form.transform((data) => ({
        ...data,
        payroll_data: payrollResults.value
    })).post('/payroll/store-batch', {
        onSuccess: () => {
            showPreviewModal.value = false;
            alert('Payroll batch posted successfully.');
        },
        // onError: () => alert('Error: Please check the highlighted issues.'),
        onFinish: () => isPosting.value = false
    });
}

const postIndividual = (emp: any) => {
    if(!confirm(`Post payroll for ${emp.name}?`)) return
    processingId.value = emp.id
    form.post(`/payroll/post-individual/${emp.id}`, {
        preserveScroll: true,
        onFinish: () => processingId.value = null,
    })
}

const viewPaySlip = (emp: any) => {
    const url = `/payroll/payslip/${emp.id}?month=${form.payrollMonth}`
    window.open(url, '_blank')
}

const formatCurr = (value: number) => {
    if (!value || value === 0) return '—';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 2
    }).format(value).replace('BDT', '৳');
}

const totalNet = computed(() => payrollResults.value.reduce((a, b) => a + Number(b.net_salary), 0))
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Payroll', href: '/payroll' }, { title: 'Generate' }]">

            <div class="bg-white dark:bg-gray-800  shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                <div class="max-w-[95%] mx-auto p-4 lg:p-6">
                <form @submit.prevent="submitForm" class="space-y-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-bold mb-2">Department</label>
                            <select v-model="form.department" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2">Office</label>
                            <select v-model="form.office" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm">
                                <option value="">All Offices</option>
                                <option v-for="off in offices" :key="off" :value="off">{{ off }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2">Payroll Month *</label>
                            <input type="month" v-model="form.payrollMonth" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2">Status *</label>
                            <select v-model="form.postOption" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm">
                                <option value="draft">📝 Save as Draft</option>
                                <option value="posted">📤 Post for Approval</option>
                                <option value="approved">✅ Final Approval</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 border-t dark:border-gray-700">
                        <button type="submit" :disabled="form.processing" class="px-10 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold flex items-center gap-2">
                            <LoadingSpinner v-if="form.processing" />
                            <Calculator v-else class="w-5 h-5" />
                            Generate Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showPreviewModal" class="fixed inset-0 z-[100] bg-white dark:bg-gray-900 flex flex-col overflow-hidden font-sans">
            <div v-if="isPosting" class="absolute inset-0 z-[110] bg-white/80 dark:bg-gray-900/80 backdrop-blur-md flex flex-col items-center justify-center">
                <Loader2 class="w-16 h-16 text-blue-600 animate-spin mb-4" />
                <h3 class="text-2xl font-black uppercase tracking-widest text-gray-900 dark:text-white">Processing Batch...</h3>
            </div>

            <div class="bg-gray-900 text-white p-4 flex items-center justify-between shadow-2xl">
                <div>
                    <h2 class="text-xl font-black uppercase">Payroll Preview: {{ form.payrollMonth }}</h2>
                    <p class="text-xs text-gray-400">Total: {{ payrollResults.length }} Employees found</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showPreviewModal = false" :disabled="isPosting" class="px-6 py-2 bg-gray-700 rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-gray-600">
                        <X class="w-4 h-4" /> Close
                    </button>
                    <button v-if="form.postOption !== 'draft'" @click="confirmAndPostAll" :disabled="isPosting" class="px-8 py-2 bg-green-600 rounded-lg text-sm font-black flex items-center gap-2 shadow-lg hover:bg-green-500">
                        <CheckCircle class="w-4 h-4" /> {{ isPosting ? 'Posting...' : 'Confirm & Post All' }}
                    </button>
                </div>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="m-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                <p class="font-bold">Errors occurred while posting:</p>
                <ul class="list-disc list-inside text-xs"><li v-for="err in form.errors">{{ err }}</li></ul>
            </div>

            <div class="flex-1 overflow-auto p-4 bg-gray-50 dark:bg-gray-950">
                <table class="w-full border-collapse text-left text-[11px] lg:text-[12px] bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                    <thead class="sticky top-0 bg-gray-100 dark:bg-gray-900 z-10 border-b-2 border-gray-300 dark:border-gray-700">
                    <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                        <th class="p-4 font-black uppercase w-12 text-center">SL</th>
                        <th class="p-4 font-black uppercase">Employee & Bank Details</th>
                        <th class="p-4 font-black uppercase text-right">Basic</th>
                        <th class="p-4 font-black uppercase text-right">House</th>
                        <th class="p-4 font-black uppercase text-right">Med.</th>
                        <th class="p-4 font-black uppercase text-right">Trans.</th>
                        <th class="p-4 font-black uppercase text-right text-blue-600">Bonus</th>
                        <th class="p-4 font-black uppercase text-right bg-green-50 dark:bg-green-900/10 text-green-700">Gross</th>
                        <th class="p-4 font-black uppercase text-right text-red-600">Tax</th>
                        <th class="p-4 font-black uppercase text-right text-red-600">PF</th>
                        <th class="p-4 font-black uppercase text-right bg-blue-600 text-white w-32">Net Pay</th>
                        <th class="p-4 font-black uppercase text-center w-24">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(emp, idx) in payrollResults" :key="emp.id" class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition">
                        <td class="p-4 text-center font-bold text-gray-400">{{ idx + 1 }}</td>
                        <td class="p-4">
                            <div class="font-black uppercase text-sm">{{ emp.name }}</div>
                            <div class="text-[10px] text-blue-600 font-mono tracking-tighter">{{ emp.bank_name || 'N/A' }} | {{ emp.account_no || 'NO_ACC' }}</div>
                        </td>
                        <td class="p-4 text-right font-mono">{{ formatCurr(emp.basic_salary) }}</td>
                        <td class="p-4 text-right font-mono">{{ formatCurr(emp.house_rent) }}</td>
                        <td class="p-4 text-right font-mono">{{ formatCurr(emp.medical) }}</td>
                        <td class="p-4 text-right font-mono">{{ formatCurr(emp.transport) }}</td>
                        <td class="p-4 text-right font-mono text-blue-600 font-black">{{ formatCurr(emp.totalBonus) }}</td>
                        <td class="p-4 text-right font-black font-mono text-slate-800 dark:text-slate-200">{{ formatCurr(emp.gross_salary) }}</td>
                        <td class="p-4 text-right font-mono text-red-600">{{ formatCurr(emp.tax_deduction) }}</td>
                        <td class="p-4 text-right font-mono text-red-600">{{ formatCurr(emp.pf_deduction) }}</td>
                        <td class="p-4 text-right font-black font-mono bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 text-sm">{{ formatCurr(emp.net_salary) }}</td>
                        <td class="p-4 text-center">
                            <div v-if="form.postOption !== 'draft'" class="flex justify-center gap-2">
                                <button @click="postIndividual(emp)" class="p-2 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-full" title="Post Individual"><Send class="w-4 h-4 text-blue-600" /></button>
                                <button @click="viewPaySlip(emp)" class="p-2 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-full" title="Pay Slip"><FileText class="w-4 h-4 text-indigo-600" /></button>
                            </div>
                            <span v-else class="text-xs italic text-gray-400">—</span>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot class="sticky bottom-0 bg-gray-900 text-white font-black text-xs shadow-2xl">
                    <tr>
                        <td colspan="2" class="p-4 text-right uppercase text-gray-400">Monthly Grand Totals</td>
                        <td class="p-4 text-right font-mono">{{ formatCurr(payrollResults.reduce((a,b)=>a+Number(b.basic_salary),0)) }}</td>
                        <td colspan="4"></td>
                        <td class="p-4 text-right font-mono text-green-400">{{ formatCurr(payrollResults.reduce((a,b)=>a+Number(b.gross_salary),0)) }}</td>
                        <td colspan="2"></td>
                        <td class="p-4 text-right font-mono text-yellow-400 text-sm">{{ formatCurr(totalNet) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-6 bg-white dark:bg-gray-900 border-t flex flex-col sm:flex-row justify-between items-center gap-6 shadow-2xl">
                <div class="flex gap-12">
                    <div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Staff</p><p class="text-xl font-black">{{ payrollResults.length }}</p></div>
                    <div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Net Payable</p><p class="text-xl font-black text-blue-600">{{ formatCurr(totalNet) }}</p></div>
                </div>
                <button class="w-full sm:w-auto px-10 py-3 bg-gray-900 text-white rounded-lg text-sm font-black transition hover:bg-black uppercase tracking-widest flex items-center justify-center gap-3">
                    <Printer class="w-5 h-5" /> Bulk Print Report
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fixed { z-index: 9999; }
.overflow-auto { scrollbar-width: thin; scrollbar-color: #4b5563 transparent; }
table { line-height: 1.4; border-spacing: 0; }
th, td { white-space: nowrap; }
</style>
