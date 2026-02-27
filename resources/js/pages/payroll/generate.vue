<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'
import PayrollPreview from './PayrollPreview.vue'
import {
    Calculator, X, Printer, CheckCircle,
    Send, FileText, Loader2, UserCheck
} from 'lucide-vue-next'

const props = defineProps<{
    departments: string[],
    offices: string[],
    divisions: string[],
}>()

const showPreviewModal = ref(false)
const isPosting = ref(false)
const payrollResults = ref<any[]>([])

const form = useForm({
    is_individual: false, // Checkbox state
    department: '',
    division: '',
    office: '',
    employee_search: '',
    payrollDate: new Date().toISOString().split('T')[0],
    payrollMonth: new Date().toISOString().slice(0, 7),
    approvedBy: '',
    notes: '',
    postOption: 'draft',
})

const submitForm = () => {
    form.post('/payroll/generate', {
        preserveState: true,
        preserveScroll: true,
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
        },
        onFinish: () => isPosting.value = false
    });
}

const viewPaySlip = (emp: any) => {
    const url = `/payroll/payslip/${emp.empId}?month=${form.payrollMonth}`
    window.open(url, '_blank')
}
</script>
<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/' },{ title: 'Payroll',  href: '/payroll' }, { title: 'Generate' }]">

        <div class="bg-white dark:bg-gray-800 shadow-lg p-6 border border-gray-200 dark:border-gray-700 rounded-2xl">
            <div class="max-w-[95%] mx-auto p-4 lg:p-6">
                <form @submit.prevent="submitForm" class="space-y-6">

                    <div class="flex items-center gap-3 p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border-2 border-blue-100 dark:border-blue-800/50 w-fit">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_individual" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-xs font-black uppercase tracking-widest text-blue-700 dark:text-blue-400 flex items-center gap-2">
                            <UserCheck class="w-4 h-4" />
                            Individual Selection Mode
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Office / Location</label>
                            <select v-model="form.office" :disabled="form.is_individual" class="w-full h-11 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm shadow-sm disabled:opacity-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all">
                                <option value="">All Offices</option>
                                <option v-for="off in offices" :key="off" :value="off">{{ off }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Division</label>
                            <select v-model="form.division" :disabled="form.is_individual" class="w-full h-11 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm shadow-sm disabled:opacity-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all">
                                <option value="">All Divisions</option>
                                <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Department</label>
                            <select v-model="form.department" :disabled="form.is_individual" class="w-full h-11 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm shadow-sm disabled:opacity-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-if="form.is_individual" class="transition-all duration-300">
                            <label class="block text-xs font-black uppercase tracking-widest text-blue-600 mb-2">Search Employee (ID / Name)</label>
                            <input
                                type="text"
                                v-model="form.employee_search"
                                required
                                placeholder="Enter Employee ID or Name..."
                                class="w-full h-11 rounded-xl border-2 border-blue-300 dark:border-blue-800 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all"
                            />
                        </div>

                        <div v-else class="hidden md:block"></div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Payroll Month *</label>
                            <input type="month" v-model="form.payrollMonth" required class="w-full h-11 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all" />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Processing Status *</label>
                            <select v-model="form.postOption" class="w-full h-11 rounded-xl border-2 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm shadow-sm font-bold focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all">
                                <option value="draft">📝 Save as Draft</option>
                                <option value="posted">📤 Post for Approval</option>
                                <option value="approved">✅ Final Approval</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 border-t dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="cursor-pointer px-12 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-xs flex items-center gap-3 transition active:scale-95 shadow-lg shadow-blue-500/30"
                        >
                            <LoadingSpinner v-if="form.processing" />
                            <Calculator v-else class="w-5 h-5" />
                            Generate Payroll Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <PayrollPreview
        :show="showPreviewModal"
        :is-posting="isPosting"
        :payroll-results="payrollResults"
        :form-errors="form.errors"
        :payroll-month="form.payrollMonth"
        :office="form.office"
        :post-option="form.postOption"
        @close="showPreviewModal = false"
        @confirm="confirmAndPostAll"
        @viewPaySlip="viewPaySlip"
    />
    </AppLayout>
</template>
