<template>
  <AppLayout
    title="Employee Details"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Employees', href: '/employees' },
      { title: 'Employee Details', href: '#' }
    ]"
  >
  <div class="w-full max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class=" py-6 px-2 sm:px-6 lg:px-5">


        <!-- PRINTABLE AREA -->
        <div  class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">

          <!-- Top Buttons - Hidden on Print -->
          <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex flex-wrap gap-4 items-center">
          <div class="flex items-center gap-4">
          <!-- Back Button with Icon -->
          <button @click="goBack" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 btn btn-outline hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span>Back</span>
          </button>

          <!-- Title -->
          <h4 class="text-xl font-semibold text-gray-800 dark:text-white">
            Employee Details
          </h4>
        </div>
            <!-- <button @click="printOnlyThis" class="text-sm font-bold text-blue-600 hover:underline">
              Print
            </button> -->
        <div class="flex items-center gap-1 ml-auto">
             <BaseActionButton
    label="Print"
    :icon="Printer"
    iconPosition="left"
    :isProcessing="isPrinting"
    @click="handlePrint"
  />

               <BaseActionButton
                label="PDF"
                :icon="FileDown"
                iconPosition="left"
                :isProcessing="isPdfing"
                @click="handlePDF"
              />

              <!-- <BaseActionButton
  label="Print"
  :icon="CornerUpLeft"
  iconPosition="left"
  :isProcessing="isPrinting"
  @click="handlePrint"
