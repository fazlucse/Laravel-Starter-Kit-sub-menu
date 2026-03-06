<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import Swal from 'sweetalert2'
import AppLayout from '@/layouts/AppLayout.vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'
import PayrollPreview from './PayrollPreview.vue'
// Import the custom date picker
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import {
    Calculator, UserCheck, Gift, Calendar, Loader2, Banknote
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
    is_individual: false,
    is_salary: true,
    is_bonus: false,
    bonus_type: '',
    bonus_date: new Date().toISOString().split('T')[0],
    department: '',
    division: '',
    office: '',
    employee_search: '',
    // Flatpickr handles the month string well
    payrollMonth: new Date().toISOString().slice(0, 7),
    postOption: 'draft',
})

const submitForm = () => {
    if (!form.is_salary && !form.is_bonus) {
        alert('Please select at least "Salary Only" or "Bonus Only" to proceed.');
        return;
    }

    form.post('/payroll/generate', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            payrollResults.value = page.props.payrollData as any[] || []
            showPreviewModal.value = true
        },
    })
}

// --- Inside Generate.vue <script setup> ---

const confirmAndPostAll = () => {
    // We set isPosting to true to trigger the "Finalizing Records..." overlay in the modal
    isPosting.value = true;
    const finalData = payrollResults.value.map(emp => ({
        ...emp,
        remarks: emp.remarks || '' // Force empty string if null/undefined
    }));

    // 2. LOG THE DATA TO CONSOLE
    console.log("--- PAYROLL BATCH DATA SENDING ---");
    console.log("Month:", form.payrollMonth);
    console.log("Records Count:", finalData.length);
    console.log("Sample First Row:", finalData[0]); // Check if 'remarks' is here
    console.log("Full Payload:", finalData);
    form.transform((data) => ({
        ...data,
        payroll_data: payrollResults.value.map(emp => ({
            ...emp,
            remarks: emp.remarks || ''
        }))
    })).post('/payroll/store-batch', {
        onSuccess: () => {
            showPreviewModal.value = false;
            Swal.fire({
                title: 'Success!',
                text: 'Payroll batch has been created.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        },
        onError: () => {
            // Stop the loading overlay if there is a database error so user sees the red box
            isPosting.value = false;
        },
        onFinish: () => {
            isPosting.value = false;
        }
    });
}

// Add these placeholders if they are missing
const handleIndividualPost = (emp: any, callback: Function) => {
    // Logic for individual post
    form.post(`/payroll/post-single/${emp.id}`, { onFinish: () => callback() });
}

const viewPaySlip = (emp: any) => {
    window.open(`/payroll/payslip/${emp.id}`, '_blank');
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/' },{ title: 'Payroll',  href: '/payroll' }, { title: 'Generate' }]">
        <Head title="Generate Payroll" />

        <div class="bg-white dark:bg-gray-800 shadow-lg p-6 border border-gray-200 dark:border-gray-700 rounded-2xl">
            <div class="max-w-[95%] mx-auto p-4 lg:p-6">
                <form @submit.prevent="submitForm" class="space-y-8">

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-3 p-3 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-800/50">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_individual" class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-400 flex items-center gap-1">
                                <UserCheck class="w-3 h-3" /> Individual Mode
                            </span>
                        </div>

                        <div class="flex items-center gap-6 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_salary" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 group-hover:text-blue-600 transition-colors flex items-center gap-1">
                                    <Banknote class="w-3 h-3" /> Salary Only
                                </span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_bonus" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 group-hover:text-blue-600 transition-colors flex items-center gap-1">
                                    <Gift class="w-3 h-3" /> Bonus Only
                                </span>
                            </label>
                        </div>
                    </div>

                    <div v-if="form.is_bonus" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-in fade-in slide-in-from-top-2">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 flex items-center gap-2">
                                <Gift class="w-4 h-4" /> Bonus Type *
                            </label>
                            <select v-model="form.bonus_type" :required="form.is_bonus"
                                    class="w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all shadow-sm">
                                <option value="">Select Bonus Category</option>
                                <option value="festival">Festival / Eid Bonus</option>
                                <option value="performance">Performance / KPI</option>
                                <option value="annual">Annual Profit Bonus</option>
                                <option value="other">Other Bonus</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 flex items-center gap-2">
                                <Calendar class="w-4 h-4" /> Bonus Disbursement Date *
                            </label>
                            <FlatpickrInput
                                v-model="form.bonus_date"
                                placeholder="Select Date"
                                :config="{ dateFormat: 'Y-m-d' }"
                                class="w-full h-11"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Office</label>
                            <select v-model="form.office" :disabled="form.is_individual" class="w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none disabled:opacity-40 transition-all">
                                <option value="">All Locations</option>
                                <option v-for="off in offices" :key="off" :value="off">{{ off }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Division</label>
                            <select v-model="form.division" :disabled="form.is_individual" class="w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none disabled:opacity-40 transition-all">
                                <option value="">All Divisions</option>
                                <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Department</label>
                            <select v-model="form.department" :disabled="form.is_individual" class="w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none disabled:opacity-40 transition-all">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-if="form.is_individual" class="animate-in fade-in zoom-in-95">
                            <label class="block text-xs font-black uppercase tracking-widest text-blue-600 mb-2 flex items-center gap-1">
                                <UserCheck class="w-3 h-3" /> Employee ID/Name *
                            </label>
                            <input type="text" v-model="form.employee_search" required placeholder="Type ID or Name..."
                                   class="w-full h-11 rounded-xl border-2 border-blue-200 dark:border-blue-900 bg-white dark:bg-gray-900 text-sm focus:ring-1 focus:ring-blue-600 outline-none" />
                        </div>
                        <div v-else></div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Payroll Month *</label>
                            <FlatpickrInput
                                v-model="form.payrollMonth"
                                placeholder="Select Month"
                                :config="{
                                    dateFormat: 'Y-m',
                                    plugins: [] // Note: If your component supports the MonthSelect plugin, add it here
                                }"
                                class="w-full h-11"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Processing Status *</label>
                            <select v-model="form.postOption" class="w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm font-bold focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none">
                                <option value="draft">📝 Save as Draft</option>
                                <option value="posted">📤 Post for Approval</option>
                                <option value="approved">✅ Final Approval</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-8 border-t dark:border-gray-700">
                        <button type="submit" :disabled="form.processing"
                                class="cursor-pointer px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black uppercase tracking-widest text-[11px] flex items-center gap-3 shadow-xl shadow-blue-500/20 active:scale-95 transition-all">
                            <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                            <Calculator v-else class="w-5 h-5" />
                            Generate Records
                        </button>
                    </div>
                </form>
            </div>
        </div>

<!--        <PayrollPreview-->
<!--            :show="showPreviewModal"-->
<!--            :payroll-results="payrollResults"-->
<!--            :form-errors="form.errors"-->
<!--            @close="showPreviewModal = false"-->
<!--            @confirm="confirmAndPostAll"-->
<!--        />-->
        <PayrollPreview
            :show="showPreviewModal"
            :is-posting="isPosting"
            :payroll-results="payrollResults"
            :form-errors="$page.props.errors"
            :payroll-month="form.payrollMonth"
            :office="form.office"
            :post-option="form.postOption"
            @close="showPreviewModal = false"
            @confirm="confirmAndPostAll"
            @post-individual="handleIndividualPost"
            @view-pay-slip="viewPaySlip"
        />
    </AppLayout>
</template>
