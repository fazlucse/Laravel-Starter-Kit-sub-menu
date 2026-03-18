<script setup lang="ts">
import {computed, ref} from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AttendanceReportModal from './AttendanceReportModal.vue'
import PersonAutocomplete from '@/components/PersonAutocomplete.vue' // Assuming the path
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import ProcessingOverlay from '@/components/custom/ProcessingOverlay.vue'
import { FileSearch } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps<{
    offices: string[],
    departments: string[],
    divisions: string[]
}>()

const showReportModal = ref(false)
const reportData = ref([])
const isGenerating = ref(false)

// Define the form with updated keys for PersonAutocomplete
const form = useForm({
    office: '',
    department: '',
    division: '',
    employee_id: null,
    person_name: '',
    date_from: new Date().toISOString().slice(0, 10),
    date_to: new Date().toISOString().slice(0, 10),
})
const hasErrors = computed(() => Object.keys(form.errors).length > 0)
const submitReport = async () => {
    isGenerating.value = true
    form.clearErrors() // Reset previous errors

    try {
        const response = await axios.post('/reports/attendance/generate', form.data())
        reportData.value = response.data.reportData
        showReportModal.value = true
    } catch (error: any) {
        console.error("Report Generation Failed:", error)

        if (error.response && error.response.status === 422) {
            // Laravel Validation Errors
            form.setError(error.response.data.errors)
        } else if (error.response && error.response.status === 500) {
            // Laravel System/Code Error
            form.setError({
                server: error.response.data.message || "Internal Server Error"
            })
        } else {
            // Connection/Network issues
            form.setError({
                server: "Could not connect to the server. Please check your network."
            })
        }
    } finally {
        isGenerating.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/' },{ title: 'Reports',  href: '#' }, { title: 'Attendance' }]">
        <div class="max-w-[98%] mx-auto py-6">
            <ProcessingOverlay :active="isGenerating" message="Generating Report Data..." />
            <div v-if="hasErrors" class="mx-6 mb-0 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3">
                <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                <div>
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-tight">
                        {{ form.errors.server ? 'System Error' : 'Validation Errors Detected' }}
                    </h3>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 font-medium">
                        <li v-for="(error, key) in form.errors" :key="key">
                            {{ key === 'server' ? '' : key + ': ' }}{{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 border-gray-300 dark:border-gray-700  mb-6">
                <form @submit.prevent="submitReport" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Office / Location</label>
                            <select v-model="form.office" class="border p-2 rounded w-full dark:bg-gray-700 dark:text-white dark:border-gray-60">
                                <option value="">All Offices</option>
                                <option v-for="off in offices" :key="off.id" :value="off.id">{{ off.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Division</label>
                            <select v-model="form.division" class="border p-2 rounded w-full dark:bg-gray-700 dark:text-white dark:border-gray-60">
                                <option value="">All Divisions</option>
                                <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Department</label>
                            <select v-model="form.department" class="border p-2 rounded w-full dark:bg-gray-700 dark:text-white dark:border-gray-60">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="md:col-span-1">
                            <PersonAutocomplete
                                v-model="form.employee_id"
                                :initial-name="form.person_name"
                                @update:name="form.person_name = $event"
                                label="Employee / Person"
                                endpoint="/api/persons/search"
                                :error="form.errors.employee_id"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">From Date</label>
                            <FlatpickrInput
                                v-model="form.date_from"
                                placeholder="Select start date"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">To Date</label>
                            <FlatpickrInput
                                v-model="form.date_to"
                                placeholder="Select end date"
                            />
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="isGenerating"
                                class="w-full h-11 bg-blue-600 hover:bg-blue-700 text-white rounded font-black uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 transition active:scale-95 shadow-lg shadow-blue-500/30 cursor-pointer disabled:opacity-50"
                            >
                                <FileSearch v-if="!isGenerating" class="w-4 h-4" />
                                <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                {{ isGenerating ? 'Processing...' : 'Generate Report' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <AttendanceReportModal
                :show="showReportModal"
                :report-data="reportData"
                :filters="form"
                @close="showReportModal = false"
            />

            <div v-if="!showReportModal && reportData.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400">
                <div class="p-6 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                    <FileSearch class="w-12 h-12 opacity-20" />
                </div>
                <p class="font-black uppercase tracking-widest text-xs">Select filters and generate to view attendance records</p>
            </div>
        </div>
    </AppLayout>
</template>
