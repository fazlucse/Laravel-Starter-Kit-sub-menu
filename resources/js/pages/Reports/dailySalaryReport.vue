<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DailySalaryReportModal from './DailySalaryReportModal.vue' // Import the modal
import PersonAutocomplete from '@/components/PersonAutocomplete.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import ProcessingOverlay from '@/components/custom/ProcessingOverlay.vue'
import { FileSearch, AlertCircle, LayoutDashboard, FileBarChart } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps<{
    offices: any[],
    departments: any[],
    divisions: any[]
}>()

const showReportModal = ref(false)
const reportData = ref([])
const isGenerating = ref(false)

// Define the form
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
    form.clearErrors()

    try {
        const response = await axios.post('/reports/daily-salary/generate', form.data())
        reportData.value = response.data.reportData
        showReportModal.value = true
    } catch (error: any) {
        console.error("Report Generation Failed:", error)

        if (error.response && error.response.status === 422) {
            // Laravel Validation Errors (422)
            form.setError(error.response.data.errors)
        } else if (error.response && (error.response.status === 500 || error.response.status === 404)) {
            // SQL or System Errors (500)
            form.setError({
                server: error.response.data.message || "A database or server error occurred."
            })
        } else {
            // Network/Unknown Errors
            form.setError({
                server: "Network connection failed. Check your internet or server status."
            })
        }
    } finally {
        isGenerating.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'Reports', href: '#' }, { title: 'Daily Salary & OT' }]">
        <div class="max-w-[98%] mx-auto py-6">
            <ProcessingOverlay :active="isGenerating" message="Calculating Daily Salary & OT..." />

            <div v-if="hasErrors" class="mx-6 mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl flex items-start gap-3">
                <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                <div class="flex-1">
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-tight">
                        {{ form.errors.server ? 'Critical SQL / System Error' : 'Input Validation Notice' }}
                    </h3>
                    <div v-if="form.errors.server" class="mt-2 p-2 bg-white rounded border border-red-100 text-[11px] font-mono text-red-600 break-words">
                        {{ form.errors.server }}
                    </div>
                    <ul v-else class="mt-1 list-disc list-inside text-xs text-red-700 font-medium">
                        <li v-for="(error, key) in form.errors" :key="key">
                            <span class="font-bold uppercase">{{ key }}:</span> {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-8 border border-gray-200 dark:border-gray-700 shadow-sm rounded-xl mb-6">
                <div class="flex items-center gap-3 mb-8 border-b pb-4">
                    <FileBarChart class="w-6 h-6 text-blue-600" />
                    <h1 class="text-lg font-black uppercase tracking-tighter">Daily Salary & OT Report Generator</h1>
                </div>

                <form @submit.prevent="submitReport" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Office / Unit</label>
                            <select v-model="form.office" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">Select All Offices</option>
                                <option v-for="off in offices" :key="off.id" :value="off.id">{{ off.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Division</label>
                            <select v-model="form.division" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">All Divisions</option>
                                <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Department</label>
                            <select v-model="form.department" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end border-t pt-8">
                        <div class="md:col-span-1">
                            <PersonAutocomplete
                                v-model="form.employee_id"
                                :initial-name="form.person_name"
                                @update:name="form.person_name = $event"
                                label="Specific Employee (Optional)"
                                endpoint="/api/persons/search"
                                :error="form.errors.employee_id"
                            />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">From Date</label>
                            <FlatpickrInput v-model="form.date_from" placeholder="Pick start date" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">To Date</label>
                            <FlatpickrInput v-model="form.date_to" placeholder="Pick end date" />
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="isGenerating"
                                class="w-full h-12 bg-gray-900 hover:bg-black text-white rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center justify-center gap-3 transition active:scale-95 shadow-xl disabled:opacity-50 cursor-pointer"
                            >
                                <FileSearch v-if="!isGenerating" class="w-5 h-5" />
                                <span v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                {{ isGenerating ? 'Processing...' : 'Run Analysis' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div v-if="!showReportModal && reportData.length === 0" class="flex flex-col items-center justify-center py-24 text-gray-400">
                <div class="p-8 bg-gray-100 dark:bg-gray-800 rounded-full mb-6 border-4 border-white dark:border-gray-700 shadow-inner">
                    <FileSearch class="w-16 h-16 opacity-10" />
                </div>
                <p class="font-black uppercase tracking-widest text-xs">Awaiting Filters to Generate Report</p>
            </div>

            <DailySalaryReportModal
                :show="showReportModal"
                :report-data="reportData"
                :filters="form"
                @close="showReportModal = false"
            />
        </div>
    </AppLayout>
</template>
