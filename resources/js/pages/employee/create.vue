<template>
  <AppLayout
    title="Employee Management"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Employees', href: '/employees' },
      { title: mode === 'create' ? 'Add New Employee' : 'Edit Employee', href: '#' },
    ]"
  >
    <!-- Page gradient background (light → dark) -->
<div class="bg-white dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-t-xl shadow-lg p-6 mb-0">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 text-center">
            {{ mode === 'create' ? 'Employee Registration' : 'Update Employee' }}
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center text-red-500">
            Complete all sections. Fields marked with * are required.
          </p>
        </div>

        <!-- Tabs -->
        <div class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
          <div class="flex overflow-x-auto">
            <button
              v-for="section in sections"
              :key="section.id"
              @click="activeSection = section.id"
              class="cursor-pointer flex items-center gap-2 px-6 py-4 border-b-2 transition-colors whitespace-nowrap"
              :class="[
                activeSection === section.id
                  ? 'border-blue-600 text-blue-600 bg-white dark:bg-gray-800 dark:text-blue-400'
                  : 'border-transparent text-gray-600 dark:text-gray-300 hover:text-gray-900 hover:bg-gray-100 dark:hover:text-gray-100 dark:hover:bg-gray-600'
              ]"
            >
              <component :is="section.icon" class="w-5 h-5" />
              <span class="font-medium">{{ section.label }}</span>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-lg rounded-b-2xl p-8 space-y-10">

          <!-- PERSONAL -->
          <FormSection v-show="activeSection === 'personal'" title="Personal Information" icon="user">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="input-wrapper">
                <PersonAutocomplete
                  v-model="form.person_id"
                  :initial-name="form.person_name"
                  @update:name="form.person_name = $event"
                  label="Person"
                  endpoint="/api/persons/search"
                  required
                  :error="allErrors.person_id"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.gender"
                  label="Gender"
                  :options="['', 'Male', 'Female', 'Other']"
                  :error="allErrors.gender"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.marital_status"
                  label="Marital Status"
                  :options="['', 'Single', 'Married', 'Divorced', 'Widowed']"
                  :error="allErrors.marital_status"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.blood_group"
                  label="Blood Group"
                  :options="['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                  :error="allErrors.blood_group"
                />
              </div>

              <div class="input-wrapper">
                <NumberInput
                  v-model.number="form.work_experience"
                  label="Work Experience (Years)"
                  :error="allErrors.work_experience"
                />
              </div>

              <div class="md:col-span-2 input-wrapper">
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

          <!-- COMPANY -->
          <FormSection v-show="activeSection === 'company'" title="Company Information" icon="building">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="input-wrapper">
                <SelectInput
                  v-model="form.company_id"
                  label="Company"
                  @update:name="form.company_name = $event"
                  :options="companyOptions"
                  :error="allErrors.company_id"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.fin_com_id"
                  label="Financial Company"
                  :options="finCompanyOptions"
                  @update:name="form.fin_com_name = $event"
                  :error="allErrors.fin_com_id"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.division_id"
                  label="Division"
                  :options="divisionOptions"
                  @update:name="form.division_name = $event"
                  :error="allErrors.division_id"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.department_id"
                  label="Department"
                  :options="departmentOptions"
                  @update:name="form.department_name = $event"
                  :error="allErrors.department_id"
                />
              </div>

              <div class="input-wrapper">
                <SelectInput
                  v-model="form.designation_id"
                  label="Designation"
                  :options="designationOptions"
                  @update:name="form.designation_name = $event"
                  :error="allErrors.designation_id"
                />
              </div>

              <div class="input-wrapper">
                <Autocomplete
                  v-model="form.department_head"
                  @update:name="form.department_head_name = $event"
                  :initial-name="form.department_head_name"
                  label="Department Head"
                  endpoint="/api/persons/search"
                  :is_photo="false"
                  required
                  :error="allErrors.department_head"
                />
              </div>
            </div>
          </FormSection>

          <!-- EMPLOYMENT -->
          <FormSection v-if="activeSection === 'employment'" title="Employment Details" icon="briefcase">
            <div class="space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-wrapper">
                  <TextInput
                    v-model="form.employee_id"
                    label="Employee ID"
                    required
                    maxlength="20"
                    :error="allErrors.employee_id"
                  />
                </div>

                <div class="input-wrapper">
                  <FlatpickrInput
                    v-model="form.joining_date"
                    label="Joining Date"
                    required
                    :error="allErrors.joining_date"
                  />
                </div>

                <div class="input-wrapper">
                  <FlatpickrInput
                    v-model="form.confirmation_date"
                    label="Confirmation Date"
                    :error="allErrors.confirmation_date"
                  />
                </div>

                <div class="input-wrapper">
                  <FlatpickrInput
                    v-model="form.probation_end_date"
                    label="Probation End Date"
                    :error="allErrors.probation_end_date"
                  />
                </div>

                <div class="input-wrapper">
                  <FlatpickrInput
                    v-model="form.effective_date"
                    label="Effective Date"
                    :error="allErrors.effective_date"
                  />
                </div>

                <div class="input-wrapper">
                  <SelectInput
                    v-model="form.employment_type"
                    label="Employment Type"
                    :options="employmentTypes"
                    required
                    :error="allErrors.employment_type"
                  />
                </div>

                <div class="input-wrapper">
                  <SelectInput
                    v-model="form.employee_status"
                    label="Employee Status"
                    :options="statusOptions"
                    :error="allErrors.employee_status"
                  />
                </div>

                <div class="input-wrapper">
                  <TextInput
                    v-model="form.work_location"
                    label="Work Location"
                    maxlength="100"
                    :error="allErrors.work_location"
                  />
                </div>

                <div class="input-wrapper">
                  <TextInput
                    v-model="form.shift"
                    label="Shift"
                    placeholder="e.g., Day, Night"
                    maxlength="50"
                    :error="allErrors.shift"
                  />
                </div>

                <div class="input-wrapper">
                  <TextInput
                    v-model="form.official_email"
                    label="Official Email"
                    type="email"
                    maxlength="100"
                    :error="allErrors.official_email"
                  />
                </div>

                <div class="input-wrapper">
                  <TextInput
                    v-model="form.official_phone"
                    label="Official Phone"
                    maxlength="20"
                    :error="allErrors.official_phone"
                  />
                </div>

                <div class="input-wrapper">
                  <FlatpickrInput
                    v-model="form.office_in_time"
                    label="Office In Time"
                    mode="time"
                    :error="allErrors.office_in_time"
                  />
                </div>

                <div class="input-wrapper">
                  <FlatpickrInput
                    v-model="form.office_out_time"
                    label="Office Out Time"
                    mode="time"
                    :error="allErrors.office_out_time"
                  />
                </div>

                <div class="input-wrapper">
                  <NumberInput
                    v-model.number="form.late_time"
                    label="Late Time (minutes)"
                    :error="allErrors.late_time"
                  />
                </div>

                <div class="input-wrapper">
                  <Autocomplete
                    v-model="form.reporting_manager_id"
                    @update:name="form.reporting_manager_name = $event"
                    :initial-name="form.reporting_manager_name"
                    label="First Reporting Manager"
                    endpoint="/api/persons/search"
                    :is_photo="false"
                    :error="allErrors.reporting_manager_id"
                  />
                </div>

                <div class="input-wrapper">
                  <Autocomplete
                    v-model="form.second_reporting_manager_id"
                    @update:name="form.second_reporting_manager_name = $event"
                    :initial-name="form.second_reporting_manager_name"
                    label="2nd Reporting Manager"
                    endpoint="/api/persons/search"
                    :is_photo="false"
                    :error="allErrors.second_reporting_manager_id"
                  />
                </div>
              </div>

              <!-- Emergency -->
              <div class="border-t dark:border-gray-600 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                  <Phone class="w-5 h-5 text-blue-600 dark:text-blue-400" /> Emergency Contact
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="input-wrapper">
                    <TextInput v-model="form.emergency_contact_name" label="Name" maxlength="100" :error="allErrors.emergency_contact_name" />
                  </div>
                  <div class="input-wrapper">
                    <TextInput v-model="form.emergency_contact_phone" label="Phone" maxlength="20" :error="allErrors.emergency_contact_phone" />
                  </div>
                </div>
              </div>

              <!-- Performance -->
              <div class="border-t dark:border-gray-600 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                  <Award class="w-5 h-5 text-blue-600 dark:text-blue-400" /> Performance & Promotions
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="input-wrapper"><FlatpickrInput v-model="form.last_appraisal_date" label="Last Appraisal" :error="allErrors.last_appraisal_date" /></div>
                  <div class="input-wrapper"><FlatpickrInput v-model="form.next_appraisal_date" label="Next Appraisal" :error="allErrors.next_appraisal_date" /></div>
                  <div class="input-wrapper"><FlatpickrInput v-model="form.last_promotion_date" label="Last Promotion" :error="allErrors.last_promotion_date" /></div>
                  <div class="input-wrapper"><FlatpickrInput v-model="form.next_promotion_due" label="Next Promotion Due" :error="allErrors.next_promotion_due" /></div>
                </div>
              </div>

              <div class="border-t dark:border-gray-600 pt-6 input-wrapper">
                <TextareaInput v-model="form.office_time" label="Office Time Notes" rows="3" :error="allErrors.office_time" />
              </div>
            </div>
          </FormSection>

          <!-- SALARY -->
          <FormSection v-if="activeSection === 'salary'" title="Salary Information" icon="dollar-sign">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="input-wrapper"><SelectInput v-model="form.currency" label="Currency" :options="['USD', 'EUR', 'GBP', 'BDT', 'INR']" /></div>

              <div class="input-wrapper">
                <NumberInput
                  v-model.number="form.gross_salary"
                  label="Gross Salary"
                  step="0.01"
                  placeholder="Enter gross salary"
                  :error="allErrors.gross_salary"
                />
              </div>

              <div class="input-wrapper"><NumberInput v-model.number="form.basic_salary" label="Basic Salary" step="0.01" /></div>
              <div class="input-wrapper"><NumberInput v-model.number="form.house_rent_allowance" label="House Rent Allowance" step="0.01" /></div>
              <div class="input-wrapper"><NumberInput v-model.number="form.medical_allowance" label="Medical Allowance" step="0.01" /></div>
              <div class="input-wrapper"><NumberInput v-model.number="form.transport_allowance" label="Transport Allowance" step="0.01" /></div>
              <div class="input-wrapper"><NumberInput v-model.number="form.other_allowances" label="Other Allowances" step="0.01" /></div>
              <div class="input-wrapper"><NumberInput v-model.number="form.overtime_rate" label="Overtime Rate" step="0.01" /></div>
              <div class="input-wrapper"><NumberInput v-model.number="form.total_salary" label="Total Salary" disabled /></div>

              <div class="input-wrapper">
                <SelectInput v-model="form.is_tax_dedction" label="Tax Deduction"
                  :options="[{ label: 'No', value: '0' }, { label: 'Yes', value: '1' }]" />
              </div>
              <div class="input-wrapper">
                <SelectInput v-model="form.is_salary_stop" label="Salary Stop"
                  :options="[{ label: 'No', value: '0' }, { label: 'Yes', value: '1' }]" />
              </div>
            </div>
          </FormSection>

          <!-- BANKING -->
          <FormSection v-if="activeSection === 'banking'" title="Banking & Tax" icon="credit-card">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="input-wrapper"><TextInput v-model="form.bank_name" label="Bank Name" maxlength="255" /></div>
              <div class="input-wrapper"><TextInput v-model="form.bank_account_no" label="Bank Account Number" maxlength="50" /></div>
              <div class="input-wrapper"><TextInput v-model="form.bank_ifsc_code" label="Bank IFSC Code" maxlength="20" /></div>
              <div class="input-wrapper"><TextInput v-model="form.pan_number" label="PAN Number" maxlength="20" /></div>
              <div class="input-wrapper"><SelectInput v-model="form.tax_status" label="Tax Status" :options="['', 'Resident', 'Non-Resident']" /></div>
              <div class="input-wrapper"><TextInput v-model="form.social_security_no" label="Social Security No" maxlength="50" /></div>
              <div class="input-wrapper"><TextInput v-model="form.passport_number" label="Passport Number" maxlength="20" /></div>
            </div>
          </FormSection>

          <!-- Navigation -->
          <div class="flex justify-between items-center pt-8 border-t border-gray-200 dark:border-gray-600">
            <button
              type="button"
              @click="goToPrevious"
              :disabled="activeSection === 'personal'"
              class="cursor-pointer px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
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
              class="cursor-pointer px-8 py-3 bg-blue-600 hover:bg-blue-700  text-white rounded-lg font-semibold hover:from-green-700 hover:to-emerald-700 transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
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
import AppLayout from '@/layouts/AppLayout.vue'
import FormSection from '@/components/FormSection.vue'
import TextInput from '@/components/TextInput.vue'
import NumberInput from '@/components/NumberInput.vue'
import SelectInput from '@/components/SelectInput.vue'
import TextareaInput from '@/components/TextareaInput.vue'
import PersonAutocomplete from '@/components/PersonAutocomplete.vue'
import FlatpickrInput from '@/components/FlatpickrInput.vue'
import Autocomplete from '@/components/Autocomplete.vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'
import { User, Building2, Briefcase, DollarSign, CreditCard, Phone, Award } from 'lucide-vue-next'

