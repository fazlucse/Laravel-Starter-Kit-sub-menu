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
              class="cursor-pointer flex items-center gap-2 px-6 py-4 border-b-2 transition-colors whitespace-nowrap"
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

          <!-- PERSONAL (v-show → keeps autocomplete alive) -->
          <FormSection v-show="activeSection === 'personal'" title="Personal Information" icon="user">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Person Autocomplete -->
              <PersonAutocomplete
                v-model="form.person_id"
                @update:name="form.person_name = $event"
                label="Person"
                endpoint="/api/persons/search"
                required
                :error="allErrors.person_id"
              />

              <SelectInput
                v-model="form.gender"
                label="Gender"
                :options="['', 'Male', 'Female', 'Other']"
                :error="allErrors.gender"
              />

              <SelectInput
                v-model="form.marital_status"
                label="Marital Status"
                :options="['', 'Single', 'Married', 'Divorced', 'Widowed']"
                :error="allErrors.marital_status"
              />

              <SelectInput
                v-model="form.blood_group"
                label="Blood Group"
                :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                :error="allErrors.blood_group"
              />

              <NumberInput
                v-model.number="form.work_experience"
                label="Work Experience (Years)"
                :error="allErrors.work_experience"
              />

              <div class="md:col-span-2">
                <TextareaInput
                  v-model="form.skills"
                  label="Skills"
                  rows="3"
                  placeholder="List skills separated by commas"
                  :error="allErrors.skills"
                />
              </div>
            </div>
          </FormSection>

          <!-- COMPANY (v-show → keeps Autocomplete alive) -->
          <FormSection v-show="activeSection === 'company'" title="Company Information" icon="building">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <SelectInput
                v-model="form.company_id"
                label="Company"
                @update:name="form.company_name = $event"
                :options="companyOptions"
                :error="allErrors.company_id"
              />

              <SelectInput
                v-model="form.fin_com_id"
                label="Financial Company"
                :options="finCompanyOptions"
                @update:name="form.fin_com_name = $event"
                :error="allErrors.fin_com_id"
              />

              <SelectInput
                v-model="form.division_id"
                label="Division"
                :options="divisionOptions"
                @update:name="form.division_name = $event"
                :error="allErrors.division_id"
              />

              <SelectInput
                v-model="form.department_id"
                label="Department"
                :options="departmentOptions"
                @update:name="form.department_name = $event"
                :error="allErrors.department_id"
              />
              <SelectInput
                v-model="form.designation_id"
                label="Designation"
                :options="designationOptions"
                @update:name="form.designation_name = $event"
                :error="allErrors.designation_id"
              />

              <Autocomplete
                v-model="form.department_head"
                @update:name="form.department_head_name = $event"
                label="Department Head"
                endpoint="/api/persons/search"
                :is_photo="false" 
                required
                :error="allErrors.department_head"
              />
                        
            </div>
          </FormSection>

          <!-- EMPLOYMENT (v-if → safe to destroy) -->
          <FormSection v-if="activeSection === 'employment'" title="Employment Details" icon="briefcase">
            <div class="space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <TextInput
                  v-model="form.employee_id"
                  label="Employee ID"
                  required
                  maxlength="20"
                  :error="allErrors.employee_id"
                />

                <FlatpickrInput
                  v-model="form.joining_date"
                  label="Joining Date"
                  required
                  :error="allErrors.joining_date"
                />

                <FlatpickrInput
                  v-model="form.confirmation_date"
                  label="Confirmation Date"
                  :error="allErrors.confirmation_date"
                />

                <FlatpickrInput
                  v-model="form.probation_end_date"
                  label="Probation End Date"
                  :error="allErrors.probation_end_date"
                />

                <FlatpickrInput
                  v-model="form.effective_date"
                  label="Effective Date"
                  :error="allErrors.effective_date"
                />

                <SelectInput
                  v-model="form.employment_type"
                  label="Employment Type"
                  :options="employmentTypes"
                  required
                  :error="allErrors.employment_type"
                />

                <SelectInput
                  v-model="form.employee_status"
                  label="Employee Status"
                  :options="statusOptions"
                  :error="allErrors.employee_status"
                />

                <TextInput
                  v-model="form.work_location"
                  label="Work Location"
                  maxlength="100"
                  :error="allErrors.work_location"
                />

                <TextInput
                  v-model="form.shift"
                  label="Shift"
                  placeholder="e.g., Day, Night"
                  maxlength="50"
                  :error="allErrors.shift"
                />

                <TextInput
                  v-model="form.official_email"
                  label="Official Email"
                  type="email"
                  maxlength="100"
                  :error="allErrors.official_email"
                />

                <TextInput
                  v-model="form.official_phone"
                  label="Official Phone"
                  maxlength="20"
                  :error="allErrors.official_phone"
                />

                <FlatpickrInput
                  v-model="form.office_in_time"
                  label="Office In Time"
                  mode="time"
                  :error="allErrors.office_in_time"
                />

                <FlatpickrInput
                  v-model="form.office_out_time"
                  label="Office Out Time"
                  mode="time"
                  :error="allErrors.office_out_time"
                />

                <NumberInput
                  v-model.number="form.late_time"
                  label="Late Time (minutes)"
                  :error="allErrors.late_time"
                />
                 <Autocomplete
                v-model="form.reporting_manager_id"
                @update:name="form.reporting_manager_name = $event"
                label="First Reporting Manager"
                endpoint="/api/persons/search"
                :is_photo="false" 
                :error="allErrors.reporting_manager_id"
              />
               
                <Autocomplete
                v-model="form.second_reporting_manager_id"
                @update:name="form.second_reporting_manager_name = $event"
                label="2nd Reporting Manager"
                endpoint="/api/persons/search"
                :is_photo="false" 
                :error="allErrors.second_reporting_manager_id"
              />
              </div>

              <!-- Emergency -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                  <Phone class="w-5 h-5 text-blue-600" /> Emergency Contact
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <TextInput v-model="form.emergency_contact_name" label="Name" maxlength="100" :error="allErrors.emergency_contact_name" />
                  <TextInput v-model="form.emergency_contact_phone" label="Phone" maxlength="20" :error="allErrors.emergency_contact_phone" />
                </div>
              </div>

              <!-- Performance -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                  <Award class="w-5 h-5 text-blue-600" /> Performance & Promotions
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <FlatpickrInput v-model="form.last_appraisal_date" label="Last Appraisal" :error="allErrors.last_appraisal_date" />
                  <FlatpickrInput v-model="form.next_appraisal_date" label="Next Appraisal" :error="allErrors.next_appraisal_date" />
                  <FlatpickrInput v-model="form.last_promotion_date" label="Last Promotion" :error="allErrors.last_promotion_date" />
                  <FlatpickrInput v-model="form.next_promotion_due" label="Next Promotion Due" :error="allErrors.next_promotion_due" />
                </div>
              </div>

              <div class="border-t pt-6">
                <TextareaInput v-model="form.office_time" label="Office Time Notes" rows="3" :error="allErrors.office_time" />
              </div>
            </div>
          </FormSection>

          <!-- SALARY (v-if) -->
          <FormSection v-if="activeSection === 'salary'" title="Salary Information" icon="dollar-sign">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <SelectInput v-model="form.currency" label="Currency" :options="['USD', 'EUR', 'GBP', 'BDT', 'INR']" />
              <NumberInput :model-value="grossSalary" label="Gross Salary" disabled />
              <NumberInput v-model.number="form.basic_salary" label="Basic Salary" step="0.01" />
              <NumberInput v-model.number="form.house_rent_allowance" label="House Rent Allowance" step="0.01" />
              <NumberInput v-model.number="form.medical_allowance" label="Medical Allowance" step="0.01" />
              <NumberInput v-model.number="form.transport_allowance" label="Transport Allowance" step="0.01" />
              <NumberInput v-model.number="form.other_allowances" label="Other Allowances" step="0.01" />
              <NumberInput v-model.number="form.overtime_rate" label="Overtime Rate" step="0.01" />
              <NumberInput v-model.number="form.total_salary" label="Total Salary" disabled />
              <SelectInput v-model="form.is_tax_dedction" label="Tax Deduction" :options="[{ label: 'No', value: '0' }, { label: 'Yes', value: '1' }]" />
              <SelectInput v-model="form.is_salary_stop" label="Salary Stop" :options="[{ label: 'No', value: '0' }, { label: 'Yes', value: '1' }]" />
            </div>
          </FormSection>

          <!-- BANKING (v-if) -->
          <FormSection v-if="activeSection === 'banking'" title="Banking & Tax" icon="credit-card">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <TextInput v-model="form.bank_name" label="Bank Name" maxlength="255" />
              <TextInput v-model="form.bank_account_no" label="Bank Account Number" maxlength="50" />
              <TextInput v-model="form.bank_ifsc_code" label="Bank IFSC Code" maxlength="20" />
              <TextInput v-model="form.pan_number" label="PAN Number" maxlength="20" />
              <SelectInput v-model="form.tax_status" label="Tax Status" :options="['', 'Resident', 'Non-Resident']" />
              <TextInput v-model="form.social_security_no" label="Social Security No" maxlength="50" />
              <TextInput v-model="form.passport_number" label="Passport Number" maxlength="20" />
            </div>
          </FormSection>

          <!-- Navigation -->
          <div class="flex justify-between items-center pt-8 border-t border-gray-200">
            <button
              type="button"
              @click="goToPrevious"
              :disabled="activeSection === 'personal'"
              class="cursor-pointer px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>

            <button
              v-if="activeSection !== 'banking'"
              type="button"
              @click="goToNext"
              class="cursor-pointer px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
            >
              Next
            </button>

            <button
              v-else
              type="submit"
              :disabled="form.processing"
              class=" cursor-pointer px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-semibold hover:from-green-700 hover:to-emerald-700 transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <LoadingSpinner v-if="form.processing" />
              {{ mode === 'create' ? 'Submit' : 'Update' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormSection from '@/Components/FormSection.vue'
import TextInput from '@/Components/TextInput.vue'
import NumberInput from '@/Components/NumberInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextareaInput from '@/Components/TextareaInput.vue'
import PersonAutocomplete from '@/Components/PersonAutocomplete.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import Autocomplete from '@/components/Autocomplete.vue'
import LoadingSpinner from '@/Components/custom/LoadingSpinner.vue'
import { User, Building2, Briefcase, DollarSign, CreditCard, Phone, Award } from 'lucide-vue-next'

const props = defineProps({
  employee: Object,
  mode: { type: String, required: true },
  errors: Object,
  companies:    { type: Array, default: () => [] },
  divisions:    { type: Array, default: () => [] },
  departments:  { type: Array, default: () => [] },
  designations: { type: Array, default: () => [] },
  finCompany: { type: Array, defalut: () =>[]}
})

// Local client-side errors
const clientErrors = ref({})

// Merge server + client errors
const allErrors = computed(() => ({
  ...props.errors,
  ...clientErrors.value,
}))

// Tabs
const sections = [
  { id: 'personal',   label: 'Personal Info',   icon: User },
  { id: 'company',    label: 'Company Info',    icon: Building2 },
  { id: 'employment', label: 'Employment',      icon: Briefcase },
  { id: 'salary',     label: 'Salary',          icon: DollarSign },
  { id: 'banking',    label: 'Banking & Tax',   icon: CreditCard },
]
const toSelectOptions = (items: any[], valueKey: string, labelKey: string) => {
  return [{ value: '', label: '— Select —' }, ...items.map(i => ({
    value: i[valueKey],
    label: i[labelKey],
  }))]
}
const companyOptions    = computed(() => toSelectOptions(props.companies,    'id', 'company_name'))
const divisionOptions   = computed(() => toSelectOptions(props.divisions,   'id', 'division_name'))
const departmentOptions = computed(() => toSelectOptions(props.departments, 'id', 'department_name'))
const designationOptions= computed(() => toSelectOptions(props.designations,'id', 'name'))
const finCompanyOptions= computed(() => toSelectOptions(props.finCompany,'id', 'company_name'))

const activeSection = ref('personal')
console.log(companyOptions);
const goToPrevious = () => {
  const i = sections.findIndex(s => s.id === activeSection.value)
  if (i > 0) activeSection.value = sections[i - 1].id
}

const goToNext = () => {
  const i = sections.findIndex(s => s.id === activeSection.value)
  if (i < sections.length - 1) activeSection.value = sections[i + 1].id
}

const employmentTypes = ['Permanent', 'Contract', 'Intern', 'Probation', 'Freelancer']
const statusOptions = ['Active', 'Inactive', 'Terminated', 'Resigned', 'Retired', 'On Leave']

// Form
const form = useForm({
  person_id: null,
  person_name: '',
  gender: '',
  marital_status: '',
  blood_group: '',
  work_experience: null,
  skills: '',
  company_id: null,
  company_name: '',
  fin_com_id: null,
  fin_com_name: '',
  division_id: null,
  division_name: '',
  department_id: null,
  department_name: '',
  designation_id: null,
  designation_name: '',
  department_head: null,
  department_head_name: '',
  employee_id: '',
  employee_code: '',
  joining_date: '',
  confirmation_date: null,
  probation_end_date: null,
  effective_date: null,
  employment_type: 'Permanent',
  employee_status: 'Active',
  work_location: '',
  shift: '',
  official_email: '',
  official_phone: '',
  office_in_time: null,
  office_out_time: null,
  late_time: 0,
  reporting_manager_id: null,
  reporting_manager_name: '',
  second_reporting_manager_id: null,
  second_reporting_manager_name: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  last_appraisal_date: null,
  next_appraisal_date: null,
  last_promotion_date: null,
  next_promotion_due: null,
  office_time: '',
  currency: 'USD',
  basic_salary: 0,
  house_rent_allowance: 0,
  medical_allowance: 0,
  transport_allowance: 0,
  other_allowances: 0,
  overtime_rate: 0,
  gross_salary: 0,
  total_salary: 0,
  is_tax_dedction: '0',
  is_salary_stop: '0',
  bank_name: '',
  bank_account_no: '',
  bank_ifsc_code: '',
  pan_number: '',
  tax_status: '',
  social_security_no: '',
  passport_number: '',
})

// Fill on edit
if (props.employee) form.fill(props.employee)

// Salary calculation
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

// Validation
const validationRules = {
  personal: [{ field: 'person_id', rule: v => !!v && v > 0 }],
  company: [
    { field: 'department_id', rule: v => !!v && v > 0 },
    { field: 'designation_id', rule: v => !!v && v > 0 },
    { field: 'department_head', rule: v => !!v && v > 0 },
  ],
  employment: [
    { field: 'employee_id', rule: v => !!v && v.trim().length > 0 },
    { field: 'joining_date', rule: v => !!v },
    { field: 'employment_type', rule: v => !!v },
  ],
  salary: [{ field: 'basic_salary', rule: v => v >= 0 }],
}

function validateTab(tabId: string) {
  const rules = validationRules[tabId] || []
  const errors: Record<string, string> = {}
  rules.forEach(({ field, rule }) => {
    if (!rule(form[field])) {
      errors[field] = 'This field is required.'
    }
  })
  return errors
}

function submit() {
  // === 1. Reset client errors ===
  clientErrors.value = {}

  // === 2. Validate only the CURRENT tab ===
  const currentTabErrors = validateTab(activeSection.value)

  if (Object.keys(currentTabErrors).length > 0) {
    clientErrors.value = currentTabErrors
    nextTick(() => {
      const firstField = Object.keys(currentTabErrors)[0]
      const el = document.querySelector(`[name="${firstField}"]`)
      el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    })
    return
  }

  // === 3. Submit ===
  const method = props.mode === 'create' ? 'post' : 'put'
  const url = props.mode === 'create' ? '/employees.store' : `/employees/${props.employee.id}`

  form[method](url, {
    onSuccess: () => {
      if (props.mode === 'create') form.reset()
    },
    onError: () => {
      // form.errors is auto-filled
      const firstServerError = Object.keys(form.errors)[0]
      const tab = Object.entries(validationRules).find(([, rules]) =>
        rules.some(r => r.field === firstServerError)
      )

      if (tab) {
        activeSection.value = tab[0]
        nextTick(() => {
          document.querySelector(`[name="${firstServerError}"]`)?.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          })
        })
      }
    }
  })
}
</script>