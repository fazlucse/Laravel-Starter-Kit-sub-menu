<template>
    <AppLayout
        title="Attendance Management"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Attendance', href: '/attendance' },
            { title: 'Create Attendance', href: '#' },
        ]"
    >
        <div class="bg-white dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">

                <FormSection title="Attendance Type">
                    <div class="flex gap-6 mt-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="radio"
                                class="form-radio text-blue-600"
                                value="individual"
                                v-model="type"
                            />
                            <span class="ml-2 dark:text-gray-200">Individual Entry</span>
                        </label>

                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="radio"
                                class="form-radio text-green-600"
                                value="excel"
                                v-model="type"
                            />
                            <span class="ml-2 dark:text-gray-200">Upload Excel</span>
                        </label>
                    </div>

                </FormSection>

                <FormSection v-if="type === 'individual'" title="Individual Attendance Entry">
                    <form @submit.prevent="submitIndividual" class="space-y-4">
                        <div>
                            <SearchableSelect
                                v-model="form.employee_id"
                                label="Select Employee"
                                :items="employees"
                                placeholder="Search Name or ID..."
                                labelKey="person_name"
                                valueKey="person_id"
                                subLabelKey="person_id"
                            />
                            <p v-if="form.errors.employee_id" class="text-red-500 text-xs mt-1">{{ form.errors.employee_id }}</p>
                        </div>

                        <div>
                            <FlatpickrInput v-model="form.date" label="Date" />
                            <p v-if="form.errors.date" class="text-red-500 text-xs mt-1">{{ form.errors.date }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <TextInput type="time" v-model="form.in_time" label="In Time" />
                                <p v-if="form.errors.in_time" class="text-red-500 text-xs mt-1">{{ form.errors.in_time }}</p>
                            </div>
                            <div>
                                <TextInput type="time" v-model="form.out_time" label="Out Time" />
                                <p v-if="form.errors.out_time" class="text-red-500 text-xs mt-1">{{ form.errors.out_time }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Attendance' }}
                            </button>
                        </div>

                    </form>
                </FormSection>

                <FormSection v-if="type === 'excel'" title="Upload Attendance Excel">
                    <form @submit.prevent="submitExcel">
                        <div
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop"
                            :class="[
                                'relative border-2 border-dashed rounded-xl p-12 transition-all duration-200 text-center',
                                isDragging
                                    ? 'bg-blue-600 border-blue-400 scale-[1.01]'
                                    : 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700'
                            ]"
                        >
                            <input
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                @change="handleFile"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            />

                            <div :class="['transition-colors duration-200', isDragging ? 'text-white' : 'text-gray-600 dark:text-gray-400']">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>

                                <p class="text-lg font-medium">
                                    {{ isDragging ? 'Drop file to upload' : 'Click or drag Excel/CSV file here' }}
                                </p>

                                <p v-if="excelForm.file" class="mt-2 text-sm font-bold bg-white/20 inline-block px-3 py-1 rounded">
                                    Selected: {{ excelForm.file.name }}
                                </p>
                                <p v-if="excelForm.errors.file" class="text-red-500 mt-2">{{ excelForm.errors.file }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button
                                type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition-all disabled:opacity-50"
                                :disabled="excelForm.processing || !excelForm.file"
                            >
                                {{ excelForm.processing ? 'Processing File...' : 'Upload Attendance' }}
                            </button>
                        </div>
                    </form>
                </FormSection>

            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref,computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import FormSection from '@/components/FormSection.vue'
import TextInput from '@/components/TextInput.vue'
import SearchableSelect from '@/components/SearchableSelect.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import PersonAutocomplete from '@/components/PersonAutocomplete.vue'

const props = defineProps<{ employees: Array<any> }>()
const isDropdownOpen = ref(false)
const searchQuery = ref('')
const selectedEmployeeName = ref('')
// State
const type = ref<'individual' | 'excel'>('individual')
const isDragging = ref(false)

// Individual Form
const form = useForm({
    employee_id: '',
    date: '',
    in_time: '',
    out_time: ''
})

// Excel Form
const excelForm = useForm({
    file: null as File | null
})

// Handlers
const handleFile = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files?.length) {
        excelForm.file = target.files[0]
    }
}

const handleDrop = (e: DragEvent) => {
    isDragging.value = false
    if (e.dataTransfer?.files?.length) {
        excelForm.file = e.dataTransfer.files[0]
    }
}

const submitIndividual = () => form.post('/attendance')
const submitExcel = () => excelForm.post('/attendance?type=excel')
// const submitExcel = () => {
//     excelForm.post('/attendance?type=excel', {
//         forceFormData: true, // This is key for file uploads
//         onCancelToken: (token) => {},
//         onStart: () => { console.log('Starting upload...') },
//         onError: (errors) => { console.error('Upload Failed', errors) },
//         onFinish: () => { isDragging.value = false },
//     })
// }
const filteredEmployees = computed(() => {
    const q = searchQuery.value.toLowerCase()
    return props.employees.filter(emp =>
        emp.person_name.toLowerCase().includes(q) ||
        emp.person_id.toString().toLowerCase().includes(q)
    )
})

// Handle selecting an item
const selectEmployee = (emp) => {
    form.employee_id = emp.id // Set the ID for Laravel
    selectedEmployeeName.value = `${emp.person_id} | ${emp.person_name}` // Display text
    isDropdownOpen.value = false // Close the dropdown
    searchQuery.value = '' // Clear search for next time
}
</script>
