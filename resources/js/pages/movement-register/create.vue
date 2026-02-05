<template>
    <AppLayout
        title="Movement Register"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Movement Register', href: '/movement-registers' },
      { title: mode === 'create' ? 'Add New Movement' : 'Edit Movement' }
    ]"
    >
        <div class=" bg-gray-50 dark:bg-gray-950 py-4 px-4">
            <div class="max-w-5xl mx-auto">
                <!-- Main Card -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header -->
                    <div
                        class="px-8 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <!-- Title -->
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white text-center sm:text-left">
                            {{ mode === 'create' ? 'Add New Movement' : 'Edit Movement' }}
                        </h1>

                        <!-- Badge -->
                        <span
                            class="mt-3 sm:mt-0 px-5 py-2 text-red-600 bg-gray-100 dark:bg-gray-800 rounded-full text-sm font-medium border border-gray-300 dark:border-gray-600 text-center sm:text-right">
        Fields marked with * are required
    </span>
                    </div>


                    <!-- Form Body -->
                    <div class="p-5 lg:p-5">
                        <form @submit.prevent="submitForm" class="space-y-5">
                            <!-- From & To Locations -->
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
                                error-message=""
                            />

                            <!-- Visit For -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                                <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                    Visit For <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <RadioOption v-model="form.visit_for" value="Customer" label="Customer Visit"
                                                 class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                    <RadioOption v-model="form.visit_for" value="Development" label="Development"
                                                 class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                    <RadioOption v-model="form.visit_for" value="Others" label="Others"
                                                 class="border-2 p-1 border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all"/>
                                </div>

                                <Transition name="fade">
                                    <div v-if="form.visit_for" class="mt-3 space-y-2">

                                        <MultiSelect v-if="form.visit_for === 'Customer'"
                                                     :items="purposes"
                                                     v-model="form.customer_id"
                                                     :multiple="true"
                                                     placeholder="Choose a Customer"
                                                     label="Customer"
                                                     label-key="name"
                                                     id-key="id"
                                                     :required="true"
                                                     error-message=""
                                        />
                                        <FormInput v-if="form.visit_for === 'Development'" v-model="form.development"
                                                   label="Development"
                                                   placeholder="Development" required/>
                                        <div v-else-if="form.visit_for === 'Others'">
                                            <FormInput v-model="form.others" label="Other Name"
                                                       placeholder="Other Name" required/>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                            <MultiSelect
                                :items="transports"
                                v-model="form.means_of_trandport"
                                :multiple="true"
                                placeholder="Choose a Means of Transport"
                                label="Means of Transport"
                                label-key="name"
                                id-key="id"
                                :required="true"
                                :error-message="errors.means_of_trandport"
                            />
                            <Transition name="fade">
                                <div v-if="movement.started"
                                     class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-xl p-7">
                                    <div class="flex items-center gap-3 mb-6">
                                        <Clock class="w-6 h-6 text-amber-700 dark:text-amber-400"/>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                          Movement Started: {{ movement.start_time }}
                                        </span>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <FormInput v-model="form.budgeted_bill" label="Budgeted Bill (BDT)"
                                                   type="number" readonly class="bg-white dark:bg-gray-800"/>
                                        <FormInput v-model="form.conveyance_bill" label="Actual Conveyance Bill (BDT)"
                                                   type="number" required placeholder="Enter actual amount spent"/>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-6 justify-center pt-5">
                                <button
                                    type="submit"
                                    class="px-14 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded-xl shadow-xl transition-all flex items-center gap-4 justify-center"
                                >
                                    Save Movement
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
import {reactive, ref} from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import LocationSection from '@/components/movement/LocationSection.vue'
import FormSelect from '@/components/movement/FormSelect.vue'
import FormInput from '@/components/movement/FormInput.vue'
import MultiSelect from '@/components/custom/MultiSelect.vue'
import RadioOption from '@/components/movement/RadioOption.vue'
import {Clock} from 'lucide-vue-next'

const mode = 'create'
const selectedFactory = ref(null)  // Fixed: was selectedCountry
const selectedPurpose = ref(null)  // Fixed: was selectedCountry

const form = reactive({
    from: {type: '', ex_type: '', factory: '', sub_factory: '', new_name: ''},
    to: {type: '', ex_type: '', factory: '', sub_factory: '', new_name: ''},
    purpose: [],
    visit_for: '',
    customer_id: [],
    development: '',
    others: '',
    means_of_trandport: [],
    budgeted_bill: '',
    conveyance_bill: ''
})
const errors = ref({
    means_of_trandport: '' // Make sure this starts as empty string
})
const movement = reactive({
    started: false,
    ended: false,
    start_time: null
})

// Dummy Data
const factories = ref([
    {id: 1, name: 'Main Factory - Dhaka'},
    {id: 2, name: 'Factory Unit 2 - Gazipur'},
    {id: 3, name: 'Factory Unit 3 - Chittagong'},
    {id: 5, name: 'Savar Factory 5'},
    {id: 6, name: 'Savar Factory 6'},
    {id: 7, name: 'Savar Factory 7'},
    {id: 8, name: 'Savar Factory 8'},
    {id: 9, name: 'Savar Factory 9'},
    {id: 10, name: 'Savar Factory 10'},
    {id: 11, name: 'Savar Factory 11'},
])

const subFactories = ref([
    {id: 101, name: 'Sub Factory Knitting Unit - Narayanganj'},
    {id: 102, name: 'Sub Factory  Dyeing Unit - Tongi'},
    {id: 103, name: 'Sub Factory  Printing Unit - Savar'}
])

const purposes = ref([
    {id: 1, name: 'Official Meeting'},
    {id: 2, name: 'Factory Visit'},
    {id: 3, name: 'Customer Visit'},
    {id: 4, name: 'Training & Audit'}
])

const customers = ref([
    {id: 201, name: 'H&M Bangladesh'},
    {id: 202, name: 'Walmart Global'},
    {id: 203, name: 'Zara Inditex'},
    {id: 204, name: 'Primark UK'}
])

const transports = ref([
    {id: 301, name: 'Company Car'},
    {id: 302, name: 'CNG Auto'},
    {id: 303, name: 'Uber / Pathao'},
    {id: 304, name: 'Public Bus'},
    {id: 305, name: 'Office Bus (AC)'}
])

const startMovement = () => {
    movement.started = true
    movement.start_time = new Date().toLocaleString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
    form.budgeted_bill = '2500.00'
}

const endMovement = () => {
    movement.ended = true
}

const submitForm = () => {

    console.log("FULL FORM DATA →", JSON.parse(JSON.stringify(form))); // BEST way to inspect reactive
    console.log("RAW FORM OBJECT →", form);
    return;
    const isValid = multiSelectRef.value.validate()  // This triggers error display

    if (isValid) {
        console.log('Form is valid!', selected.value)
        // Proceed with submission
    } else {
        console.log('Form has errors')
    }
    if (!movement.ended) {
        alert('Please end the movement before saving!')
        return
    }
    console.log('Movement Saved →', {form, movement})
    alert('Movement registered successfully!')
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
