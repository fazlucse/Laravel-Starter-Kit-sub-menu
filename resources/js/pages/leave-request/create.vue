<template>
    <AppLayout
        title="Leave Request"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Leave Requests', href: '/leave-requests' },
            { title: mode === 'create' ? 'Add Leave Request' : 'Edit Leave Request', href: '#' },
        ]"
    >
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto p-2 sm:p-4">

            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-t-xl shadow-lg pt-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center">
                    {{ mode === 'create' ? 'Add Leave Request' : 'Edit Leave Request' }}
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
                    Complete all fields. Fields marked with * are required.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-lg rounded-b-xl p-8 space-y-6">

                <!-- Request For: Self / Other -->
                <div v-if="form.employee_scope === 'individual'" class="input-wrapper flex items-center gap-6">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                        Apply Leave For <span class="text-red-500">*</span>
                    </label>

                    <!-- Self -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" value="self" v-model="form.request_for"
                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"/>
                        <span class="text-gray-900 dark:text-gray-100">
              Self <span class="text-sm font-medium text-blue-600">({{ authUser?.name || 'You' }})</span>
            </span>
                    </label>

                    <!-- Other -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" value="other" v-model="form.request_for"
                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"/>
                        <span class="text-gray-900 dark:text-gray-100">Other Employee</span>
                    </label>

                    <p v-if="errors.request_for" class="mt-2 text-sm text-red-600 w-full">{{ errors.request_for }}</p>
                </div>

                <!-- Employee Selection if Other -->
                <div v-if="form.request_for === 'other' && form.employee_scope === 'individual'" class="input-wrapper">
                    <Autocomplete
                        v-model="form.person_id"
                        @update:name="form.person_id = $event"
                        :initial-name="form.person_name"
                        label="Person"
                        endpoint="/api/persons/search"
                        :is_photo="false"
                        :error="allErrors.person_id"
                        required
                    />
                </div>

                <!-- Leave Reason -->
                <div class="input-wrapper">
                    <SelectInput v-model="form.leave_reason" label="Leave Reason" :options="leaveReasonOptions" :error="allErrors.leave_reason"/>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FlatpickrInput v-model="form.from_date" label="From Date" mode="date" :error="allErrors.from_date"/>
                    <FlatpickrInput v-model="form.to_date" label="To Date" mode="date" :error="allErrors.to_date"/>
                </div>

                <!-- Leave Duration -->
                <div class="input-wrapper">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Leave Duration <span class="text-red-500">*</span>
                    </label>
                    <select v-model="form.leave_duration"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Select Duration</option>
                        <option value="Full Day" selected>Full Day</option>
                        <option value="First Half">First Half</option>
                        <option value="Second Half">Second Half</option>
                    </select>
                    <p v-if="allErrors.leave_duration" class="mt-1 text-sm text-red-600">{{ allErrors.leave_duration }}</p>
                </div>

                <!-- Button: Add to List -->
                <div class="flex justify-end">
                    <button type="button" @click="addLeaveToList"
                            class="cursor-pointer px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 flex items-center gap-2">
                        Add to List
                    </button>
                </div>

                <!-- List of Added Leaves -->
                <div v-if="leaves.length" class="mt-6 space-y-2">
                    <h2 class="text-lg font-bold">Added Leaves</h2>
                    <div v-for="(leave, index) in leaves" :key="index"
                         class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
                        <div>
                            <p><strong>{{ leave.request_for === 'self' ? 'Self' : leave.person_name || 'Other' }}</strong></p>
                            <p>{{ leave.from_date }} → {{ leave.to_date }} ({{ leave.leave_duration }})</p>
                            <p>Reason: {{ leave.leave_reason }}</p>
                            <p>Total Days: {{ leave.total_days }}</p>
                        </div>
                        <button @click="removeLeave(index)" class="text-red-600 hover:underline">Remove</button>
                    </div>

                    <!-- Total Days Summary -->
                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="font-bold">Total Leave Days: {{ totalLeaveDays }}</p>
                    </div>
                </div>

                <!-- Other fields -->
                <div class="input-wrapper">
                    <TextInput v-model="form.emergency_contact_name" label="Emergency Contact Name" maxlength="100" :error="allErrors.emergency_contact_name"/>
                </div>
                <div class="input-wrapper">
                    <Autocomplete v-model="form.reliever_employee" @update:name="form.reliever_employee = $event"
                                  :initial-name="form.reliever_employee_name" label="Reliever" endpoint="/api/persons/search" :is_photo="false"
                                  :error="allErrors.reliever_employee" required/>
                </div>
                <div class="input-wrapper">
                    <TextareaInput v-model="form.remarks" label="Remarks" rows="3" placeholder="Optional remarks"/>
                </div>

                <!-- Final Submit -->
                <div class="flex justify-end gap-3">
                    <button type="button" @click="$inertia.visit('/leave-request')" class="cursor-pointer px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="cursor-pointer px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 flex items-center gap-2">
                        <LoadingSpinner v-if="form.processing"/>
                        {{ mode === 'create' ? 'Submit' : 'Update' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextareaInput from '@/Components/TextareaInput.vue'
import TextInput from '@/Components/TextInput.vue'
import FlatpickrInput from '@/Components/FlatpickrInput.vue'
import Autocomplete from '@/Components/Autocomplete.vue'
import LoadingSpinner from '@/Components/custom/LoadingSpinner.vue'

const props = defineProps({
    mode: { type: String, required: true },
    employees: { type: Array, default: () => [] },
    errors: Object
})
const { authUser } = usePage().props

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

const form = useForm({
    employee_scope: 'individual',
    request_for: 'self',
    employee_id: authUser.id,
    leave_duration: '',
    from_date: '',
    to_date: '',
    leave_reason: '',
    other_reason: '',
    emergency_contact_name: '',
    office_in_time: '',
    reporting_manager_id: null,
    reporting_manager_name: '',
    reliever_id: null,
    remarks: ''
})

const allErrors = reactive({ ...props.errors })

// Leaves array
const leaves = reactive([])

// Add current leave to list
function addLeaveToList() {
    if (!form.from_date || !form.to_date || !form.leave_duration || !form.leave_reason) {
        return alert('Fill all required fields!')
    }

    const newFrom = new Date(form.from_date)
    const newTo = new Date(form.to_date)

    // Check for overlapping dates
    const isOverlap = leaves.some(l => {
        const existingFrom = new Date(l.from_date)
        const existingTo = new Date(l.to_date)
        return (newFrom <= existingTo && newTo >= existingFrom)
    })

    if (isOverlap) {
        return alert('Leave for the selected date range already exists or overlaps with another entry!')
    }

    const total_days = Math.ceil((newTo.getTime() - newFrom.getTime()) / (1000*60*60*24)) + 1

    leaves.push({
        request_for: form.request_for,
        person_name: form.request_for === 'self' ? authUser.name : form.person_name,
        from_date: form.from_date,
        to_date: form.to_date,
        leave_duration: form.leave_duration,
        leave_reason: form.leave_reason,
        total_days
    })

    // Reset fields
    form.from_date = ''
    form.to_date = ''
    form.leave_duration = ''
    form.leave_reason = ''
}

// Remove leave from list
function removeLeave(index: number) {
    leaves.splice(index, 1)
}

// Total leave days summary
const totalLeaveDays = computed(() => leaves.reduce((sum, l) => sum + l.total_days, 0))

function submit() {
    if (!leaves.length) return alert('Add at least one leave!')
    // Submit `leaves` array via API/Inertia
    console.log('Submitting:', leaves)
    form.post('/leave-request', {
        data: { leaves },
        onSuccess: () => {
            alert('Leave requests submitted successfully!');
            leaves.splice(0, leaves.length); // clear local leaves
        },
        onError: (errors) => {
            Object.assign(allErrors, errors);
        }
    });
}
</script>
