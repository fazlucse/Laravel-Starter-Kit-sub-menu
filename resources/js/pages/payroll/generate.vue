<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import LoadingSpinner from '@/Components/custom/LoadingSpinner.vue'
import { Calculator } from 'lucide-vue-next'

const props = defineProps<{
    departments?: string[],
    offices?: string[],
    payrollHistory?: any[]
}>()

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
        preserveScroll: true,
    })
}

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        posted: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
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
                    <p class="text-sm font-medium text-red-800 dark:text-red-400">Please correct the errors before proceeding.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-10">

                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b dark:border-gray-700 pb-3">Payroll Configuration</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                <select v-model="form.department"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">All Departments</option>
                                    <option v-for="dept in (departments || [])" :key="dept" :value="dept">{{ dept }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Office</label>
                                <select v-model="form.office"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">All Offices</option>
                                    <option v-for="office in (offices || [])" :key="office" :value="office">{{ office }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payroll Month <span class="text-red-500">*</span></label>
                                <input type="month" v-model="form.payrollMonth" required
                                       class="w-full rounded-lg border px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                       :class="form.errors.payrollMonth ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Submission Status <span class="text-red-500">*</span></label>
                                <select v-model="form.postOption" required
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="draft">📝 Save as Draft</option>
                                    <option value="posted">📤 Post for Approval</option>
                                    <option value="approved">✅ Final Approval</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Generation Date <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.payrollDate" required
                                       class="w-full rounded-lg border px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                       :class="form.errors.payrollDate ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Approving Authority</label>
                                <input type="text" v-model="form.approvedBy" placeholder="Name of approver"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Internal Notes</label>
                                <input type="text" v-model="form.notes"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="Add optional remarks or instructions here..." />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <Link href="/payroll"
                              class="px-10 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-bold transition">
                            Cancel
                        </Link>

                        <button type="submit" :disabled="form.processing"
                                class="cursor-pointer px-12 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition flex items-center justify-center gap-3 font-bold shadow-lg transform active:scale-95">
                            <LoadingSpinner v-if="form.processing" />
                            <Calculator v-if="!form.processing" class="w-5 h-5" />
                            {{ form.processing ? 'Processing Payroll...' : 'Generate New Payroll' }}
                        </button>
                    </div>
                </form>

                <div class="mt-20 pt-10 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-end justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Payroll History</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review and manage previously generated payroll reports.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Target Month</th>
                                <th class="px-8 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Current Status</th>
                                <th class="px-8 py-5 text-right text-xs font-black text-gray-400 uppercase tracking-widest">Staff Count</th>
                                <th class="px-8 py-5 text-right text-xs font-black text-gray-400 uppercase tracking-widest">Operations</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <tr v-for="item in (payrollHistory || [])" :key="item.id" class="hover:bg-blue-50/20 dark:hover:bg-gray-700/40 transition">
                                <td class="px-8 py-5 text-base font-bold text-gray-900 dark:text-white">
                                    {{ item.payroll_month }}
                                </td>
                                <td class="px-8 py-5">
                                        <span :class="getStatusClass(item.status)" class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider">
                                            {{ item.status }}
                                        </span>
                                </td>
                                <td class="px-8 py-5 text-base text-right text-gray-600 dark:text-gray-300 font-mono font-bold">
                                    {{ item.employee_count }}
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <Link :href="`/payroll/${item.id}`" class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-sm font-black transition">
                                        Open Report
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!(payrollHistory && payrollHistory.length)">
                                <td colspan="4" class="px-8 py-20 text-center text-gray-400 dark:text-gray-500 italic">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="text-lg">No records found</span>
                                        <span class="text-sm">Change your filters or generate your first payroll above.</span>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
