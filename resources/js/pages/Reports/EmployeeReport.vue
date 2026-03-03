<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import EmployeeReportModal from './EmployeeReportModal.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import { FileSearch, Users, Briefcase, MapPin, UserCheck, CalendarClock, UserRound, Building2, AlertCircle } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps<{
    offices: string[],
    departments: string[],
    divisions: string[],
    designations: string[],
    employmentStatuses: string[]
}>()

const showReportModal = ref(false)
const reportData = ref([])
const isGenerating = ref(false)

// form.errors is automatically populated if you use form.post,
// but since you are using axios, we manually handle the error state.
const form = useForm({
    office: '',
    division: '',
    department: '',
    designation: '',
    status: '',
    gender: '',
    search: '',
    joined_from: '',
    joined_to: '',
    confirmed_from: '',
    confirmed_to: '',
    effective_from: '',
    effective_to: '',
})

const submitReport = async () => {
    isGenerating.value = true
    form.clearErrors()

    try {
        const response = await axios.post('/reports/employees/generate', form.data())
        reportData.value = response.data.reportData
        showReportModal.value = true
    } catch (error: any) {
        if (error.response && (error.response.status === 422 || error.response.status === 500)) {
            // This will now catch both validation and the "undefined relationship" error
            const backendErrors = error.response.data.errors || { server: [error.response.data.message] };
            form.setError(backendErrors);
        } else {
            form.setError({ server: ["A connection error occurred. Check the console."] });
            console.error("Critical Error:", error);
        }
    } finally {
        isGenerating.value = false
    }
}

// Check if there are any errors at all
const hasErrors = computed(() => Object.keys(form.errors).length > 0)

const inputClasses = "w-full h-11 rounded-xl border-2 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:ring-1 outline-none px-4 transition-all"
const selectClasses = "w-full h-11 rounded-xl border-2 bg-gray-50 dark:bg-gray-900 dark:text-white text-sm focus:ring-1 outline-none px-4 transition-all cursor-pointer"
const labelClasses = "block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 flex items-center gap-2"
const errorMsgClasses = "text-[10px] font-bold text-red-500 mt-1 animate-pulse"

