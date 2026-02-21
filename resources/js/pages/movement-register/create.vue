<template>
    <AppLayout
        title="Movement Register"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Movement Register', href: '/movement-registers' },
            { title: activeMovement ? 'Complete Movement' : 'Add New Movement' }
        ]"
    >
        <div class="bg-gray-50 dark:bg-gray-950 py-4 px-4">
            <div class="max-w-5xl mx-auto">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                    <div class="px-8 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between"
                         :class="activeMovement ? 'bg-amber-50 dark:bg-amber-900/20' : ''">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white text-center sm:text-left">
                            {{ activeMovement ? 'Complete In-Progress Movement' : 'Add New Movement' }}
                        </h1>
                        <span v-if="activeMovement" class="text-amber-700 dark:text-amber-400 font-bold animate-pulse">
                            Started at: {{ formatTime(activeMovement.movement_started_at) }}
                        </span>
                    </div>

                    <div class="p-5 lg:p-5">
                        <form @submit.prevent="submitForm" class="space-y-5">

                            <div :class="{'opacity-50 pointer-events-none grayscale-[0.5]': activeMovement}" class="space-y-5">
                                <div class="grid lg:grid-cols-2 gap-2">
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
                            </div>

                            <Transition name="fade">
                                <div v-if="activeMovement" class="space-y-5 pt-5 border-t-2 border-dashed border-gray-200 dark:border-gray-700">

                                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                                        <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                            Visit For <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                            <RadioOption v-model="form.visit_for" value="Customer" label="Customer Visit" class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                            <RadioOption v-model="form.visit_for" value="Development" label="Development" class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                            <RadioOption v-model="form.visit_for" value="Others" label="Others" class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                        </div>

                                        <div v-if="form.visit_for" class="mt-3 space-y-2">
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

                                    <div class="grid md:grid-cols-2 gap-6 p-7 bg-blue-50 dark:bg-blue-900/10 rounded-xl border border-blue-200 dark:border-blue-800">
                                        <FormInput v-model="form.budgeted_bill" label="Budgeted Bill (BDT)" type="number" placeholder="Optional amount"/>
                                        <FormInput v-model="form.conveyance_bill" label="Actual Conveyance Bill (BDT)" type="number" required placeholder="Enter actual amount spent"/>
                                    </div>
                                </div>
                            </Transition>

                            <div class="flex flex-col sm:flex-row gap-6 justify-center pt-5">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    :class="activeMovement ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                                    class="cursor-pointer px-14 py-4 text-white font-bold text-xl rounded-xl shadow-xl transition-all flex items-center gap-4 justify-center"
                                >
                                    {{ form.processing ? 'Processing...' : (activeMovement ? 'Finish & Save Movement' : 'Start Movement') }}
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
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import LocationSection from '@/components/movement/LocationSection.vue'
import FormInput from '@/components/movement/FormInput.vue'
import MultiSelect from '@/components/custom/MultiSelect.vue'
import RadioOption from '@/components/movement/RadioOption.vue'
import { useAlert } from '@/composables/useAlert'

const props = defineProps({
    activeMovement: Object, // Passed from Controller create() method
})

const { showAlert } = useAlert()

/**
 * Initialize Form
 * If activeMovement exists, we pre-populate the "Start" data so the user sees what they entered.
 */

const form = useForm({
    from: props.activeMovement ? {
        type: props.activeMovement.origin_location_type,
        // Map back to object structure
        factory: props.activeMovement.origin_location_type === 'Factory'
            ? { id: props.activeMovement.origin_location_id, name: props.activeMovement.origin_location_name }
            : null,
        // Only set sub_factory if type matches
        sub_factory: props.activeMovement.origin_location_type === 'Sub Factory'
            ? { id: props.activeMovement.origin_location_id, name: props.activeMovement.origin_location_name }
            : null,
        new_name: props.activeMovement.origin_location_name
    } : { type: '', factory: null, sub_factory: null, new_name: '' },

    to: props.activeMovement ? {
        type: props.activeMovement.destination_location_type,
        // FIX: Map the destination factory back to an object
        factory: props.activeMovement.destination_location_type === 'Factory'
            ? { id: props.activeMovement.destination_location_id, name: props.activeMovement.destination_location_name }
            : null,
        // Only set sub_factory if type matches
        sub_factory: props.activeMovement.destination_location_type === 'Sub Factory'
            ? { id: props.activeMovement.destination_location_id, name: props.activeMovement.destination_location_name }
            : null,
        new_name: props.activeMovement.destination_location_name
    } : { type: '', factory: null, sub_factory: null, new_name: '' },

    // purpose: props.activeMovement ? [{ name: props.activeMovement.purpose }] : [],
    purpose: props.activeMovement
        ? (Array.isArray(props.activeMovement.purpose) ? props.activeMovement.purpose : [props.activeMovement.purpose])
        : [],
    // Fields for Step 2 (The "End")
    visit_for: '',
    customer_id: [],
    development: '',
    others: '',
    means_of_transport: [],
    budgeted_bill: '',
    conveyance_bill: '',
    is_finishing: props.activeMovement ? true : false
})

// --- Data Lists ---
const factories = ref([])
const subFactories = ref([])
const purposes = ref([])
const customers = ref([])
const transports = ref([])

onMounted(async () => {
    try {
        const res = await axios.get('/api/movement-settings');
        factories.value = res.data.factories;
        subFactories.value = res.data.sub_factories;
        purposes.value = res.data.purposes;
        customers.value = res.data.customers;
        transports.value = res.data.transports;
    } catch (e) { console.error("Error loading settings", e) }
})

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
// --- Methods ---
const submitForm = () => {
    if (!props.activeMovement) {
        // Logic for STARTING (Store)
        // form.post('/movement-registers', {
        //     onSuccess: () => showAlert('Trip started! Fill the remaining fields when finished.', 'success'),
        //     onError: (errors) => showAlert(Object.values(errors)[0], 'error')
        // });
        form.transform((data) => ({
            ...data,
            purpose: data.purpose,
            is_finishing: props.activeMovement ? true : false,
            from: {
                ...data.from,
                // Extract the ID if it's an object, otherwise keep as is
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
        })).post('/movement-registers', {
            onSuccess: () => {
                // showAlert('Success!', 'success')
            },
            onError: (errors) => showAlert(Object.values(errors)[0], 'error')
        });
    } else {
        // Logic for FINISHING (Update)
        // We use the ID from the activeMovement passed from the controller
        form.put(`/movement-registers/${props.activeMovement.id}`, {
            onSuccess: () => showAlert('Movement completed and saved.', 'success'),
            onError: (errors) => showAlert(Object.values(errors)[0], 'error')
        });
    }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
