<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import LeaveReportModal from './LeaveReportModal.vue'
import PersonAutocomplete from '@/components/PersonAutocomplete.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import { AlertCircle, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    departments: Array,
    leaveTypes: Array,
    statuses: Array
})

const form = ref({
    department: '',
    leave_type: '',
    status: '',
    from_date: '',
    to_date: '',
    person_id: null,
    person_name: ''
})

const showModal = ref(false)
const processing = ref(false) // Tracking loading state
const reportData = ref([])
const allErrors = ref<Record<string, any>>({})
const serverError = ref('')

// Computed property to check if any errors exist
const hasErrors = computed(() => {
    return Object.keys(allErrors.value).length > 0 || serverError.value !== ''
})

const generateReport = async () => {
    // Reset states before starting
    allErrors.value = {}
    serverError.value = ''
    processing.value = true

    try {
        const res = await axios.post('/reports/leaves/generate', form.value)
        reportData.value = res.data.reportData
        showModal.value = true
    } catch (err: any) {
        if (err.response && err.response.status === 422) {
            allErrors.value = err.response.data.errors
        } else {
            serverError.value = err.response?.data?.message || err.message
            console.error(err)
        }
    } finally {
        // Stop processing whether it succeeded or failed
        processing.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[
     { title: 'Dashboard', href: '/' },
     { title: 'Reports', href: '#' },
     { title: 'Leave Report' }
]">
        <div class="bg-white dark:bg-gray-800 p-2 sm:p-8 border border-sidebar-border/70 relative">

            <div v-if="processing" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 z-10 flex items-center justify-center backdrop-blur-[1px]">
                <div class="flex flex-col items-center gap-2">
                    <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Generating Report...</span>
                </div>
            </div>

            <div v-if="hasErrors" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3">
                <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                <div>
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-tight">
                        {{ serverError ? 'System Error' : 'Validation Errors' }}
                    </h3>

                    <p v-if="serverError" class="text-xs text-red-700 mt-1">{{ serverError }}</p>

                    <ul v-if="Object.keys(allErrors).length" class="mt-1 list-disc list-inside text-xs text-red-700 font-medium">
                        <li v-for="(errorMessages, field) in allErrors" :key="field">
                            <span class="capitalize">{{ field.replace('_', ' ') }}</span>: {{ errorMessages[0] }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="max-w-5xl mx-auto p-1 sm:p-4">
                <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white">Leave Report</h2>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">

                    <div class="col-span-12">
                        <PersonAutocomplete
                            v-model="form.person_id"
                            :initial-name="form.person_name"
                            @update:name="form.person_name = $event"
                            label="Person"
                            endpoint="/api/persons/search"
                            :error="allErrors.person_id ? allErrors.person_id[0] : null"
                        />
                    </div>

                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-semibold mb-1 uppercase text-gray-500">Department</label>
                        <select v-model="form.department" class="border p-2 rounded w-full dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            <option value="">All Departments</option>
                            <option v-for="d in departments" :key="d" :value="d.name">{{ d.name }}</option>
                        </select>
                    </div>

                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-semibold mb-1 uppercase text-gray-500">Leave Type</label>
                        <select v-model="form.leave_type" class="border p-2 rounded w-full dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            <option value="">All Leave Types</option>
                            <option v-for="l in leaveTypes" :key="l" :value="l">{{ l }}</option>
                        </select>
                    </div>

                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-semibold mb-1 uppercase text-gray-500">Status</label>
                        <select v-model="form.status" class="border p-2 rounded w-full dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            <option value="">All Status</option>
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>

                    <div class="col-span-12 md:col-span-12 flex flex-col md:flex-row gap-2 items-end mt-2">
                        <div class="flex-1 w-full grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold mb-1 uppercase text-gray-500">From Date</label>
                                <FlatpickrInput v-model="form.from_date" placeholder="Select Date" class="w-full" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 uppercase text-gray-500">To Date</label>
                                <FlatpickrInput v-model="form.to_date" placeholder="Select Date" class="w-full" />
                            </div>
                        </div>

                        <button
                            @click="generateReport"
                            :disabled="processing"
                            class="w-full md:w-auto min-w-[140px] px-6 py-2 rounded h-10 text-sm font-bold flex items-center justify-center gap-2 transition-all"
                            :class="processing ? 'bg-blue-400 cursor-not-allowed text-white' : 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer'"
                        >
                            <Loader2 v-if="processing" class="w-4 h-4 animate-spin" />
                            <span>{{ processing ? 'Processing...' : 'Generate Report' }}</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <LeaveReportModal
            :show="showModal"
            :reportData="reportData"
            @close="showModal = false"
        />
    </AppLayout>
</template>
