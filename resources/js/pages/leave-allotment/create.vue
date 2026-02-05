<template>
  <AppLayout
    title="Leave Allotment"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Leave Allotments', href: '/leave-allotments' },
      { title: mode === 'create' ?'Add Leave Allotment' : 'Edit Leave Allotment', href: '#' },
    ]"
  >
<!--      <div class="max-w-4xl mx-auto p-2 sm:p-4">-->
<!--          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-8 border border-sidebar-border/70">-->

          <div class=" py-8 px-4 sm:px-6 lg:px-8">
              <div class="max-w-4xl mx-auto p-2 sm:p-4">
        <!-- Header -->
                  <div class="bg-white dark:bg-gray-800 rounded-t-xl shadow-lg  pt-6">
                      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center">
                          {{ mode === 'create' ? 'Add Leave Allotment' : 'Edit Leave Allotment' }}
                      </h1>
                      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
                          Complete all fields. Fields marked with * are required.
                      </p>
                  </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-lg rounded-b-xl p-8 space-y-6">

          <!-- Fiscal Year -->
          <div class="input-wrapper">
            <SelectInput
              v-model="form.fiscal_year"
              label="Fiscal Year"
              :options="fiscalYears.map(y => ({ label: y, value: y }))"
              required
              :error="errors.fiscal_year"
            />
          </div>

          <!-- Employee Scope -->
          <div class="input-wrapper">
            <SelectInput
              v-model="form.employee_scope"
              label="Employee Scope"
              :options="[
                { label: 'Individual', value: 'individual' },
                { label: 'All Employees', value: 'all' }
              ]"
              required
              :error="errors.employee_scope"
            />
          </div>

          <!-- Employee Selection (Only if Individual) -->
          <div v-if="form.employee_scope === 'individual'" class="input-wrapper">
            <SelectInput
              v-model="form.employee_id"
              label="Select Employee"
              :options="employees.map(emp => ({ label: emp.name, value: emp.id }))"
              required
              :error="errors.employee_id"
            />
          </div>

          <!-- Remarks -->
          <div class="input-wrapper">
            <TextareaInput
              v-model="form.remarks"
              label="Remarks"
              rows="3"
              placeholder="Optional remarks"
            />
          </div>

          <!-- Submit -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="cursor-pointer px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
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
import { reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SelectInput from '@/components/SelectInput.vue'
import TextareaInput from '@/components/TextareaInput.vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'

const props = defineProps({
  mode: { type: String, required: true },
  employees: { type: Array, default: () => [] },
  errors: Object
})

// Options
const currentYear = new Date().getFullYear()
const fiscalYears = Array.from({ length: 3 }, (_, i) => {
  const start = currentYear + i
  const end = start + 1
  return `${start}`
})

// Form
const form = useForm({
  fiscal_year: '',
  employee_scope: 'all',
  employee_id: null,
  remarks: ''
})

// Reactive errors
const errors = reactive({ ...props.errors })

// Submit
function submit() {
  Object.assign(errors, {}) // clear errors

  // Basic validation
  if (!form.fiscal_year) errors.fiscal_year = 'Fiscal Year is required'
  if (!form.employee_scope) errors.employee_scope = 'Select employee scope'
  if (form.employee_scope === 'individual' && !form.employee_id) errors.employee_id = 'Select an employee'

  if (Object.keys(errors).length) return

  const method = props.mode === 'create' ? 'post' : 'put'
  const url    = props.mode === 'create' ? '/leave-allotments' : `/leave-allotments/${form.id}`

  form[method](url, {
    onSuccess: () => { if (props.mode === 'create') form.reset() }
  })
}
</script>