const props = defineProps({
  employee: Object,
  mode: { type: String, required: true },
  errors: Object,
  companies:    { type: Array, default: () => [] },
  divisions:    { type: Array, default: () => [] },
  departments:  { type: Array, default: () => [] },
  designations: { type: Array, default: () => [] },
  finCompany:   { type: Array, default: () => [] }
})

// ---------------------------------------------------------------------
// 1. Errors
// ---------------------------------------------------------------------
const clientErrors = ref({})
const allErrors = computed(() => ({
  ...props.errors,
  ...clientErrors.value,
}))

// ---------------------------------------------------------------------
// 2. Tabs
// ---------------------------------------------------------------------
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
const companyOptions     = computed(() => toSelectOptions(props.companies,    'id', 'company_name'))
const divisionOptions    = computed(() => toSelectOptions(props.divisions,   'id', 'division_name'))
const departmentOptions  = computed(() => toSelectOptions(props.departments, 'id', 'department_name'))
const designationOptions = computed(() => toSelectOptions(props.designations,'id', 'name'))
const finCompanyOptions  = computed(() => toSelectOptions(props.finCompany,  'id', 'company_name'))

const activeSection = ref('personal')

const goToPrevious = () => {
  const i = sections.findIndex(s => s.id === activeSection.value)
  if (i > 0) activeSection.value = sections[i - 1].id
}
const goToNext = () => {
  const i = sections.findIndex(s => s.id === activeSection.value)
  if (i < sections.length - 1) activeSection.value = sections[i + 1].id
}

