<!-- resources/js/pages/movement-register/create.vue -->
<template>
    <AppLayout
        title="Movement Register"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Movement Register', href: '/movement-registers' }
    ]"
    >
        <div class="mx-auto max-w-4xl bg-white rounded-xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Movement Register</h2>
                <button @click="$emit('close')" class="text-white hover:bg-white/20 p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <div class="p-6 max-h-[78vh] overflow-y-auto">
                <form @submit.prevent="submitForm" class="space-y-8">

                    <div class="flex items-center gap-2 text-red-600 text-sm font-medium">
                        [ <span class="text-2xl">*</span> Star mark indicates mandatory field ]
                    </div>

                    <!-- From & To -->
                    <LocationSection v-model="form.from" label="From" :factories="factories" :sub-factories="subFactories" />
                    <LocationSection v-model="form.to"   label="To"   :factories="factories" :sub-factories="subFactories" />

                    <!-- Purpose -->
                    <FormSelect v-model="form.purpose" label="Purpose" :options="purposes" required />

                    <!-- Visit For -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Visit For <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-8">
                            <RadioOption v-model="form.visit_for" value="Customer"     label="Customer" />
                            <RadioOption v-model="form.visit_for" value="Development" label="Development" />
                            <RadioOption v-model="form.visit_for" value="Others"      label="Others" />
                        </div>

                        <div v-if="form.visit_for === 'Customer'" class="mt-5">
                            <FormSelect v-model="form.customer_id" label="Customer" :options="customers" required />
                        </div>
                        <div v-if="form.visit_for === 'Development'" class="mt-5">
                            <FormInput v-model="form.development" label="Development Purpose" required />
                        </div>
                        <div v-if="form.visit_for === 'Others'" class="mt-5">
                            <FormInput v-model="form.others" label="Others (Specify)" required />
                        </div>
                    </div>

                    <!-- Means of Transport -->
                    <FormSelect v-model="form.means_of_transport" label="Means of Transport" :options="transports" required />

                    <!-- Bills (after start) -->
                    <div v-if="movement.started" class="grid grid-cols-2 gap-6 pt-6 border-t">
                        <FormInput v-model="form.budgeted_bill" label="Total Budgeted Bill" type="number" readonly />
                        <FormInput v-model="form.conveyance_bill" label="Total Conveyance Bill" type="number" required />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-center gap-6 pt-8">
                        <button v-if="!movement.started" @click="startMovement"
                                class="px-10 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg transition">
                            Start Movement
                        </button>

                        <button v-if="movement.started && !movement.ended" @click="endMovement"
                                class="px-10 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-lg transition">
                            End Movement
                        </button>

                        <button v-if="movement.ended" type="submit"
                                class="px-12 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-xl transition">
                            Save Movement
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

import LocationSection from '@/components/movement/LocationSection.vue'
import FormSelect      from '@/components/movement/FormSelect.vue'      // Searchable dropdown
import FormInput       from '@/components/movement/FormInput.vue'
import RadioOption     from '@/components/movement/RadioOption.vue'

const form = reactive({
    from: { type: '', ex_type: '', factory: '', sub_factory: '', new_name: '' },
    to:   { type: '', ex_type: '', factory: '', sub_factory: '', new_name: '' },
    purpose: '',
    visit_for: '',
    customer_id: '',
    development: '',
    others: '',
    means_of_transport: '',
    budgeted_bill: '',
    conveyance_bill: ''
})

const movement = reactive({ started: false, ended: false, start_time: null })

// Dummy data – all searchable
const factories = ref([
    { id: 1, name: 'Main Factory - Dhaka' },
    { id: 2, name: 'Factory Unit 2 - Gazipur' },
    { id: 3, name: 'Factory Unit 3 - Chittagong' },
    { id: 4, name: 'Savar Factory' }
])

const subFactories = ref([
    { id: 101, name: 'Knitting Unit - Narayanganj' },
    { id: 102, name: 'Dyeing Unit - Tongi' },
    { id: 103, name: 'Printing Unit - Savar' }
])

const purposes = ref([
    { id: 1, name: 'Official Meeting' },
    { id: 2, name: 'Factory Visit' },
    { id: 3, name: 'Customer Visit' },
    { id: 4, name: 'Training & Audit' }
])

const customers = ref([
    { id: 201, name: 'H&M Bangladesh' },
    { id: 202, name: 'Walmart Global' },
    { id: 203, name: 'Zara Inditex' },
    { id: 204, name: 'Primark UK' }
])

const transports = ref([
    { id: 301, name: 'Company Car' },
    { id: 302, name: 'CNG Auto' },
    { id: 303, name: 'Uber / Pathao' },
    { id: 304, name: 'Public Bus' },
    { id: 305, name: 'Office Bus (AC)' }
])

const startMovement = () => {
    movement.started = true
    movement.start_time = new Date().toLocaleString()
    form.budgeted_bill = '2500.00'
}

const endMovement = () => movement.ended = true

const submitForm = () => {
    if (!movement.ended) return alert('Please end movement first!')
    console.log('Movement Saved →', { form, movement })
    alert('Movement registered successfully!')
}
</script>
