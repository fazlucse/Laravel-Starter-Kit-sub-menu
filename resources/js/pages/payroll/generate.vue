<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
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

// Modal and Processing State
const showPreviewModal = ref(false)
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

// Main Form Submission
const submitForm = () => {
    form.post('/payroll/generate', {
        preserveState: true,
        onSuccess: (page) => {
            // Extract the calculated data from props
            payrollResults.value = page.props.payrollData as any[] || []
            showPreviewModal.value = true
        },
    })
}

// Action: Post Individual Record
const postIndividual = (emp: any) => {
    if(!confirm(`Post payroll for ${emp.name}?`)) return

    processingId.value = emp.id
    // Simulated Post Route
    form.post(`/payroll/post-individual/${emp.id}`, {
        preserveScroll: true,
        onFinish: () => processingId.value = null,
        onSuccess: () => alert('Payroll posted for ' + emp.name)
    })
}

// Action: View Pay Slip
const viewPaySlip = (emp: any) => {
    const url = `/payroll/payslip/${emp.id}?month=${form.payrollMonth}`
    window.open(url, '_blank')
}

// Helper: Format Currency
const formatCurr = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Payroll', href: '/payroll' },
            { title: 'Generate' },
        ]"
    >
        <div class="max-w-[95%] mx-auto p-2 sm:p-4 lg:p-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-8 border border-sidebar-border/70">

                <div v-if="Object.keys(form.errors).length > 0"
                     class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm font-bold text-red-800 dark:text-red-400">Please correct the errors before proceeding.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-10">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b dark:border-gray-700 pb-3">Payroll Configuration</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                <select v-model="form.department" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 dark:bg-gray-700 dark:text-white">
                                    <option value="">All Departments</option>
                                    <option v-for="dept in (departments || [])" :key="dept" :value="dept">{{ dept }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Office</label>
                                <select v-model="form.office" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 dark:bg-gray-700 dark:text-white">
                                    <option value="">All Offices</option>
                                    <option v-for="office in (offices || [])" :key="office" :value="office">{{ office }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payroll Month *</label>
                                <input type="month" v-model="form.payrollMonth" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 dark:bg-gray-700 dark:text-white" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Submission Status *</label>
                                <select v-model="form.postOption" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 dark:bg-gray-700 dark:text-white">
                                    <option value="draft">📝 Save as Draft</option>
                                    <option value="posted">📤 Post for Approval</option>
                                    <option value="approved">✅ Final Approval</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <Link href="/payroll" class="px-10 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg font-bold transition hover:bg-gray-200">Cancel</Link>
                        <button type="submit" :disabled="form.processing"
                                class="px-12 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-3 font-bold shadow-lg disabled:bg-blue-400">
                            <LoadingSpinner v-if="form.processing" />
                            <Calculator v-else class="w-5 h-5" />
                            {{ form.processing ? 'Calculating...' : 'Generate New Payroll' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showPreviewModal" class="fixed inset-0 z-[100] bg-white dark:bg-gray-900 flex flex-col overflow-hidden">
            <div class="bg-gray-900 text-white p-4 flex items-center justify-between shadow-xl">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-tight">Payroll Preview: {{ form.payrollMonth }}</h2>
                    <p class="text-xs text-gray-400">Calculated on: {{ form.payrollDate }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showPreviewModal = false" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-bold flex items-center gap-2 transition">
                        <X class="w-4 h-4" /> Close
                    </button>
                    <button class="px-6 py-2 bg-green-600 hover:bg-green-500 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg transition">
                        <CheckCircle class="w-4 h-4" /> Confirm & Post All
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-auto p-4 bg-gray-50 dark:bg-gray-950">
                <table class="w-full border-collapse text-left text-[11px] bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <thead class="sticky top-0 bg-gray-100 dark:bg-gray-900 z-10">
                    <tr class="divide-x divide-gray-300 dark:divide-gray-700 border-b-2 border-gray-300 dark:border-gray-600">
                        <th class="p-3 font-black uppercase">Staff & Bank Info</th>
                        <th class="p-3 font-black uppercase">Dept/Div</th>
                        <th class="p-3 font-black uppercase text-right">Basic</th>
                        <th class="p-3 font-black uppercase text-right">Med.</th>
                        <th class="p-3 font-black uppercase text-right">House</th>
                        <th class="p-3 font-black uppercase text-right">Trans.</th>
                        <th class="p-3 font-black uppercase text-right">Other</th>
                        <th class="p-3 font-black uppercase text-right bg-green-50 dark:bg-green-900/10 text-green-700">Gross</th>
                        <th class="p-3 font-black uppercase text-right text-red-500">Tax</th>
                        <th class="p-3 font-black uppercase text-right text-red-500">PF</th>
                        <th class="p-3 font-black uppercase text-right text-red-500">Other Ded.</th>
                        <th class="p-3 font-black uppercase text-right bg-blue-600 text-white">Net Pay</th>
                        <th class="p-3 font-black uppercase text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="emp in payrollResults" :key="emp.id" class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition">
                        <td class="p-3">
                            <div class="font-bold text-gray-900 dark:text-white uppercase">{{ emp.name }}</div>
                            <div class="text-[10px] text-blue-600 dark:text-blue-400 font-medium font-mono">
                                {{ emp.bank_name || 'N/A' }} — {{ emp.account_no || 'NO_ACC' }}
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium text-gray-700 dark:text-gray-300">{{ emp.department }}</div>
                            <div class="text-[10px] text-gray-500 italic">{{ emp.division }}</div>
                        </td>
                        <td class="p-3 text-right font-mono">{{ formatCurr(emp.basic_salary) }}</td>
                        <td class="p-3 text-right font-mono">{{ formatCurr(emp.medical) }}</td>
                        <td class="p-3 text-right font-mono">{{ formatCurr(emp.house_rent) }}</td>
                        <td class="p-3 text-right font-mono">{{ formatCurr(emp.transport) }}</td>
                        <td class="p-3 text-right font-mono">{{ formatCurr(emp.other_allowance) }}</td>

                        <td class="p-3 text-right font-bold font-mono bg-green-50/50 dark:bg-green-900/5">
                            {{ formatCurr(Number(emp.basic_salary) + Number(emp.medical) + Number(emp.house_rent) + Number(emp.transport) + Number(emp.other_allowance)) }}
                        </td>

                        <td class="p-3 text-right font-mono text-red-600">{{ formatCurr(emp.tax_deduction) }}</td>
                        <td class="p-3 text-right font-mono text-red-600">{{ formatCurr(emp.pf_deduction) }}</td>
                        <td class="p-3 text-right font-mono text-red-600">{{ formatCurr(emp.other_deduction) }}</td>

                        <td class="p-3 text-right font-black font-mono bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">
                            {{ formatCurr(emp.net_salary) }}
                        </td>

                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="postIndividual(emp)" :disabled="processingId === emp.id"
                                        class="p-2 bg-gray-100 dark:bg-gray-700 hover:bg-blue-600 hover:text-white rounded-md transition disabled:opacity-50" title="Post Individually">
                                    <Loader2 v-if="processingId === emp.id" class="w-3.5 h-3.5 animate-spin" />
                                    <Send v-else class="w-3.5 h-3.5" />
                                </button>
                                <button @click="viewPaySlip(emp)"
                                        class="p-2 bg-gray-100 dark:bg-gray-700 hover:bg-indigo-600 hover:text-white rounded-md transition" title="View Pay Slip">
                                    <FileText class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot class="bg-gray-900 text-white font-bold sticky bottom-0">
                    <tr>
                        <td colspan="2" class="p-3 text-right uppercase tracking-widest text-gray-400">Total Disbursement</td>
                        <td class="p-3 text-right font-mono">{{ formatCurr(payrollResults.reduce((a, b) => a + Number(b.basic_salary), 0)) }}</td>
                        <td colspan="8" class="p-3 text-right">Net Grand Total:</td>
                        <td class="p-3 text-right font-mono text-yellow-400 text-sm">
                            {{ formatCurr(payrollResults.reduce((a, b) => a + Number(b.net_salary), 0)) }}
                        </td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-4 bg-white dark:bg-gray-900 border-t dark:border-gray-800 flex justify-between items-center shadow-2xl">
                <div class="flex gap-10">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Total Employees</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white">{{ payrollResults.length }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Total Payable</p>
                        <p class="text-xl font-black text-blue-600">{{ formatCurr(payrollResults.reduce((a, b) => a + Number(b.net_salary), 0)) }}</p>
                    </div>
                </div>
                <button class="flex items-center gap-2 px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <Printer class="w-4 h-4" /> Export Report (PDF)
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fixed {
    z-index: 9999;
}
/* For Firefox scrollbar styling */
.overflow-auto {
    scrollbar-width: thin;
    scrollbar-color: #4b5563 transparent;
}
</style>
