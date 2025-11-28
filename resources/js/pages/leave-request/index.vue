<template>
    <AppLayout
        title="Leave Request"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Leave Requests', href: '/leave-requests' },
      { title: mode === 'create' ?'Submit Leave Request' : 'Edit Leave Request', href: '#' },
    ]"
    >
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto p-2 sm:p-4">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 rounded-t-xl shadow-lg pt-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center">
                        {{ mode === 'create' ? 'Submit Leave Request' : 'Edit Leave Request' }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
                        Complete all fields. Fields marked with * are required.
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-lg rounded-b-xl p-8 space-y-6">

                    <!-- Employee Selection with Auto-suggestion -->
                    <div class="input-wrapper">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Employee <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="employeeSearch"
                                @input="filterEmployees"
                                @focus="showEmployeeDropdown = true"
                                type="text"
                                placeholder="Type to search employee..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            />
                            <div
                                v-if="showEmployeeDropdown && filteredEmployees.length"
                                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                            >
                                <div
                                    v-for="emp in filteredEmployees"
                                    :key="emp.id"
                                    @click="selectEmployee(emp)"
                                    class="px-4 py-2 hover:bg-blue-50 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-gray-100"
                                >
                                    {{ emp.name }}
                                </div>
                            </div>
                        </div>
                        <p v-if="errors.employee_id" class="mt-1 text-sm text-red-600">{{ errors.employee_id }}</p>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-wrapper">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                From Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.from_date"
                                type="date"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            />
                            <p v-if="errors.from_date" class="mt-1 text-sm text-red-600">{{ errors.from_date }}</p>
                        </div>

                        <div class="input-wrapper">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                To Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.to_date"
                                type="date"
                                required
                                :min="form.from_date"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            />
                            <p v-if="errors.to_date" class="mt-1 text-sm text-red-600">{{ errors.to_date }}</p>
                        </div>
                    </div>

                    <!-- Total Days Display -->
                    <div v-if="form.from_date && form.to_date" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            Total Leave Days: <span class="text-lg font-bold">{{ calculateLeaveDays() }}</span>
                        </p>
                    </div>

                    <!-- Leave Reason -->
                    <div class="input-wrapper">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Leave Reason <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <SelectInput
                                v-model="form.leave_reason"
                                label=""
                                :options="leaveReasonOptions"
                                required
                                :error="errors.leave_reason"
                            />

                            <!-- Other Reason Input -->
                            <input
                                v-if="form.leave_reason === 'other'"
                                v-model="form.other_reason"
                                type="text"
                                placeholder="Please specify reason..."
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>
                        <p v-if="errors.other_reason" class="mt-1 text-sm text-red-600">{{ errors.other_reason }}</p>
                    </div>

                    <!-- Reliever Selection with Auto-suggestion -->
                    <div class="input-wrapper">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reliever <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="relieverSearch"
                                @input="filterRelievers"
                                @focus="showRelieverDropdown = true"
                                type="text"
                                placeholder="Type to search reliever..."
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            />
                            <div
                                v-if="showRelieverDropdown && filteredRelievers.length"
                                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                            >
                                <div
                                    v-for="emp in filteredRelievers"
                                    :key="emp.id"
                                    @click="selectReliever(emp)"
                                    class="px-4 py-2 hover:bg-blue-50 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-gray-100"
                                >
                                    {{ emp.name }}
                                </div>
                            </div>
                        </div>
                        <p v-if="errors.reliever_id" class="mt-1 text-sm text-red-600">{{ errors.reliever_id }}</p>
                    </div>

                    <!-- Remarks -->
                    <div class="input-wrapper">
                        <TextareaInput
                            v-model="form.remarks"
                            label="Remarks"
                            rows="4"
                            placeholder="Add any additional details about your leave request..."
                        />
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="$inertia.visit('/leave-requests')"
                            class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="cursor-pointer px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <LoadingSpinner v-if="form.processing" />
                            {{ mode === 'create' ? 'Submit Request' : 'Update Request' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextareaInput from '@/Components/TextareaInput.vue'
import LoadingSpinner from '@/Components/custom/LoadingSpinner.vue'

const props = defineProps({
    mode: { type: String, required: true },
    employees: { type: Array, default: () => [] },
    errors: Object
})

const leaveReasonOptions = [
    { label: 'Select Reason', value: '' },
    { label: 'Sick Leave', value: 'sick' },
    { label: 'Casual Leave', value: 'casual' },
    { label: 'Annual Leave', value: 'annual' },
    { label: 'Emergency Leave', value: 'emergency' },
    { label: 'Maternity Leave', value: 'maternity' },
    { label: 'Paternity Leave', value: 'paternity' },
    { label: 'Other', value: 'other' }
]

// Auto-suggestion states
const employeeSearch = ref('')
const relieverSearch = ref('')
const filteredEmployees = ref([])
const filteredRelievers = ref([])
const showEmployeeDropdown = ref(false)
const showRelieverDropdown = ref(false)

// Form
const form = useForm({
    employee_id: null,
    from_date: '',
    to_date: '',
    leave_reason: '',
    other_reason: '',
    reliever_id: null,
    remarks: ''
})

// Reactive errors
const errors = reactive({ ...props.errors })

// Calculate leave days
function calculateLeaveDays() {
    if (!form.from_date || !form.to_date) return 0
    const start = new Date(form.from_date)
    const end = new Date(form.to_date)
    const diffTime = Math.abs(end - start)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays + 1 // Include both start and end dates
}

// Employee filtering
function filterEmployees() {
    const search = employeeSearch.value.toLowerCase()
    if (search.length < 1) {
        filteredEmployees.value = props.employees
    } else {
        filteredEmployees.value = props.employees.filter(emp =>
            emp.name.toLowerCase().includes(search)
        )
    }
    showEmployeeDropdown.value = true
}

function selectEmployee(emp) {
    form.employee_id = emp.id
    employeeSearch.value = emp.name
    showEmployeeDropdown.value = false
}

// Reliever filtering
function filterRelievers() {
    const search = relieverSearch.value.toLowerCase()
    if (search.length < 1) {
        filteredRelievers.value = props.employees.filter(emp => emp.id !== form.employee_id)
    } else {
        filteredRelievers.value = props.employees.filter(emp =>
            emp.id !== form.employee_id && emp.name.toLowerCase().includes(search)
        )
    }
    showRelieverDropdown.value = true
}

function selectReliever(emp) {
    form.reliever_id = emp.id
    relieverSearch.value = emp.name
    showRelieverDropdown.value = false
}

// Close dropdowns on outside click
document.addEventListener('click', (e) => {
    if (!e.target.closest('.input-wrapper')) {
        showEmployeeDropdown.value = false
        showRelieverDropdown.value = false
    }
})

// Submit
function submit() {
    Object.assign(errors, {}) // clear errors

    // Validation
    if (!form.employee_id) errors.employee_id = 'Select an employee'
    if (!form.from_date) errors.from_date = 'From date is required'
    if (!form.to_date) errors.to_date = 'To date is required'
    if (form.from_date && form.to_date && form.from_date > form.to_date) {
        errors.to_date = 'To date must be after from date'
    }
    if (!form.leave_reason) errors.leave_reason = 'Leave reason is required'
    if (form.leave_reason === 'other' && !form.other_reason) {
        errors.other_reason = 'Please specify the reason'
    }
    if (!form.reliever_id) errors.reliever_id = 'Reliever is required'

    if (Object.keys(errors).length) return

    const method = props.mode === 'create' ? 'post' : 'put'
    const url = props.mode === 'create' ? '/leave-requests' : `/leave-requests/${form.id}`

    form[method](url, {
        onSuccess: () => {
            if (props.mode === 'create') {
                form.reset()
                employeeSearch.value = ''
                relieverSearch.value = ''
            }
        }
    })
}
</script>
