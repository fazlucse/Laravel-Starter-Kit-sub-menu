<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AttendanceReportModal from './AttendanceReportModal.vue'
import { FileSearch, CalendarDays, Users, Building2 } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps<{
    offices: string[],
    departments: string[],
    divisions: string[]
}>()

const showReportModal = ref(false)
const reportData = ref([])
const isGenerating = ref(false)

// Define the form with all necessary keys
const form = useForm({
    office: '',
    department: '',
    division: '',
    employee_id: '',
    date_from: new Date().toISOString().slice(0, 10),
    date_to: new Date().toISOString().slice(0, 10),
})

const submitReport = async () => {
    isGenerating.value = true
    try {
        // We use axios for the data fetch to keep the main page state clean
        const response = await axios.post('/reports/attendance/generate', form.data())
        reportData.value = response.data.reportData
        showReportModal.value = true
    } catch (error) {
        console.error("Report Generation Failed:", error)
        alert("Failed to generate report. Please check your connection.")
    } finally {
        isGenerating.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/' },{ title: 'Reports',  href: '#' }, { title: 'Attendance' }]">
        <div class="max-w-[98%] mx-auto py-6">

            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border-2 border-gray-300 dark:border-gray-700 shadow-lg mb-6">
                <form @submit.prevent="submitReport" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Office / Location</label>
                            <select v-model="form.office" class="w-full h-11 rounded-xl border-2 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all cursor-pointer">
                                <option value="">All Offices</option>
                                <option v-for="off in offices" :key="off" :value="off">{{ off }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Division</label>
                            <select v-model="form.division" class="w-full h-11 rounded-xl border-2 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all cursor-pointer">
                                <option value="">All Divisions</option>
                                <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Department</label>
                            <select v-model="form.department" class="w-full h-11 rounded-xl border-2 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-all cursor-pointer">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Employee ID / Name</label>
                            <input
                                type="text"
                                v-model="form.employee_id"
                                placeholder="Search Employee..."
                                class="w-full h-11 rounded-xl border-2 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none px-4 transition-all"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">From Date</label>
                            <input
                                type="date"
                                v-model="form.date_from"
                                class="w-full h-11 rounded-xl border-2 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none px-4 transition-all"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">To Date</label>
                            <input
                                type="date"
                                v-model="form.date_to"
                                class="w-full h-11 rounded-xl border-2 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none px-4 transition-all"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                type="submit"
                                :disabled="isGenerating"
                                class="w-full h-11 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 transition active:scale-95 shadow-lg shadow-blue-500/30 cursor-pointer disabled:opacity-50"
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

<style scoped>
/* Scoped styles to ensure the date picker and selects look industrial */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(0.5);
}
</style>