// ---------------------------------------------------------------------
// 3. Static options
// ---------------------------------------------------------------------
const employmentTypes = ['Permanent', 'Contract', 'Intern', 'Probation', 'Freelancer']
const statusOptions   = ['Active', 'Inactive', 'Terminated', 'Resigned', 'Retired', 'On Leave']

// ---------------------------------------------------------------------
// 4. Form
// ---------------------------------------------------------------------
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
if (props.employee) {
  Object.assign(form, {
    ...props.employee,
    department_head: props.employee.department_head || null,
    department_head_name: props.employee.department_head_name || '',
  })

  const findName = (list: any[], id: any, key: string) =>
    list.find(i => i.id == id)?.[key] || ''

  form.company_name     = findName(props.companies,    props.employee.company_id,    'company_name')
  form.fin_com_name     = findName(props.finCompany,  props.employee.fin_com_id,    'company_name')
  form.division_name    = findName(props.divisions,   props.employee.division_id,   'division_name')
  form.department_name  = findName(props.departments, props.employee.department_id, 'department_name')
  form.designation_name = findName(props.designations,props.employee.designation_id,'name')
}

// ---------------------------------------------------------------------
// 5. Salary helpers
// ---------------------------------------------------------------------
const calculateFromGross = () => {
  const gross = Number(form.gross_salary) || 0
  if (gross < 0) return

  form.basic_salary        = Number((gross * 0.50).toFixed(2))
  form.house_rent_allowance= Number((gross * 0.30).toFixed(2))
  form.medical_allowance   = Number((gross * 0.10).toFixed(2))
  form.transport_allowance = Number((gross * 0.05).toFixed(2))
  form.other_allowances    = Number((gross * 0.05).toFixed(2))

  updateTotalSalary()
}
const updateTotalSalary = () => {
  const gross    = Number(form.gross_salary) || 0
  const overtime = Number(form.overtime_rate) || 0
  form.total_salary = Number((gross + overtime).toFixed(2))
}
watch(() => form.gross_salary, calculateFromGross)

