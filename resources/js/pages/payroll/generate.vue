<!-- resources/js/Pages/Payroll/Generate.vue -->
<template>
    <AppLayout
        title="Generate Payroll"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Payroll', href: '/payroll' },
      { title: 'Generate New Payroll', href: '#' },
    ]"
    >
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-lg p-8">

                    <!-- Global Form Errors -->
                    <div
                        v-if="Object.keys(form.errors).length > 0"
                        class="mb-8 p-5 bg-red-50 border border-red-200 rounded-xl text-red-800"
                    >
                        <p class="font-medium mb-3 text-red-900">Please correct the following errors:</p>
                        <ul class="list-disc pl-6 space-y-1.5 text-sm">
                            <li v-for="(message, field) in form.errors" :key="field">
                <span class="font-medium capitalize">
                  {{ field.replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:
                </span>
                                {{ message }}
                            </li>
                        </ul>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department (optional)</label>
                                <select
                                    v-model="form.department"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="dept in departments" :key="dept" :value="dept">
                                        {{ dept }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Office (optional)</label>
                                <select
                                    v-model="form.office"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                                >
                                    <option value="">All Offices</option>
                                    <option v-for="office in offices" :key="office" :value="office">
                                        {{ office }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Dates & People -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Payroll Date <span class="text-red-600">*</span>
                                </label>
                                <input
                                    type="date"
                                    v-model="form.payrollDate"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                                >
                                <p v-if="form.errors.payrollDate" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.payrollDate }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Payroll Month <span class="text-red-600">*</span>
                                </label>
                                <input
                                    type="month"
                                    v-model="form.payrollMonth"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                                >
                                <p v-if="form.errors.payrollMonth" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.payrollMonth }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Prepared By <span class="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.preparedBy"
                                    required
                                    placeholder="Your name"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                                >
                                <p v-if="form.errors.preparedBy" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.preparedBy }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Approved By (optional)</label>
                                <input
                                    type="text"
                                    v-model="form.approvedBy"
                                    placeholder="Approver name"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                                >
                                <p v-if="form.errors.approvedBy" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.approvedBy }}
                                </p>
                            </div>
                        </div>

                        <!-- Indian Statutory Components -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Indian Statutory Components (optional – select multiple)
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <label
                                    v-for="opt in indianPostOptions"
                                    :key="opt.value"
                                    class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors border border-gray-200"
                                >
                                    <input
                                        type="checkbox"
                                        :value="opt.value"
                                        v-model="form.indianComponents"
                                        class="rounded text-indigo-600 focus:ring-indigo-500 h-5 w-5"
                                    >
                                    <div>
                                        <div class="font-medium text-gray-800">{{ opt.label }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ opt.fixed ? `₹${opt.rate} (fixed)` : `${opt.rate}% of basic` }}
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Post Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Post Status <span class="text-red-600">*</span>
                            </label>
                            <select
                                v-model="form.postOption"
                                required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                            >
                                <option value="draft">📝 Draft – save for later editing</option>
                                <option value="posted">📤 Posted – submit for approval</option>
                                <option value="approved">✅ Approved – final & locked</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                            <textarea
                                v-model="form.notes"
                                rows="4"
                                placeholder="Any additional remarks or special instructions..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4"
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-4 pt-6 border-t">
                            <button
                                type="button"
                                @click="$inertia.visit(route('payroll.index'))"
                                class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-medium"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium shadow-sm flex items-center gap-2 disabled:opacity-70"
                            >
                                <Calculator class="w-5 h-5" />
                                <span v-if="!form.processing">Generate Payroll</span>
                                <span v-else>Generating...</span>
                            </button>
                        </div>
                    </form>

                    <!-- === Payroll History Section === -->
                    <div class="mt-16 pt-10 border-t border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Previous Payrolls</h2>

                        <div v-if="payrollHistory.length === 0" class="text-center py-12 text-gray-500 bg-gray-50 rounded-xl">
                            No payrolls have been generated yet.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Month
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Prepared by
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employees
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in payrollHistory" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.payroll_month }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                          :class="getStatusClass(item.status)"
                          class="px-2.5 py-1 rounded-full text-xs font-medium"
                      >
                        {{ item.status.toUpperCase() }}
                      </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.prepared_by || '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ item.employee_count || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="$inertia.visit(route('payroll.show', item.id))"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Calculator } from 'lucide-vue-next'

const props = defineProps({
    departments: {
        type: Array,
        default: () => []
    },
    offices: {
        type: Array,
        default: () => []
    },
    indianPostOptions: {
        type: Array,
        default: () => []
    },
    payrollHistory: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    department: '',
    office: '',
    payrollDate: new Date().toISOString().split('T')[0],
    payrollMonth: new Date().toISOString().slice(0, 7),
    preparedBy: '',
    approvedBy: '',
    notes: '',
    postOption: 'draft',
    indianComponents: []
})

const submitForm = () => {
    form.post(route('payroll.generate'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Page will be replaced by Report page automatically
        }
    })
}

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        posted: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
