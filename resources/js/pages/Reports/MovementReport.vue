<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import MovementReportModal from './MovementReportModal.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import {
    FileSearch, Navigation, Users, MapPin,
    CalendarClock, Building2, AlertCircle, Loader2
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps<{
    divisions: string[],
    statuses: { id: number, name: string }[]
}>()

const showReportModal = ref(false)
const reportData = ref([])
const isGenerating = ref(false)

const form = useForm({
    division: '',
    employee_name: '',
    status: '',
    date_from: '',
    date_to: '',
})

const submitReport = async () => {
    isGenerating.value = true
    form.clearErrors()

    try {
        const response = await axios.post('/reports/movements/generate', form.data())
        reportData.value = response.data.reportData
        showReportModal.value = true
    } catch (error: any) {
        if (error.response && error.response.data.errors) {
            form.setError(error.response.data.errors)
        } else {
            form.setError({ server: "Failed to connect to the server." })
        }
    } finally {
        isGenerating.value = false
    }
}

// UI Styling Constants
const labelClasses = "block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 flex items-center gap-2"
const inputClasses = "w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 text-sm outline-none focus:border-blue-600 transition-all"
const selectClasses = "w-full h-11 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 text-sm outline-none focus:border-blue-600 cursor-pointer transition-all"
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Reports', href: '#' }, { title: 'Movement Register' }]">
        <div class="max-w-[95%] mx-auto py-8">

            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3 animate-pulse">
                <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                <div>
                    <h3 class="text-sm font-black text-red-800 uppercase">Filter Errors</h3>
                    <p class="text-xs text-red-700 font-bold">Please check the highlighted fields below.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border-2 border-gray-200 dark:border-gray-700 shadow-2xl">
                <form @submit.prevent="submitReport" class="space-y-8 no-print">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label :class="labelClasses"><Building2 class="w-3 h-3 text-blue-500"/> Division</label>
                            <select v-model="form.division" :class="selectClasses">
                                <option value="">All Divisions</option>
                                <option v-for="d in divisions" :key="d" :value="d">{{ d }}</option>
                            </select>
                        </div>

                        <div>
                            <label :class="labelClasses"><Users class="w-3 h-3 text-blue-500"/> Employee Name</label>
                            <input type="text" v-model="form.employee_name" placeholder="Search by name..." :class="inputClasses" />
                        </div>

                        <div>
                            <label :class="labelClasses"><AlertCircle class="w-3 h-3 text-blue-500"/> Approval Status</label>
                            <select v-model="form.status" :class="selectClasses">
                                <option value="">Select Status</option>
                                <option value="0">Pending</option>
                                <option value="1">Approved</option>
                                <option value="2">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-700" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label :class="labelClasses"><CalendarClock class="w-3 h-3 text-green-600"/> Movement Period</label>
                            <div class="grid grid-cols-2 gap-4">
                                <FlatpickrInput v-model="form.date_from" placeholder="From Date" :has-error="!!form.errors.date_from" />
                                <FlatpickrInput v-model="form.date_to" placeholder="To Date" :has-error="!!form.errors.date_to" />
                            </div>
                        </div>

                        <div class="flex items-end">
                            <button
                                type="submit"
                                :disabled="isGenerating"
                                class="w-full h-11 bg-slate-900 hover:bg-black text-white rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center justify-center gap-3 transition active:scale-95 shadow-xl disabled:opacity-50 cursor-pointer"
                            >
                                <Loader2 v-if="isGenerating" class="w-5 h-5 animate-spin" />
                                <FileSearch v-else class="w-5 h-5" />
                                {{ isGenerating ? 'Generating...' : 'Generate Movement Report' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <MovementReportModal
                :show="showReportModal"
                :report-data="reportData"
                :filters="form"
                @close="showReportModal = false"
            />
        </div>
    </AppLayout>
</template>