// ---------------------------------------------------------------------
// 6. Validation
// ---------------------------------------------------------------------
const validationRules = {
  personal: [{ field: 'person_id', rule: v => !!v && v > 0 }],
  company: [
    { field: 'department_id',   rule: v => !!v && v > 0 },
    { field: 'designation_id',  rule: v => !!v && v > 0 },
    { field: 'department_head', rule: v => !!v && v > 0 },
  ],
  employment: [
    { field: 'employee_id',     rule: v => !!v && v.trim().length > 0 },
    { field: 'joining_date',    rule: v => !!v },
    { field: 'employment_type', rule: v => !!v },
    { field: 'office_in_time',  rule: v => !!v },
    { field: 'office_out_time', rule: v => !!v },
  ],
  salary: [{ field: 'basic_salary', rule: v => v >= 0 }],
}

function validateTab(tabId: string) {
  const rules = validationRules[tabId] || []
  const errors: Record<string, string> = {}
  rules.forEach(({ field, rule }) => {
    if (!rule(form[field])) errors[field] = 'This field is required.'
  })
  return errors
}

// ---------------------------------------------------------------------
// 7. Submit
// ---------------------------------------------------------------------
function submit() {
  clientErrors.value = {}
  const currentTabErrors = validateTab(activeSection.value)

  if (Object.keys(currentTabErrors).length) {
    clientErrors.value = currentTabErrors
    nextTick(() => {
      const first = Object.keys(currentTabErrors)[0]
      document.querySelector(`[name="${first}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    })
    return
  }

  const method = props.mode === 'create' ? 'post' : 'put'
  const url    = props.mode === 'create' ? '/employees.store' : `/employees/${props.employee.id}`

  form[method](url, {
    onSuccess: () => { if (props.mode === 'create') form.reset() },
    onError: () => {
      const first = Object.keys(form.errors)[0]
      const tab = Object.entries(validationRules).find(([, rules]) =>
        rules.some(r => r.field === first)
      )
      if (tab) {
        activeSection.value = tab[0]
        nextTick(() => document.querySelector(`[name="${first}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' }))
      }
    }
  })
}
</script>
