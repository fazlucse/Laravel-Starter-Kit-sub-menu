<template>
  <AppLayout
    title="Employee"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Employees', href: '/employees' },
      { title: mode === 'create' ? 'Create' : 'Edit', href: '#' },
    ]"
  >
    <div class="flex flex-col h-[calc(100vh-5rem)] overflow-auto p-6">
      <form @submit.prevent="submit" class="space-y-8 max-w-5xl mx-auto">
        <!-- ==== Person Info ==== -->
        <Section title="Person Info">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <TextInput v-model="form.person_name" label="Full Name" required />
            <TextInput v-model="form.employee_code" label="Employee Code" required />
            <TextInput v-model="form.employee_id" label="Employee ID" required />
          </div>
        </Section>

        <!-- ==== Company Info ==== -->
        <Section title="Company Info">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <TextInput v-model="form.company_name" label="Company Name" />
            <TextInput v-model="form.fin_com_name" label="Financial Company Name" />
            <TextInput v-model="form.fin_com_id" label="Financial Company ID" type="number" />
          </div>
        </Section>

        <!-- ==== Division & Department ==== -->
        <Section title="Division & Department">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TextInput v-model="form.division_name" label="Division" />
            <TextInput v-model="form.department_name" label="Department" required />
          </div>
        </Section>

        <!-- ==== Designation ==== -->
        <Section title="Designation">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TextInput v-model="form.designation_name" label="Designation" required />
          </div>
        </Section>

        <!-- ==== Joining & Employment ==== -->
        <Section title="Joining & Employment">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <DateInput v-model="form.joining_date" label="Joining Date" required />
            <DateInput v-model="form.confirmation_date" label="Confirmation Date" />
            <DateInput v-model="form.probation_end_date" label="Probation End Date" />
            <DateInput v-model="form.effective_date" label="Effective Date" />
            <SelectInput
              v-model="form.employment_type"
              label="Employment Type"
              :options="['Permanent','Contract','Intern','Probation','Freelancer']"
              required
            />
            <TextInput v-model="form.work_location" label="Work Location" />
            <TextInput v-model="form.shift" label="Shift" />
            <TextInput v-model="form.official_email" label="Official Email" type="email" />
            <TextInput v-model="form.official_phone" label="Official Phone" />
          </div>
        </Section>

        <!-- ==== Office Timings ==== -->
        <Section title="Office Timings">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <TimeInput v-model="form.office_in_time" label="In Time" />
            <TimeInput v-model="form.office_out_time" label="Out Time" />
            <NumberInput v-model.number="form.late_time" label="Late Time (min)" />
            <SelectInput
              v-model="form.employee_status"
              label="Status"
              :options="['Active','Inactive','Terminated','Resigned','Retired','On Leave']"
            />
          </div>
        </Section>

        <!-- ==== Salary Components ==== -->
        <Section title="Salary Components">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <NumberInput v-model.number="form.basic_salary" label="Basic Salary" required />
            <NumberInput v-model.number="form.house_rent_allowance" label="HRA" />
            <NumberInput v-model.number="form.medical_allowance" label="Medical" />
            <NumberInput v-model.number="form.transport_allowance" label="Transport" />
            <NumberInput v-model.number="form.other_allowances" label="Other Allowances" />
            <NumberInput v-model.number="form.overtime_rate" label="Overtime Rate" />
            <div class="col-span-3">
              <NumberInput :model-value="grossSalary" label="Gross Salary (auto)" disabled />
            </div>
            <NumberInput v-model.number="form.total_salary" label="Total Salary" disabled />
            <TextInput v-model="form.currency" label="Currency" />
          </div>
        </Section>

        <!-- ==== Banking & Tax ==== -->
        <Section title="Banking & Tax">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <TextInput v-model="form.bank_name" label="Bank Name" />
            <TextInput v-model="form.bank_account_no" label="Account No" />
            <TextInput v-model="form.bank_ifsc_code" label="IFSC Code" />
            <TextInput v-model="form.pan_number" label="PAN Number" />
            <SelectInput
              v-model="form.tax_status"
              label="Tax Status"
              :options="['Resident','Non-Resident']"
            />
            <TextInput v-model="form.social_security_no" label="SSN / NID" />
            <TextInput v-model="form.passport_number" label="Passport No" />
            <CheckboxInput v-model="form.is_tax_dedction" label="Tax Deduction?" />
            <CheckboxInput v-model="form.is_salary_stop" label="Salary Stopped?" />
          </div>
        </Section>

        <!-- ==== Personal Info ==== -->
        <Section title="Personal Info">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <TextInput v-model="form.emergency_contact_name" label="Emergency Contact Name" />
            <TextInput v-model="form.emergency_contact_phone" label="Emergency Phone" />
            <SelectInput
              v-model="form.marital_status"
              label="Marital Status"
              :options="['Single','Married','Divorced','Widowed']"
            />
            <SelectInput
              v-model="form.gender"
              label="Gender"
              :options="['Male','Female','Other']"
            />
            <TextInput v-model="form.blood_group" label="Blood Group" />
            <NumberInput v-model.number="form.work_experience" label="Work Experience (yrs)" />
            <TextareaInput v-model="form.skills" label="Skills" rows="3" />
          </div>
        </Section>

        <!-- ==== Reporting Managers ==== -->
        <Section title="Reporting Structure">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TextInput v-model="form.reporting_manager_name" label="Reporting Manager" />
            <TextInput v-model="form.second_reporting_manager_name" label="2nd Manager" />
            <TextInput v-model="form.deparment_head_name" label="Department Head" />
          </div>
        </Section>

        <!-- ==== Performance & Promotions ==== -->
        <Section title="Performance & Promotions">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <DateInput v-model="form.last_appraisal_date" label="Last Appraisal" />
            <DateInput v-model="form.next_appraisal_date" label="Next Appraisal" />
            <DateInput v-model="form.last_promotion_date" label="Last Promotion" />
            <DateInput v-model="form.next_promotion_due" label="Next Promotion Due" />
          </div>
        </Section>

        <!-- ==== Admin Fields ==== -->
        <Section title="Admin">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TextareaInput v-model="form.office_time" label="Office Time (Notes)" rows="2" />
          </div>
        </Section>

        <!-- ==== Submit Buttons ==== -->
        <div class="flex justify-end space-x-3 pt-6">
          <Link href="/employees" class="btn btn-secondary">Cancel</Link>
          <button type="submit" class="btn btn-primary">
            {{ mode === 'create' ? 'Create Employee' : 'Update Employee' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue'
import TextInput from '@/Components/TextInput.vue';
import NumberInput from '@/Components/NumberInput.vue';
import DateInput from '@/Components/DateInput.vue';
import TimeInput from '@/Components/TimeInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CheckboxInput from '@/Components/CheckboxInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import Section from '@/Components/Section.vue';
defineProps({
  employee: Object,
  mode: { type: String, required: true }, // 'create' | 'edit'
});

const form = useForm(
  reactive({
    // Person
    person_id: null,
    person_name: '',
    employee_code: '',
    employee_id: '',

    // Company
    company_id: null,
    company_name: '',
    fin_com_id: 0,
    fin_com_name: '',

    // Division
    division_id: null,
    division_name: '',

    // Department
    department_id: null,
    department_name: '',

    // Designation
    designation_id: null,
    designation_name: '',

    // Joining
    joining_date: '',
    confirmation_date: null,
    probation_end_date: null,
    effective_date: null,
    employment_type: 'Permanent',
    work_location: '',
    shift: '',
    official_email: '',
    official_phone: '',

    // Office
    office_in_time: null,
    office_out_time: null,
    late_time: 0,
    employee_status: 'Active',

    // Salary
    gross_salary: 0,
    basic_salary: 0,
    house_rent_allowance: 0,
    medical_allowance: 0,
    transport_allowance: 0,
    other_allowances: 0,
    overtime_rate: 0,
    total_salary: 0,
    currency: 'USD',

    // Banking & Tax
    bank_name: '',
    bank_account_no: '',
    bank_ifsc_code: '',
    pan_number: '',
    tax_status: null,
    social_security_no: '',
    passport_number: '',
    is_tax_dedction: 0,
    is_salary_stop: 0,

    // Personal
    emergency_contact_name: '',
    emergency_contact_phone: '',
    marital_status: null,
    gender: null,
    blood_group: '',
    work_experience: null,
    skills: '',

    // Reporting
    reporting_manager_id: null,
    reporting_manager_name: '',
    second_reporting_manager_id: null,
    second_reporting_manager_name: '',
    deparment_head: null,
    deparment_head_name: '',

    // Performance
    last_appraisal_date: null,
    next_appraisal_date: null,
    last_promotion_date: null,
    next_promotion_due: null,

    // Admin
    office_time: '',
    created_by: null,
    updated_by: null,
  })
);

// Fill form on edit
if (props.employee) {
  form.fill(props.employee);
}

/* ========== Auto Calculate Gross Salary ========== */
const grossSalary = computed(() => {
  return (
    Number(form.basic_salary || 0) +
    Number(form.house_rent_allowance || 0) +
    Number(form.medical_allowance || 0) +
    Number(form.transport_allowance || 0) +
    Number(form.other_allowances || 0)
  );
});

watch(
  () => [
    form.basic_salary,
    form.house_rent_allowance,
    form.medical_allowance,
    form.transport_allowance,
    form.other_allowances,
  ],
  () => {
    form.gross_salary = grossSalary.value;
    form.total_salary = grossSalary.value + Number(form.overtime_rate || 0);
  },
  { immediate: true }
);

/* ========== Submit ========== */
function submit() {
  const method = props.mode === 'create' ? 'post' : 'put';
  const url =
    props.mode === 'create'
      ? route('employees.store')
      : route('employees.update', props.employee.id);

  form[method](url, {
    onSuccess: () => {
      if (props.mode === 'create') form.reset();
    },
  });
}
</script>
