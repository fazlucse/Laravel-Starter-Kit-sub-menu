<template>
  <AppLayout
    title="Employee Management"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Employees', href: '/employees' },
      { title: mode === 'create' ? 'Add New Employee' : 'Edit Employee', href: '#' },
    ]"
  >
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 text-center">
          <h1 class="text-3xl font-bold text-gray-900">
            {{ mode === 'create' ? 'Create New Employee' : 'Update Employee' }}
          </h1>
          <p class="mt-2 text-sm text-gray-600">
            Fill in the details below. Fields marked with * are required.
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-10">
          <!-- Person Info -->
          <FormSection title="Person Info" icon="user">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <TextInput v-model="form.person_name" label="Full Name" required placeholder="John Doe" />
              <TextInput v-model="form.employee_code" label="Employee Code" required placeholder="EMP-001" />
              <TextInput v-model="form.employee_id" label="Employee ID" required placeholder="E12345" />
            </div>
          </FormSection>

          <!-- Company -->
          <FormSection title="Company Details" icon="building">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <TextInput v-model="form.company_name" label="Company Name" placeholder="Acme Corp" />
              <TextInput v-model="form.fin_com_name" label="Financial Company" placeholder="Finance Ltd" />
              <NumberInput v-model.number="form.fin_com_id" label="Fin Company ID" placeholder="1001" />
            </div>
          </FormSection>

          <!-- Division & Department -->
          <FormSection title="Division & Department" icon="sitemap">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <TextInput v-model="form.division_name" label="Division" placeholder="Technology" />
              <TextInput v-model="form.department_name" label="Department" required placeholder="Engineering" />
            </div>
          </FormSection>

          <!-- Designation -->
          <FormSection title="Designation" icon="briefcase">
            <TextInput v-model="form.designation_name" label="Job Title" required placeholder="Senior Developer" />
          </FormSection>

          <!-- Joining & Employment -->
          <FormSection title="Joining & Employment" icon="calendar-check">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <DateInput v-model="form.joining_date" label="Joining Date" required />
              <DateInput v-model="form.confirmation_date" label="Confirmation Date" />
              <DateInput v-model="form.probation_end_date" label="Probation End" />
              <DateInput v-model="form.effective_date" label="Effective Date" />
              <SelectInput v-model="form.employment_type" label="Employment Type" :options="employmentTypes" required />
              <TextInput v-model="form.work_location" label="Work Location" placeholder="Head Office" />
              <TextInput v-model="form.shift" label="Shift" placeholder="Day" />
              <TextInput v-model="form.official_email" label="Official Email" type="email" placeholder="john@company.com" />
              <TextInput v-model="form.official_phone" label="Official Phone" placeholder="+880 1xxx xxx xxx" />
            </div>
          </FormSection>

          <!-- Office Timings -->
          <FormSection title="Office Timings" icon="clock">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <TimeInput v-model="form.office_in_time" label="In Time" />
              <TimeInput v-model="form.office_out_time" label="Out Time" />
              <NumberInput v-model.number="form.late_time" label="Late (min)" placeholder="15" />
              <SelectInput v-model="form.employee_status" label="Status" :options="statusOptions" />
            </div>
          </FormSection>

          <!-- Salary Components -->
          <FormSection title="Salary Components" icon="dollar-sign">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <NumberInput v-model.number="form.basic_salary" label="Basic Salary" required placeholder="50000" />
              <NumberInput v-model.number="form.house_rent_allowance" label="HRA" placeholder="15000" />
              <NumberInput v-model.number="form.medical_allowance" label="Medical" placeholder="5000" />
              <NumberInput v-model.number="form.transport_allowance" label="Transport" placeholder="3000" />
              <NumberInput v-model.number="form.other_allowances" label="Other Allowances" placeholder="2000" />
              <NumberInput v-model.number="form.overtime_rate" label="Overtime Rate" placeholder="500" />
              <div class="md:col-span-2">
                <NumberInput :model-value="grossSalary" label="Gross Salary" disabled />
              </div>
              <NumberInput :model-value="form.total_salary" label="Total Salary" disabled />
              <TextInput v-model="form.currency" label="Currency" placeholder="BDT" />
            </div>
          </FormSection>

          <!-- Banking & Tax -->
          <FormSection title="Banking & Tax" icon="credit-card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <TextInput v-model="form.bank_name" label="Bank Name" placeholder="City Bank" />
              <TextInput v-model="form.bank_account_no" label="Account Number" placeholder="1234567890" />
              <TextInput v-model="form.bank_ifsc_code" label="IFSC / Routing" placeholder="CTBK0001" />
              <TextInput v-model="form.pan_number" label="PAN / Tax ID" placeholder="ABCDE1234F" />
              <SelectInput v-model="form.tax_status" label="Tax Status" :options="['Resident','Non-Resident']" />
              <TextInput v-model="form.social_security_no" label="SSN / NID" placeholder="1234567890123" />
              <TextInput v-model="form.passport_number" label="Passport Number" placeholder="A1234567" />
              <CheckboxInput v-model="form.is_tax_dedction" label="Tax Deduction Applicable" />
              <CheckboxInput v-model="form.is_salary_stop" label="Salary Stopped" />
            </div>
          </FormSection>

          <!-- Personal Info -->
          <FormSection title="Personal Information" icon="id-card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <TextInput v-model="form.emergency_contact_name" label="Emergency Contact" placeholder="Jane Doe" />
              <TextInput v-model="form.emergency_contact_phone" label="Emergency Phone" placeholder="+880 1xxx xxx xxx" />
              <SelectInput v-model="form.marital_status" label="Marital Status" :options="maritalOptions" />
              <SelectInput v-model="form.gender" label="Gender" :options="['Male','Female','Other']" />
              <TextInput v-model="form.blood_group" label="Blood Group" placeholder="O+" />
              <NumberInput v-model.number="form.work_experience" label="Experience (Years)" placeholder="5" />
              <TextareaInput v-model="form.skills" label="Skills" rows="4" placeholder="Vue.js, Laravel, Tailwind..." />
            </div>
          </FormSection>

          <!-- Reporting Structure -->
          <FormSection title="Reporting Structure" icon="users">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <TextInput v-model="form.reporting_manager_name" label="Reporting Manager" placeholder="Mr. Ahmed" />
              <TextInput v-model="form.second_reporting_manager_name" label="2nd Manager" placeholder="Ms. Khan" />
              <TextInput v-model="form.deparment_head_name" label="Department Head" placeholder="Dr. Rahman" />
            </div>
          </FormSection>

          <!-- Performance & Promotions -->
          <FormSection title="Performance & Promotions" icon="trending-up">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <DateInput v-model="form.last_appraisal_date" label="Last Appraisal" />
              <DateInput v-model="form.next_appraisal_date" label="Next Appraisal" />
              <DateInput v-model="form.last_promotion_date" label="Last Promotion" />
              <DateInput v-model="form.next_promotion_due" label="Next Promotion Due" />
            </div>
          </FormSection>

          <!-- Admin Notes -->
          <FormSection title="Admin Notes" icon="sticky-note">
            <TextareaInput v-model="form.office_time" label="Office Time / Notes" rows="3" placeholder="9:00 AM - 5:00 PM..." />
          </FormSection>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-4 pt-8 pb-6 border-t border-gray-200 bg-white rounded-b-xl shadow-sm px-6">
            <Link
              href="/employees"
              class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition shadow-sm"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg font-medium hover:from-indigo-700 hover:to-indigo-800 transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <span v-if="form.processing" class="animate-spin">Loading...</span>
              {{ mode === 'create' ? 'Create Employee' : 'Update Employee' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormSection from '@/Components/FormSection.vue'
import TextInput from '@/Components/TextInput.vue'
import NumberInput from '@/Components/NumberInput.vue'
import DateInput from '@/Components/DateInput.vue'
import TimeInput from '@/Components/TimeInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import CheckboxInput from '@/Components/CheckboxInput.vue'
import TextareaInput from '@/Components/TextareaInput.vue'

// Props
const { employee, mode } = defineProps({
  employee: Object,
  mode: { type: String, required: true },
})

// Options
const employmentTypes = ['Permanent', 'Contract', 'Intern', 'Probation', 'Freelancer']
const statusOptions = ['Active', 'Inactive', 'Terminated', 'Resigned', 'Retired', 'On Leave']
const maritalOptions = ['Single', 'Married', 'Divorced', 'Widowed']

// Form
const form = useForm(
  reactive({
    person_id: null,
    person_name: '',
    employee_code: '',
    employee_id: '',
    company_id: null,
    company_name: '',
    fin_com_id: 0,
    fin_com_name: '',
    division_id: null,
    division_name: '',
    department_id: null,
    department_name: '',
    designation_id: null,
    designation_name: '',
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
    currency: 'BDT',
    bank_name: '',
    bank_account_no: '',
    bank_ifsc_code: '',
    pan_number: '',
    tax_status: null,
    social_security_no: '',
    passport_number: '',
    is_tax_dedction: 0,
    is_salary_stop: 0,
    emergency_contact_name: '',
    emergency_contact_phone: '',
    marital_status: null,
    gender: null,
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
    created_by: null,
    updated_by: null,
  })
)

// Fill on edit
if (employee) form.fill(employee)

// Auto-calculate
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

// Submit
function submit() {
  const method = mode === 'create' ? 'post' : 'put'
  const url = mode === 'create' ? '/employees' : `/employees/${employee.id}`

  form[method](url, {
    onSuccess: () => {
      if (mode === 'create') form.reset()
    },
  })
}
</script>