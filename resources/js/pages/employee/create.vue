<template>
  <AppLayout
    title="Employee Management"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Employees', href: '/employees' },
      { title: mode === 'create' ? 'Add New Employee' : 'Edit Employee', href: '#' },
    ]"
  >
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-t-2xl shadow-lg p-6 mb-0">
          <h1 class="text-3xl font-bold text-gray-900 text-center">
            {{ mode === 'create' ? 'Employee Registration' : 'Update Employee' }}
          </h1>
          <p class="mt-2 text-sm text-gray-600 text-center">
            Complete all sections. Fields marked with * are required.
          </p>
        </div>

        <!-- Tabs -->
        <div class="bg-gray-50 border-b border-gray-200">
          <div class="flex overflow-x-auto">
            <button
              v-for="section in sections"
              :key="section.id"
              @click="activeSection = section.id"
              class="flex items-center gap-2 px-6 py-4 border-b-2 transition-colors whitespace-nowrap"
              :class="[
                activeSection === section.id
                  ? 'border-blue-600 text-blue-600 bg-white'
                  : 'border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              <component :is="section.icon" class="w-5 h-5" />
              <span class="font-medium">{{ section.label }}</span>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white shadow-lg rounded-b-2xl p-8 space-y-10">

          <!-- Personal -->
          <FormSection v-if="activeSection === 'personal'" title="Personal Information" icon="user">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Person Autocomplete (Name + Photo) -->
             <PersonAutocomplete
              v-model="form.designation_id"
              @update:name="form.designation_name = $event"
              label="Designation"
              endpoint="/api/designations/search"
              required
              :error="errors.designation_id || errors.designation_name"
            />

              <SelectInput
                v-model="form.gender"
                label="Gender"
                :options="['', 'Male', 'Female', 'Other']"
                :error="errors.gender"
              />

              <SelectInput
                v-model="form.marital_status"
                label="Marital Status"
                :options="['', 'Single', 'Married', 'Divorced', 'Widowed']"
                :error="errors.marital_status"
              />

              <!-- Blood Group Dropdown -->
                          <SelectInput
                  v-model="form.blood_group"
                  label="Blood Group"
                  :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                  :error="errors.blood_group"
                />

              <NumberInput
                v-model.number="form.work_experience"
                label="Work Experience (Years)"
                :error="errors.work_experience"
              />

              <div class="md:col-span-2">
                <TextareaInput
                  v-model="form.skills"
                  label="Skills"
                  rows="3"
                  placeholder="List skills separated by commas"
                  :error="errors.skills"
                />
              </div>
            </div>
          </FormSection>

          <!-- Company -->
          <FormSection v-if="activeSection === 'company'" title="Company Information" icon="building">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Company Dropdown -->
              <!-- <CompanySelect
                v-model="form.company_id"
                @update:name="form.company_name = $event"
                :error="errors.company_id"
              /> -->
               <SelectInput
                   v-model="form.company_id"
                  label="Company "
                  :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                  :error="errors.company_id"
                />

              <!-- Financial Company -->
              <SelectInput
                v-model="form.fin_com_id"
                label="Financial Company"
                 :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                :error="errors.fin_com_id"
              />

               <SelectInput
                   v-model="form.division_id"
                  label="division "
                  :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                  :error="errors.division_id"
                />

              <!-- Department -->
               <SelectInput
                   v-model="form.department_id"
                  label="department "
                  :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                  :error="errors.department_id"
                />
              <!-- Designation -->
             <Autocomplete
                  v-model="form.designation_id"
                  @update:name="form.designation_name = $event"
                  label="Designation"
                  endpoint="/api/designations/search"
                  required
                  :error="errors.designation_id || errors.designation_name"
                />

              <!-- Department Head -->
             <Autocomplete
                v-model="form.deparment_head"
                @update:name="form.deparment_head_name = $event"
                label="Department Head"
                endpoint="/api/persons/search"
                :error="errors.deparment_head"
              />
            </div>
          </FormSection>

          <!-- Employment -->
          <FormSection v-if="activeSection === 'employment'" title="Employment Details" icon="briefcase">
            <div class="space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <TextInput
                  v-model="form.employee_id"
                  label="Employee ID"
                  required
                  maxlength="20"
                  :error="errors.employee_id"
                />

                <!-- Flatpickr Date -->
                <FlatpickrInput
                  v-model="form.joining_date"
                  label="Joining Date"
                  required
                  :error="errors.joining_date"
                />

                <FlatpickrInput
                  v-model="form.confirmation_date"
                  label="Confirmation Date"
                  :error="errors.confirmation_date"
                />

                <FlatpickrInput
                  v-model="form.probation_end_date"
                  label="Probation End Date"
                  :error="errors.probation_end_date"
                />

                <FlatpickrInput
                  v-model="form.effective_date"
                  label="Effective Date"
                  :error="errors.effective_date"
                />

                <SelectInput
                  v-model="form.employment_type"
                  label="Employment Type"
                  :options="employmentTypes"
                  required
                  :error="errors.employment_type"
                />

                <SelectInput
                  v-model="form.employee_status"
                  label="Employee Status"
                  :options="statusOptions"
                  :error="errors.employee_status"
                />

                <TextInput
                  v-model="form.work_location"
                  label="Work Location"
                  maxlength="100"
                  :error="errors.work_location"
                />

                <TextInput
                  v-model="form.shift"
                  label="Shift"
                  placeholder="e.g., Day, Night"
                  maxlength="50"
                  :error="errors.shift"
                />

                <TextInput
                  v-model="form.official_email"
                  label="Official Email"
                  type="email"
                  maxlength="100"
                  :error="errors.official_email"
                />

                <TextInput
                  v-model="form.official_phone"
                  label="Official Phone"
                  maxlength="20"
                  :error="errors.official_phone"
                />

                <!-- Flatpickr Time -->
                <FlatpickrInput
                  v-model="form.office_in_time"
                  label="Office In Time"
                  mode="time"
                  :error="errors.office_in_time"
                />

                <FlatpickrInput
                  v-model="form.office_out_time"
                  label="Office Out Time"
                  mode="time"
                  :error="errors.office_out_time"
                />

                <NumberInput
                  v-model.number="form.late_time"
                  label="Late Time (minutes)"
                  :error="errors.late_time"
                />

                <NumberInput
                  v-model.number="form.reporting_manager_id"
                  label="Reporting Manager ID"
                  :error="errors.reporting_manager_id"
                />

                <NumberInput
                  v-model.number="form.second_reporting_manager_id"
                  label="2nd Reporting Manager ID"
                  :error="errors.second_reporting_manager_id"
                />
              </div>

              <!-- Emergency -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                  <Phone class="w-5 h-5 text-blue-600" /> Emergency Contact
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <TextInput v-model="form.emergency_contact_name" label="Name" maxlength="100" :error="errors.emergency_contact_name" />
                  <TextInput v-model="form.emergency_contact_phone" label="Phone" maxlength="20" :error="errors.emergency_contact_phone" />
                </div>
              </div>

              <!-- Performance -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                  <Award class="w-5 h-5 text-blue-600" /> Performance & Promotions
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <FlatpickrInput v-model="form.last_appraisal_date" label="Last Appraisal" :error="errors.last_appraisal_date" />
                  <FlatpickrInput v-model="form.next_appraisal_date" label="Next Appraisal" :error="errors.next_appraisal_date" />
                  <FlatpickrInput v-model="form.last_promotion_date" label="Last Promotion" :error="errors.last_promotion_date" />
                  <FlatpickrInput v-model="form.next_promotion_due" label="Next Promotion Due" :error="errors.next_promotion_due" />
                </div>
              </div>

              <div class="border-t pt-6">
                <TextareaInput v-model="form.office_time" label="Office Time Notes" rows="3" :error="errors.office_time" />
              </div>
            </div>
          </FormSection>

          <!-- Salary & Banking (unchanged) -->
          <!-- ... [keep as-is] ... -->

          <!-- Navigation -->
          <div class="flex justify-between items-center pt-8 border-t border-gray-200">
            <button type="button" @click="goToPrevious" :disabled="activeSection === 'personal'" class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
              Previous
            </button>

            <button v-if="activeSection !== 'banking'" type="button" @click="goToNext" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
              Next
            </button>

            <button v-else type="submit" :disabled="form.processing" class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-semibold hover:from-green-700 hover:to-emerald-700 transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2">
              <span v-if="form.processing" class="animate-spin">Loading...</span>
              {{ mode === 'create' ? 'Submit' : 'Update' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed, ref, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormSection from '@/Components/FormSection.vue'
import TextInput from '@/Components/TextInput.vue'
import NumberInput from '@/Components/NumberInput.vue'
import DateInput from '@/Components/DateInput.vue'
import TimeInput from '@/Components/TimeInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextareaInput from '@/Components/TextareaInput.vue'
import PersonAutocomplete from '@/Components/PersonAutocomplete.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import Autocomplete from '@/components/Autocomplete.vue'

import { User, Building2, Briefcase, DollarSign, CreditCard, Phone, Award } from 'lucide-vue-next'

const props = defineProps({
  employee: Object,
  mode: { type: String, required: true },
})

// === TABS ===
const sections = [
  { id: 'personal',   label: 'Personal Info',   icon: User },
  { id: 'company',    label: 'Company Info',    icon: Building2 },
  { id: 'employment', label: 'Employment',      icon: Briefcase },
  { id: 'salary',     label: 'Salary',          icon: DollarSign },
  { id: 'banking',    label: 'Banking & Tax',   icon: CreditCard },
]

const activeSection = ref('personal')

const goToPrevious = () => {
  const i = sections.findIndex(s => s.id === activeSection.value)
  if (i > 0) activeSection.value = sections[i - 1].id
}

const goToNext = () => {
  const i = sections.findIndex(s => s.id === activeSection.value)
  if (i < sections.length - 1) activeSection.value = sections[i + 1].id
}

// === OPTIONS ===
const employmentTypes = ['Permanent', 'Contract', 'Intern', 'Probation', 'Freelancer']
const statusOptions = ['Active', 'Inactive', 'Terminated', 'Resigned', 'Retired', 'On Leave']

// === FORM ===
const form = useForm(
  reactive({
    person_id: '',
    person_name: '',
    company_id: null,
    company_name: '',
    fin_com_name: '',
    fin_com_id: null,
    division_id: null,
    division_name: '',
    department_id: null,
    department_name: '',
    designation_id: null,
    designation_name: '',
    employee_code: '',
    employee_id: '',
    joining_date: '',
    confirmation_date: null,
    probation_end_date: null,
    effective_date: null,
    employment_type: 'Permanent',
    work_location: '',
    shift: '',
    official_email: '',
    official_phone: '',
    office_in_time: null,
    office_out_time: null,
    late_time: 0,
    employee_status: 'Active',
    gross_salary: 0,
    basic_salary: 0,
    house_rent_allowance: 0,
    medical_allowance: 0,
    transport_allowance: 0,
    other_allowances: 0,
    overtime_rate: 0,
    total_salary: 0,
    currency: 'USD',
    bank_name: '',
    bank_account_no: '',
    bank_ifsc_code: '',
    pan_number: '',
    tax_status: '',
    social_security_no: '',
    passport_number: '',
    is_tax_dedction: '0',
    is_salary_stop: '0',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    marital_status: '',
    gender: '',
    blood_group: '',
    work_experience: null,
    skills: '',
    reporting_manager_id: null,
    reporting_manager_name: '',
    second_reporting_manager_id: null,
    second_reporting_manager_name: '',
    deparment_head: null,
    deparment_head_name: '',
    last_appraisal_date: null,
    next_appraisal_date: null,
    last_promotion_date: null,
    next_promotion_due: null,
    office_time: '',
  })
)

// Fill on edit
if (props.employee) form.fill(props.employee)

// === AUTO CALCULATE ===
const grossSalary = computed(() => {
  return (
    Number(form.basic_salary || 0) +
    Number(form.house_rent_allowance || 0) +
    Number(form.medical_allowance || 0) +
    Number(form.transport_allowance || 0) +
    Number(form.other_allowances || 0)
  )
})

watch(
  () => [
    form.basic_salary,
    form.house_rent_allowance,
    form.medical_allowance,
    form.transport_allowance,
    form.other_allowances,
    form.overtime_rate,
  ],
  () => {
    form.gross_salary = grossSalary.value
    form.total_salary = grossSalary.value + Number(form.overtime_rate || 0)
  },
  { immediate: true }
)

// === VALIDATION ===
const errors = ref({})

const validationRules = {
  personal: [
    { field: 'person_id', rule: v => !!v && v > 0 },
    { field: 'person_name', rule: v => !!v && v.trim().length > 0 },
  ],
  company: [
    { field: 'department_id', rule: v => !!v && v > 0 },
    { field: 'department_name', rule: v => !!v && v.trim().length > 0 },
    { field: 'designation_id', rule: v => !!v && v > 0 },
    { field: 'designation_name', rule: v => !!v && v.trim().length > 0 },
  ],
  employment: [
    { field: 'employee_code', rule: v => !!v && v.trim().length > 0 },
    { field: 'employee_id', rule: v => !!v && v.trim().length > 0 },
    { field: 'joining_date', rule: v => !!v },
    { field: 'employment_type', rule: v => !!v },
  ],
  salary: [
    { field: 'basic_salary', rule: v => v >= 0 },
  ],
}

function validateTab(tabId) {
  const rules = validationRules[tabId] || []
  const tabErrors = {}
  rules.forEach(({ field, rule }) => {
    if (!rule(form[field])) {
      tabErrors[field] = 'This field is required.'
    }
  })
  return tabErrors
}

function submit() {
  errors.value = {}
  let hasError = false
  let firstErrorTab = null

  sections.forEach(section => {
    const tabErrors = validateTab(section.id)
    if (Object.keys(tabErrors).length > 0) {
      errors.value = { ...errors.value, ...tabErrors }
      if (!firstErrorTab) firstErrorTab = section.id
      hasError = true
    }
  })

  if (hasError) {
    activeSection.value = firstErrorTab
    nextTick(() => {
      document.querySelector('form')?.scrollIntoView({ behavior: 'smooth' })
    })
    return
  }

  const method = props.mode === 'create' ? 'post' : 'put'
  const url = props.mode === 'create' ? '/employees' : `/employees/${props.employee.id}`

  form[method](url, {
    onSuccess: () => {
      if (props.mode === 'create') form.reset()
    },
    onError: (err) => {
      errors.value = err
      const firstField = Object.keys(err)[0]
      const tab = Object.entries(validationRules).find(([, rules]) =>
        rules.some(r => r.field === firstField)
      )
      if (tab) {
        activeSection.value = tab[0]
        nextTick(() => {
          document.querySelector(`[name="${firstField}"]`)?.scrollIntoView({ behavior: 'smooth' })
        })
      }
    }
  })
}
</script>