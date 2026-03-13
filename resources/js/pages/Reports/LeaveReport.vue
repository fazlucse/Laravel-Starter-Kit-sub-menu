<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import LeaveReportModal from './LeaveReportModal.vue'
import PersonAutocomplete from '@/components/PersonAutocomplete.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'

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
const reportData = ref([])
const allErrors = ref({})
const serverError = ref('');
const generateReport = async () => {
    allErrors.value = {}
    serverError.value = ''  // reset
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
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[
     { title: 'Dashboard', href: '/' },
     { title: 'Reports', href: '#' },
     { title: 'Leave Report' }
]">
        <div class="bg-white dark:bg-gray-800 p-4 sm:p-8 border border-sidebar-border/70">
            <div v-if="hasErrors" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3 animate-bounce">
                <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                <div v-if="Object.keys(allErrors).length" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3 animate-bounce">
                    <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                    <div>
                        <h3 class="text-sm font-black text-red-800 uppercase tracking-tight">Validation Errors Detected</h3>
                        <ul class="mt-1 list-disc list-inside text-xs text-red-700 font-medium">
                            <li v-for="(errorMessages, field) in allErrors" :key="field">
                                {{ field }}: {{ errorMessages[0] }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="max-w-5xl mx-auto p-2 sm:p-4">
                <h2 class="text-xl font-bold mb-6">Leave Report</h2>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">

                    <!-- Person Autocomplete (Full Width) -->
                    <div class="col-span-12">
                        <PersonAutocomplete
                            v-model="form.person_id"
                            :initial-name="form.person_name"
                            @update:name="form.person_name = $event"
                            label="Person"
                            endpoint="/api/persons/search"
                            required
                            :error="allErrors.person_id"
                        />
                    </div>

                    <!-- Department -->
                    <div class="col-span-12 md:col-span-4">
                        <select v-model="form.department" class="border p-2 rounded w-full">
                            <option value="">All Departments</option>
                            <option v-for="d in departments" :key="d">{{ d }}</option>
                        </select>
                    </div>

                    <!-- Leave Type -->
                    <div class="col-span-12 md:col-span-4">
                        <select v-model="form.leave_type" class="border p-2 rounded w-full">
                            <option value="">All Leave Types</option>
                            <option v-for="l in leaveTypes" :key="l">{{ l }}</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-span-12 md:col-span-4">
                        <select v-model="form.status" class="border p-2 rounded w-full">
                            <option value="">All Status</option>
                            <option v-for="s in statuses" :key="s">{{ s }}</option>
                        </select>
                    </div>

                    <!-- Date Range + Button -->
                    <!-- Date Range + Button -->
                    <div class="col-span-12 md:col-span-9 flex gap-2 items-stretch">
                        <FlatpickrInput v-model="form.from_date" placeholder="From Date" class="flex-1" />
                        <FlatpickrInput v-model="form.to_date" placeholder="To Date" class="flex-1" />
                        <button
                            @click="generateReport"
                            class=" cursor-pointer bg-blue-600 text-white mt-2 px-4 py-1.5 rounded h-10 text-sm flex items-center justify-center"
                        >
                        Generate
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Report Modal -->
        <LeaveReportModal
            :show="showModal"
            :reportData="reportData"
            @close="showModal = false"
        />
    </AppLayout>
</template>
