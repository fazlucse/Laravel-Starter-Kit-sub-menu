<template>
    <AppLayout
        title="Create Holiday"
        :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'Holidays', href: '/holidays' }, { title: 'New Entry', href: '#' }]"
    >
        <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 transition-all">

                <div class="bg-white dark:bg-gray-800 rounded-t-xl shadow-lg p-6 mb-0 border-b border-gray-100 dark:border-gray-700">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 text-center">
                        {{ mode === 'create' ? 'Holiday Configuration' : 'Update Holiday' }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
                        Complete all sections. <span class="text-red-500 font-medium">Fields marked with * are required.</span>
                    </p>
                </div>

                <form @submit.prevent="submit" class="p-8">
                    <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded shadow-sm">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-red-700 dark:text-red-400 font-bold uppercase tracking-tight">
                                Please correct the following errors:
                            </p>
                        </div>

                        <ul class="mt-2 ml-7 list-disc list-inside space-y-1">
                            <li v-for="(error, key) in form.errors" :key="key" class="text-xs text-red-600 dark:text-red-400 font-medium">
                                <span class="capitalize font-bold" v-if="key !== 'error'">{{ key.replace('_', ' ') }}:</span>
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <div class="space-y-10">

                        <section>
                            <div class="flex items-center space-x-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">
                                <span class="text-indigo-600 dark:text-indigo-400 font-black text-xl">01.</span>
                                <h4 class="font-bold text-gray-800 dark:text-white uppercase tracking-widest text-xs">Basic Details</h4>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Holiday Type *</label>
                                    <select
                                        v-model="form.holiday_type"
                                        :class="{'border-red-500 focus:ring-red-500': form.errors.holiday_type}"
                                        class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 transition-all py-2.5 dark:text-white"
                                    >
                                        <option value="">Select Category</option>
                                        <option v-for="t in holidayTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </select>
                                    <p v-if="form.errors.holiday_type" class="text-red-500 text-[10px] mt-1 font-bold animate-pulse">{{ form.errors.holiday_type }}</p>
                                </div>

                                <div class="input-wrapper">
                                    <FlatpickrInput
                                        v-model="form.date_from"
                                        label="Date From *"
                                        placeholder="Select Start Date"
                                        :error="form.errors.date_from"
                                    />
                                </div>

                                <div class="input-wrapper">
                                    <FlatpickrInput
                                        v-model="form.date_to"
                                        label="Date To *"
                                        placeholder="Select End Date"
                                        :error="form.errors.date_to"
                                    />
                                </div>
                            </div>
                        </section>

                        <section>
                            <div class="flex items-center space-x-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">
                                <span class="text-indigo-600 dark:text-indigo-400 font-black text-xl">02.</span>
                                <h4 class="font-bold text-gray-800 dark:text-white uppercase tracking-widest text-xs">Target Scope</h4>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 mb-6 transition-colors">
                                <label class="relative flex items-center group cursor-pointer">
                                    <input type="checkbox" v-model="form.is_personwise" class="w-6 h-6 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <div class="ml-4">
                                        <p class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-tight">Person-wise Holiday Mode</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">Apply to specific employee IDs instead of general office/departments</p>
                                    </div>
                                </label>
                            </div>

                            <div v-if="form.is_personwise" class="animate-fade-in space-y-1">
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Employee IDs</label>
                                <textarea
                                    v-model="form.total_person_ids"
                                    rows="2"
                                    :class="{'border-red-500 focus:ring-red-500': form.errors.total_person_ids}"
                                    placeholder="e.g. 101, 205, 310"
                                    class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 py-3 dark:text-white shadow-sm transition-all"
                                ></textarea>
                                <p v-if="form.errors.total_person_ids" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.total_person_ids }}</p>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Office</label>
                                    <select v-model="form.operational_office" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white py-3 transition-all">
                                        <option :value="null">All Locations (Global)</option>
                                        <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
                                    </select>
                                    <p v-if="form.errors.operational_office" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.operational_office }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Division</label>
                                    <select v-model="form.division" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white py-3 transition-all">
                                        <option :value="null">All Divisions</option>
                                        <option v-for="d in divisions" :key="d.id" :value="d.id">{{ d.name }}</option>
                                    </select>
                                    <p v-if="form.errors.division" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.division }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Department</label>
                                    <select v-model="form.department" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white py-3 transition-all">
                                        <option :value="null">All Departments</option>
                                        <option v-for="dp in departments" :key="dp.id" :value="dp.id">{{ dp.name }}</option>
                                    </select>
                                    <p v-if="form.errors.department" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.department }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <div class="flex items-center space-x-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">
                                <span class="text-indigo-600 dark:text-indigo-400 font-black text-xl">03.</span>
                                <h4 class="font-bold text-gray-800 dark:text-white uppercase tracking-widest text-xs">Additional Info</h4>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Notes / Remarks</label>
                                <textarea
                                    v-model="form.remarks"
                                    rows="3"
                                    :class="{'border-red-500': form.errors.remarks}"
                                    class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-indigo-500 py-4 dark:text-white shadow-sm transition-all"
                                    placeholder="Enter any additional details here..."
                                ></textarea>
                                <p v-if="form.errors.remarks" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.remarks }}</p>
                            </div>
                        </section>
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-end gap-4">
                        <button type="button" @click="form.reset(); form.clearErrors();" class="px-8 py-3 text-sm font-bold text-gray-500 hover:text-gray-800 dark:hover:text-white transition-colors">
                            Discard Changes
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-12 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition-all transform active:scale-95 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Configuration' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import FlatpickrInput from '@/components/FlatpickrInput.vue'

const props = defineProps({
    offices: Array,
    departments: Array,
    divisions: Array,
    holidayTypes: Array,
    mode: { type: String, default: 'create' }
});

const form = useForm({
    is_personwise: false,
    total_person_ids: '',
    operational_office: null,
    department: null,
    division: null,
    holiday_type: '',
    date_from: '',
    date_to: '',
    remarks: ''
});

const submit = () => {
    // Direct URL submission to /holidays
    form.post('/holidays', {
        preserveScroll: true,
        onSuccess: () => {
            alert('Holiday saved successfully.');
            form.reset();
        },
        onError: () => {
            // Errors are automatically caught by Inertia and placed in form.errors
            console.log("Submission failed. Review errors in the form.");
        }
    });
};
</script>

<style scoped>
@reference "../../../css/app.css";

.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.input-wrapper :deep(label) {
    @apply text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 block;
}
</style>