// Helper for dynamic border color based on error
const getBorderClass = (field: string) => form.errors[field] ? 'border-red-500 focus:border-red-600 focus:ring-red-600' : 'border-gray-300 dark:border-gray-700 focus:border-blue-600 focus:ring-blue-600'
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Reports', href: '#' }, { title: 'Employee Directory' }]">
        <div class="max-w-[98%] mx-auto py-6">

            <div v-if="hasErrors" class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3 animate-bounce">
                <AlertCircle class="w-5 h-5 text-red-600 mt-0.5" />
                <div>
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-tight">Validation Errors Detected</h3>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 font-medium">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border-2 border-gray-200 dark:border-gray-700 shadow-xl mb-6">
                <form @submit.prevent="submitReport" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label :class="labelClasses"><MapPin class="w-3 h-3 text-blue-500"/> Office</label>
                            <select v-model="form.office" :class="[selectClasses, getBorderClass('office')]">
                                <option value="">All Offices</option>
                                <option v-for="o in offices" :key="o">{{ o }}</option>
                            </select>
                            <p v-if="form.errors.office" :class="errorMsgClasses">{{ form.errors.office }}</p>
                        </div>

                        <div>
                            <label :class="labelClasses"><Building2 class="w-3 h-3 text-blue-500"/> Division</label>
                            <select v-model="form.division" :class="[selectClasses, getBorderClass('division')]">
                                <option value="">All Divisions</option>
                                <option v-for="d in divisions" :key="d">{{ d }}</option>
                            </select>
                            <p v-if="form.errors.division" :class="errorMsgClasses">{{ form.errors.division }}</p>
                        </div>

                        <div>
                            <label :class="labelClasses"><Users class="w-3 h-3 text-blue-500"/> Department</label>
                            <select v-model="form.department" :class="[selectClasses, getBorderClass('department')]">
                                <option value="">All Depts</option>
                                <option v-for="d in departments" :key="d">{{ d }}</option>
                            </select>
                            <p v-if="form.errors.department" :class="errorMsgClasses">{{ form.errors.department }}</p>
                        </div>

                        <div>
                            <label :class="labelClasses"><Briefcase class="w-3 h-3 text-blue-500"/> Designation</label>
                            <select v-model="form.designation" :class="[selectClasses, getBorderClass('designation')]">
                                <option value="">All Designations</option>
                                <option v-for="d in designations" :key="d">{{ d }}</option>
                            </select>
                            <p v-if="form.errors.designation" :class="errorMsgClasses">{{ form.errors.designation }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label :class="labelClasses"><UserCheck class="w-3 h-3 text-blue-500"/> Employment Status</label>
                            <select v-model="form.status" :class="[selectClasses, getBorderClass('status')]">
                                <option value="">All Statuses</option>
                                <option v-for="s in employmentStatuses" :key="s">{{ s }}</option>
                            </select>
                            <p v-if="form.errors.status" :class="errorMsgClasses">{{ form.errors.status }}</p>
                        </div>

                        <div>
                            <label :class="labelClasses"><UserRound class="w-3 h-3 text-blue-500"/> Gender</label>
                            <select v-model="form.gender" :class="[selectClasses, getBorderClass('gender')]">
                                <option value="">All Genders</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <p v-if="form.errors.gender" :class="errorMsgClasses">{{ form.errors.gender }}</p>
                        </div>

                        <div class="lg:col-span-2">
                            <label :class="labelClasses">Search Employee</label>
                            <input type="text" v-model="form.search" placeholder="ID or Name..." :class="[inputClasses, getBorderClass('search')]" />
                            <p v-if="form.errors.search" :class="errorMsgClasses">{{ form.errors.search }}</p>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-700" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label :class="labelClasses"><CalendarClock class="w-3 h-3 text-green-600"/> Joined Date Range</label>
                            <div class="grid grid-cols-2 gap-4">
                                <FlatpickrInput v-model="form.joined_from" placeholder="From" :has-error="!!form.errors.joined_from" />
                                <FlatpickrInput v-model="form.joined_to" placeholder="To" :has-error="!!form.errors.joined_to" />
                            </div>
                            <p v-if="form.errors.joined_from || form.errors.joined_to" :class="errorMsgClasses">
                                {{ form.errors.joined_from || form.errors.joined_to }}
                            </p>
                        </div>
                        <div>
                            <label :class="labelClasses"><CalendarClock class="w-3 h-3 text-blue-600"/> Confirmed Date Range</label>
                            <div class="grid grid-cols-2 gap-4">
                                <FlatpickrInput v-model="form.confirmed_from" placeholder="From" :has-error="!!form.errors.confirmed_from" />
                                <FlatpickrInput v-model="form.confirmed_to" placeholder="To" :has-error="!!form.errors.confirmed_to" />
                            </div>
                            <p v-if="form.errors.confirmed_from || form.errors.confirmed_to" :class="errorMsgClasses">
                                {{ form.errors.confirmed_from || form.errors.confirmed_to }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label :class="labelClasses"><CalendarClock class="w-3 h-3 text-red-600"/> Effective Date Range (Inactive)</label>
                            <div class="grid grid-cols-2 gap-4">
                                <FlatpickrInput v-model="form.effective_from" placeholder="From" :has-error="!!form.errors.effective_from" />
                                <FlatpickrInput v-model="form.effective_to" placeholder="To" :has-error="!!form.errors.effective_to" />
                            </div>
                            <p v-if="form.errors.effective_from || form.errors.effective_to" :class="errorMsgClasses">
                                {{ form.errors.effective_from || form.errors.effective_to }}
                            </p>
                        </div>
                        <div class="flex items-end">
                            <button
                                type="submit"
                                :disabled="isGenerating"
                                class="w-full h-11 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center justify-center gap-3 transition active:scale-95 shadow-lg shadow-blue-500/20 cursor-pointer disabled:opacity-50"
                            >
                                <FileSearch v-if="!isGenerating" class="w-5 h-5" />
                                <span v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                {{ isGenerating ? 'Processing...' : 'Generate Employee Report' }}
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <EmployeeReportModal :show="showReportModal" :report-data="reportData" :filters="form" @close="showReportModal = false" />
        </div>
    </AppLayout>
</template>