/> -->

          <!-- <button @click="print" :disabled="isProcessing"
            class=" cursor-pointer inline-flex items-center gap-2 px-4 py-2 btn btn-outline hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Print</span>
          </button> -->

          <!-- PDF Button -->
          <!-- <button   @click="exportToPDF('print_content', 'Employee_Details')" :disabled="isProcessing"
            class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 btn btn-primary hover:bg-red-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>PDF</span>
          </button> -->

          <!-- Excel Button -->
          <!-- <button @click="exportToExcel(tableDataForExcel)" :disabled="isProcessing"
            class=" cursor-pointer inline-flex items-center gap-2 px-4 py-2 btn btn-success hover:bg-green-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            <span>Excel</span>
          </button> -->
        </div>


          </div>

          <!-- Header Banner -->
          <div class="px-6 py-4 text-white"
               :style="{ background: 'linear-gradient(to right, #256, 0.9), var(--color-primary-700))' }">
            <span class="bg-white/20 text-xs font-medium px-3 py-1 rounded-full backdrop-blur-sm">
              ID: {{ employee.employee_id }}
            </span>
          </div>

          <!-- Main Content -->
          <div id="print_content" class="p-6 space-y-8">
            <!-- Profile Header -->
            <div class="flex flex-col sm:flex-row items-center gap-6">
              <div class="flex-shrink-0">
                <img v-if="employee.person?.photo"
                     :src="employee.person.photo"
                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg ring-4 ring-gray-200 dark:ring-gray-700" />
                <div v-else
                     class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600
                            flex items-center justify-center text-white font-bold text-3xl shadow-lg
                            border-4 border-white ring-4 ring-gray-200 dark:ring-gray-700">
                  {{ getInitials(employee.person_name) }}
                </div>
              </div>

              <div class="text-center sm:text-left flex-1">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ employee.person_name }}</h3>
                 <p v-if="employee.designation || employee.company" class="text-gray-600 dark:text-gray-300">
        {{ employee.designation }}
        <span v-if="employee.designation && employee.company"> | </span>
        {{ employee.company }}
      </p>
                <div class="flex flex-wrap gap-2 justify-center sm:justify-start mt-3">
                  <span class="px-3 py-1 rounded-full text-xs font-medium"
                        :class="employee.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ employee.status }}
                  </span>
                  <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">
                    {{ employee.employment_type }}
                  </span>
                  <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-3 py-1 rounded-full">
                    {{ employee.currency }}
                  </span>
                </div>
              </div>
            </div>

            <!-- All Info Sections -->
            <InfoSection title="Personal Information">
              <InfoItem label="Person ID" :value="employee.person_id" />
              <InfoItem label="Full Name" :value="employee.person_name" />
              <InfoItem label="Gender" :value="employee.gender" />
              <InfoItem label="Marital Status" :value="employee.marital_status" />
              <InfoItem label="Blood Group" :value="employee.blood_group" />
              <InfoItem label="Work Experience" :value="employee.experience + ' years'" />
              <InfoItem label="Official Email" :value="employee.email" />
              <InfoItem label="Official Phone" :value="employee.phone" />
            </InfoSection>

            <InfoSection title="Company & Hierarchy">
              <InfoItem label="Company" :value="`${employee.company_name} (ID: ${employee.company_id})`" />
              <InfoItem label="Financial Company" :value="`${employee.fin_com_name} (ID: ${employee.fin_com_id})`" />
              <InfoItem label="Division" :value="employee.division_name" />
              <InfoItem label="Department" :value="employee.department_name" />
              <InfoItem label="Designation" :value="employee.designation_name" />
              <InfoItem label="Employee Code" :value="employee.employee_code" />
              <InfoItem label="Employee ID" :value="employee.employee_id" />
              <InfoItem label="Work Location" :value="employee.location" />
              <InfoItem label="Shift" :value="employee.shift" />
              <InfoItem label="Reporting Manager" :value="employee.reporting_manager_name" />
              <InfoItem label="2nd Reporting Manager" :value="employee.second_reporting_manager_name" />
              <InfoItem label="Department Head" :value="employee.department_head_name" />
            </InfoSection>

            <InfoSection title="Employment Timeline">
              <InfoItem label="Joining Date" :value="formatDate(employee.joining_date)" />
              <InfoItem label="Confirmation Date" :value="formatDate(employee.confirmation_date)" />
              <InfoItem label="Probation End Date" :value="formatDate(employee.probation_end_date)" />
              <InfoItem label="Effective Date" :value="formatDate(employee.effective_date)" />
              <InfoItem label="Last Appraisal" :value="formatDate(employee.last_appraisal)" />
              <InfoItem label="Next Appraisal Due" :value="formatDate(employee.next_appraisal)" />
              <InfoItem label="Last Promotion" :value="formatDate(employee.last_promotion)" />
              <InfoItem label="Next Promotion Due" :value="formatDate(employee.next_promotion)" />
            </InfoSection>

            <InfoSection title="Compensation Details">
              <InfoItem label="Gross Salary" :value="formatCurrency(employee.gross_salary)" />
              <InfoItem label="Basic Salary" :value="formatCurrency(employee.basic_salary)" />
              <InfoItem label="House Rent Allowance" :value="formatCurrency(employee.hra)" />
              <InfoItem label="Medical Allowance" :value="formatCurrency(employee.medical)" />
              <InfoItem label="Transport Allowance" :value="formatCurrency(employee.transport)" />
              <InfoItem label="Other Allowances" :value="formatCurrency(employee.other_allowances)" />
              <InfoItem label="Overtime Rate" :value="formatCurrency(employee.overtime_rate) + '/hr'" />
              <InfoItem label="Total Salary" :value="formatCurrency(employee.total_salary)" />
              <InfoItem label="Currency" :value="employee.currency" />
            </InfoSection>

            <InfoSection title="Banking & Tax Information">
              <InfoItem label="Bank Name" :value="employee.bank_name" />
              <InfoItem label="Account Number" :value="employee.account_number" />
              <InfoItem label="IFSC / Routing" :value="employee.routing" />
              <InfoItem label="PAN / SSN" :value="employee.tax_id" />
              <InfoItem label="Passport Number" :value="employee.passport" />
            </InfoSection>

            <InfoSection title="Emergency Contact & Others">
              <InfoItem label="Emergency Contact" :value="employee.emergency_contact" />
              <InfoItem label="Emergency Phone" :value="employee.emergency_phone" />
              <InfoItem label="Skills" :value="formatSkills(employee.skills)" />
              <InfoItem label="Office In/Out Time" :value="employee.office_time" />
              <InfoItem label="Employee Status">
                <Badge :active="(employee.employee_status || '').toLowerCase() === 'active'">
                  {{ employee.employee_status || '—' }}
                </Badge>
              </InfoItem>
            </InfoSection>

            <!-- Audit Trail - Hidden on Print -->
            <div class="text-xs text-gray-500 border-t pt-4 mt-8 print:hidden">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>Created by: {{ employee.created_by }} on {{ formatDateTime(employee.created_at) }}</div>
                <div>Updated by: {{ employee.updated_by }} on {{ formatDateTime(employee.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InfoSection from '@/components/InfoSection.vue'
import InfoItem from '@/components/InfoItem.vue'
import Badge from '@/components/Badge.vue'
import { useExport } from '@/composables/useExport'
import BaseActionButton from '@/components/BaseActionButton.vue'
import { Printer, FileDown,CornerUpLeft } from 'lucide-vue-next';

const { employee } = usePage().props
const { isProcessing, print, exportToPDF, exportToExcel,exportPdfv2 } = useExport({
  contentId: 'print_content',
  filename: `${employee.person_name.replace(/\s+/g, '_')}_Profile`,
  title: `${employee.person_name} - Employee Profile`,
})
// Formatters
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'
const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'
const formatCurrency = (amount) => amount ? new Intl.NumberFormat('en-US', { style: 'currency', currency: employee.currency || 'USD' }).format(amount) : '—'
const formatSkills = (skills) => Array.isArray(skills) ? skills.join(', ') : (skills || '—')
const getInitials = (name) => name ? name.trim().split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : '??'

// Actions
const goBack = () => router.visit('/employees')
const isPrinting = ref(false)
const isPdfing = ref(false)
const isExcelling = ref(false)

const handlePrint = async () => {
  isPrinting.value = true
  await print()
  isPrinting.value = false
}

const handlePDF = async () => {
  isPdfing.value = true
  await exportToPDF('print_content', 'employee_profile')
  isPdfing.value = false
}

const handleExcel = async () => {
  isExcelling.value = true
  await excelFunc()
  isExcelling.value = false
}
</script>
