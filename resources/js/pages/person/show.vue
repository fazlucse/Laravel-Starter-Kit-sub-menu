<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import {useExport} from '@/composables/useExport'
import BaseActionButton from '@/components/BaseActionButton.vue'
import {ref} from 'vue';
import {useForm, Link} from '@inertiajs/vue3';
import { Printer, FileDown } from 'lucide-vue-next';

const props = defineProps<{
    person: any,
    editable?: boolean
}>();
const { isProcessing, print, exportToPDF, exportToExcel,exportPdfv2 } = useExport({
    contentId: 'print_content',
    filename: `${props.person.name.replace(/\s+/g, '_')}_Profile`,
    title: `${props.person.name} - Person Profile`,
})
const editable = props.editable ?? false;

const form = useForm({
    name: props.person.name || '',
    designation: props.person.designation || '',
    phone: props.person.phone || '',
    email: props.person.email || '',
    country: props.person.country || '',
    city: props.person.city || '',
    address: props.person.address || '',
    present_address: props.person.present_address || '',
    education: props.person.education || '',
    section: props.person.section || '',
    material_status: props.person.material_status || '',
    father_name: props.person.father_name || '',
    mother_name: props.person.mother_name || '',
    company: props.person.company || '',
    nationality: props.person.nationality || '',
    national_id: props.person.national_id || '',
    tin: props.person.tin || '',
    dob: props.person.dob || '',
    avatar: props.person.avatar || null,
    photo: props.person.photo || null,
});
const isPrinting = ref(false)
const isPdfing = ref(false)
const isExcelling = ref(false)

const handlePrint = async () => {
    isPrinting.value = true
    await print('print_content', '')
    isPrinting.value = false
}

const handlePDF = async () => {
    isPdfing.value = true
    await exportToPDF('print_content', '')
    isPdfing.value = false
}

const handleExcel = async () => {
    isExcelling.value = true
    await exportToExcel()
    isExcelling.value = false
}

// Breadcrumbs array
const breadcrumbs = [
    {title: 'Dashboard', href: '/'},
    {title: 'People', href: '/people.index'},
    {title: 'Profile'}
];
</script>

<template>
    <AppLayout
        title="People Profile"
        :breadcrumbs="breadcrumbs"
    >
        <!-- Back Button -->
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-2 flex flex-wrap gap-4 items-center">
            <div class="flex items-center gap-4">
                <!-- Back Button with Icon -->
                <Link
                    href="/people.index"
                    class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-md hover:bg-gray-400 dark:hover:bg-gray-700 transition"
                >
                    &larr; Back
                </Link>

                <!-- Title -->
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white">
                    Person Details
                </h4>
            </div>
            <div class="flex items-center gap-1 ml-auto">
                <BaseActionButton
                    label="Print"
                    :icon="Printer"
                    iconPosition="left"
                    :isProcessing="isPrinting"
                    @click="handlePrint"
                />

                <BaseActionButton
                    label="PDF"
                    :icon="FileDown"
                    iconPosition="left"
                    :isProcessing="isPdfing"
                    @click="handlePDF"
                />
            </div>
        </div>
        <!-- Profile Card -->
        <div
            class="w-full mx-auto bg-white dark:bg-gray-800 shadow-lg p-4 border border-gray-200 dark:border-gray-700">
            <div id="print_content" class="p-6 space-y-8">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Photo Section -->
                    <div class="flex-shrink-0 flex flex-col items-center text-center">
                        <div>
                            <img
                                v-if="form.photo"
                                :src="`/${form.photo}`"
                                class="w-32 h-32 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-lg"
                                alt="Avatar"
                            />
                            <div v-else
                                 class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 border-4 border-white dark:border-gray-700">
                                No Photo
                            </div>
                        </div>
                        <!-- Name below photo -->
                        <div class="mt-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ form.name }}</h2>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="flex-1">
                        <!-- Two-column grid for desktop, one column for mobile -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <!-- Column 1 -->
                            <div class="space-y-4">
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Name:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.name }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Designation:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.designation || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Email:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.email || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2  dark:text-gray-300 min-w-[150px]">Phone:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.phone || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Date of Birth:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.dob || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Marital Status:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.material_status || '-' }}</span>
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div class="space-y-4">
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Nationality:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.nationality || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Company:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.company || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Section/Department:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.section || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 min-w-[150px]">Education:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.education || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">National ID:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.national_id || '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">TIN:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.tin || '-' }}</span>
                                </div>
                            </div>

                            <!-- Full width rows -->
                            <div class="md:col-span-2 space-y-4">
                                <div class="flex flex-col md:flex-row">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px] md:w-[150px] md:flex-shrink-0">Permanent Address:</span>
                                    <span class="text-gray-900 dark:text-white md:ml-2 mt-1 md:mt-0">{{ form.address || '-' }}</span>
                                </div>
                                <div class="flex flex-col md:flex-row">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px] md:w-[150px] md:flex-shrink-0">Present Address:</span>
                                    <span class="text-gray-900 dark:text-white md:ml-2 mt-1 md:mt-0">{{ form.present_address || '-' }}</span>
                                </div>
                                <div v-if="form.father_name" class="flex">
                                    <span class="font-semibold text-gray-700  gap-2 dark:text-gray-300 min-w-[150px]">Father's Name:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.father_name }}</span>
                                </div>
                                <div v-if="form.mother_name" class="flex">
                                    <span class="font-semibold text-gray-700 gap-2 dark:text-gray-300 min-w-[150px]">Mother's Name:</span>
                                    <span class="text-gray-900 dark:text-white ml-2">{{ form.mother_name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Button -->
            <div v-if="editable" class="mt-6 text-right">
                <Link
                    :href="`/people/${props.person.id}/edit`"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md"
                >
                    Edit Profile
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
