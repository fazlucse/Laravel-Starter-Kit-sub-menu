<template>
    <AppLayout
        title="Edit Movement"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Movement Register', href: '/movement-registers' },
            { title: 'Edit Movement' }
        ]"
    >
        <div class="bg-gray-50 dark:bg-gray-950 py-4 px-4">
            <div class="max-w-5xl mx-auto">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                    <div class="px-8 py-4 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Edit Movement Record #{{ movement.id }}
                        </h1>
                    </div>

                    <div class="p-5">
                        <form @submit.prevent="submitForm" class="space-y-6">

                            <div class="grid lg:grid-cols-2 gap-4">
                                <LocationSection
                                    v-model="form.from"
                                    label="From Location"
                                    :factories="factories"
                                    :sub-factories="subFactories"
                                    class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                                />
                                <LocationSection
                                    v-model="form.to"
                                    label="To Location"
                                    :factories="factories"
                                    :sub-factories="subFactories"
                                    class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                                />
                            </div>

                            <MultiSelect
                                :items="purposes"
                                v-model="form.purpose"
                                :multiple="true"
                                placeholder="Choose a Purpose"
                                label="Purpose"
                                label-key="name"
                                id-key="id"
                                :required="true"
                                :error-message="form.errors.purpose"
                            />

                            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                                <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                    Visit For <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <RadioOption v-model="form.visit_for" value="Customer" label="Customer Visit" class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                    <RadioOption v-model="form.visit_for" value="Development" label="Development" class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                    <RadioOption v-model="form.visit_for" value="Others" label="Others" class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                </div>

                                <div v-if="form.visit_for" class="mt-4 space-y-4">
                                    <MultiSelect v-if="form.visit_for === 'Customer'"
                                                 :items="customers"
                                                 v-model="form.customer_id"
                                                 :multiple="true"
                                                 placeholder="Choose a Customer"
                                                 label="Customer"
                                                 label-key="name"
                                                 id-key="id"
                                                 :required="true"
                                                 :error-message="form.errors.customer_id"
                                    />
                                    <FormInput v-if="form.visit_for === 'Development'" v-model="form.development" label="Development" placeholder="Development Detail" required/>
                                    <FormInput v-else-if="form.visit_for === 'Others'" v-model="form.others" label="Other Name" placeholder="Other Name Detail" required/>
                                </div>
                            </div>

                            <MultiSelect
                                :items="transports"
                                v-model="form.means_of_transport"
                                :multiple="true"
                                placeholder="Choose a Means of Transport"
                                label="Means of Transport"
                                label-key="name"
                                id-key="id"
                                :required="true"
                                :error-message="form.errors.means_of_transport"
                            />

                            <div class="grid md:grid-cols-2 gap-6 p-6 bg-blue-50 dark:bg-blue-900/10 rounded-xl border border-blue-200 dark:border-blue-800">
                                <FormInput v-model="form.budgeted_bill" label="Budgeted Bill (BDT)" type="number" placeholder="Optional amount"/>
                                <FormInput v-model="form.conveyance_bill" label="Actual Conveyance Bill (BDT)" type="number" required placeholder="Enter amount spent"/>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link href="/movement-registers" class="text-gray-600 hover:underline">Cancel</Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-10 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all"
                                >
                                    {{ form.processing ? 'Saving...' : 'Update Record' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import LocationSection from '@/components/movement/LocationSection.vue'
import FormInput from '@/components/movement/FormInput.vue'
import MultiSelect from '@/components/custom/MultiSelect.vue'
import RadioOption from '@/components/movement/RadioOption.vue'
import { useAlert } from '@/composables/useAlert'

const props = defineProps({
    movement: Object
})

const { showAlert } = useAlert()

// --- API Data Refs ---
const factories = ref([])
const subFactories = ref([])
const purposes = ref([])
const customers = ref([])
const transports = ref([])

// Fetch settings from API on mount
onMounted(async () => {
    try {
        const response = await axios.get('/api/movement-settings');
        factories.value = response.data.factories;
        subFactories.value = response.data.sub_factories;
        purposes.value = response.data.purposes;
        customers.value = response.data.customers;
        transports.value = response.data.transports;
        loading.value = false;
    } catch (error) {
        console.error("Failed to fetch settings:", error);
        showAlert("Error loading dropdown data", "error");
    }
})

// --- Form Initialization ---
const form = useForm({
    from: {
        type: props.movement.origin_location_type,
        factory: props.movement.origin_location_type === 'Factory'
            ? { id: Number(props.movement.origin_location_id), name: props.movement.origin_location_name }
            : null,
        sub_factory: props.movement.origin_location_type === 'Sub Factory'
            ? { id: Number(props.movement.origin_location_id), name: props.movement.origin_location_name }
            : null,
        new_name: props.movement.origin_location_type === 'Other' ? props.movement.origin_location_name : ''
    },
    to: {
        type: props.movement.destination_location_type,
        factory: props.movement.destination_location_type === 'Factory'
            ? { id: Number(props.movement.destination_location_id), name: props.movement.destination_location_name }
            : null,
        sub_factory: props.movement.destination_location_type === 'Sub Factory'
            ? { id: Number(props.movement.destination_location_id), name: props.movement.destination_location_name }
            : null,
        new_name: props.movement.destination_location_type === 'Other' ? props.movement.destination_location_name : ''
    },
    purpose: props.movement.purpose
        ? (Array.isArray(props.movement.purpose) ? props.movement.purpose : [props.movement.purpose])
        : [],
    visit_for: props.movement.visit_type || '',
    customer_id: props.movement.customer_obj
        ? (Array.isArray(props.movement.customer_obj) ? props.movement.customer_obj : [props.movement.purpose])
        : [],
    development: props.movement.visit_type === 'Development'
        ?  props.movement.customer_name : '',

    // If it's Others, strip "Other: " from bill_remarks
    others: props.movement.visit_type === 'Others'
        ? props.movement.customer_name : '',
    means_of_transport: props.movement.transport_mode
        ? props.movement.transport_mode.map(item => {
            return typeof item === 'string' ? { id: item, name: item } : item;
        })
        : [],
    budgeted_bill: props.movement.budgeted_amount || '',
    conveyance_bill: props.movement.conveyance_amount || '',
    is_finishing: false,
})

// --- Submit Logic ---
const submitForm = () => {
    form.transform((data) => ({
        ...data,
        from: {
            ...data.from,
            factory: data.from.factory?.id || null,
            factory_name: data.from.factory?.name || null,
            sub_factory: data.from.sub_factory?.id || null,
            sub_factory_name: data.from.sub_factory?.name || null,
        },
        to: {
            ...data.to,
            factory: data.to.factory?.id || null,
            factory_name: data.to.factory?.name || null,
            sub_factory: data.to.sub_factory?.id || null,
            sub_factory_name: data.to.sub_factory?.name || null,
        }
    })).put(`/movement-registers/${props.movement.id}`, {
        onSuccess: () => {
            // showAlert('Movement updated!', 'success')
        },
        onError: (errors) => showAlert(Object.values(errors)[0], 'error')
    });
}
</script>
