<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'

const props = defineProps<{
    company?: any
}>()

const isEdit = !!props.company

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST',
    name: props.company?.name || '',
    type: props.company?.type || '',
    company_code: props.company?.company_code || '',
    registration_no: props.company?.registration_no || '',
    tax_identification_no: props.company?.tax_identification_no || '',
    email: props.company?.email || '',
    phone: props.company?.phone || '',
    website: props.company?.website || '',
    address_line1: props.company?.address_line1 || '',
    address_line2: props.company?.address_line2 || '',
    city: props.company?.city || '',
    state: props.company?.state || '',
    country: props.company?.country || '',
    postal_code: props.company?.postal_code || '',
    ownership_type: props.company?.ownership_type || '',
    status: props.company?.status || 'Active',
    logo: null as File | null,
})

const logoPreview = ref(
    props.company?.logo_path ? `/${props.company.logo_path}` : null
)

function handleLogo(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file) {
        form.logo = file
        logoPreview.value = URL.createObjectURL(file)
    }
}

const submit = () => {
    // Matches your Route::post('/store') and Route::put('/update/{company}')
    const url = isEdit ? `/companies/update/${props.company.id}` : '/companies/store';

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Optional: add a notification here
        }
    });
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Company' : 'Create Company'" :breadcrumbs="[
    { title: 'Companies', href: '/companies' },
    { title: isEdit ? 'Edit Company' : 'Create Company' }
  ]">
        <div class="max-w-5xl mx-auto p-2 sm:p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-8 border border-gray-200 dark:border-gray-700">
                <form @submit.prevent="submit" class="space-y-8">

                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4 border-b pb-2">Primary Identity</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name"
                                       class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                       :class="form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Industry Type <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.type"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.type }"
                                        class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 transition-colors dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                    <option value="">Select Industry</option>
                                    <option value="Own Company">Own Company</option>
                                    <option value="Financial Company">Financial Company</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Factory">Factory</option>
                                    <option value="Other">Other</option>
                                </select>

                                <div v-if="form.errors.type" class="mt-1 text-xs font-bold text-red-500 uppercase italic">
                                    {{ form.errors.type }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
                                <select v-model="form.status"
                                        class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4 border-b pb-2">Legal & Branding</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <div class="space-y-4 md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Code</label>
                                        <input v-model="form.company_code" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Registration No</label>
                                        <input v-model="form.registration_no" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax ID (TIN)</label>
                                        <input v-model="form.tax_identification_no" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ownership Type</label>
                                        <input v-model="form.ownership_type" placeholder="e.g. Private Limited" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Logo</label>
                                <div v-if="logoPreview" class="mb-3">
                                    <img :src="logoPreview" class="w-32 h-32 object-contain border rounded-lg bg-gray-50 dark:bg-gray-900" />
                                </div>
                                <input type="file" accept="image/*" @change="handleLogo"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4 border-b pb-2">Communication & Address</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Official Email</label>
                                <input v-model="form.email" type="email" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                <input v-model="form.phone" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                                <input v-model="form.website" placeholder="https://" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 1</label>
                                <input v-model="form.address_line1" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 2</label>
                                <input v-model="form.address_line2" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                                <input v-model="form.city" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State</label>
                                <input v-model="form.state" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                                <input v-model="form.country" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zip Code</label>
                                <input v-model="form.postal_code" class="w-full rounded-md border px-3 py-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <Link href="/companies"
                              class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-center transition">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                                class="cursor-pointer px-8 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition flex items-center justify-center gap-2 font-semibold shadow-md">
                            <LoadingSpinner v-if="form.processing" />
                            {{ isEdit ? 'Update Company' : 'Save Company' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>
