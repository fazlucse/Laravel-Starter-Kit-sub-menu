<template>
  <AppLayout
    title="Employee Details"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Employees', href: '/employees' },
      { title: 'Employee Details', href: '#' }
    ]"
  >
    <div class="bg-white dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">

        <!-- PRINTABLE AREA -->
        <div id="print_content" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">

          <!-- Top Buttons - Hidden on Print -->
          <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex flex-wrap gap-4 items-center print:hidden">
            <button @click="goBack" class="text-sm font-medium text-gray-700 hover:underline">
              Back
            </button>

            <button @click="printOnlyThis" class="text-sm font-bold text-blue-600 hover:underline">
              Print
            </button>

            <button @click="exportPDF" class="text-sm font-medium text-gray-700 hover:underline">
              Export PDF
            </button>

            <h4 class="ml-auto text-xl font-semibold text-gray-800 dark:text-white">
              Employee Details
            </h4>
          </div>

          <!-- Header Banner -->
          <div class="px-6 py-4 text-white"
               :style="{ background: 'linear-gradient(to right, #256, 0.9), var(--color-primary-700))' }">
            <span class="bg-white/20 text-xs font-medium px-3 py-1 rounded-full backdrop-blur-sm">
              ID: {{ employee.employee_id }}
            </span>
          </div>

          <!-- Main Content -->
          <div class="p-6 space-y-8">
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
                <p class="text-gray-600 dark:text-gray-300">{{ employee.designation }} | {{ employee.company }}</p>
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
import { usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InfoSection from '@/components/InfoSection.vue'
import InfoItem from '@/components/InfoItem.vue'
import Badge from '@/components/Badge.vue'
import { useExport } from '@/composables/useExport'

const { employee } = usePage().props
const { printElement, downloadPDF, exportExcel } = useExport()
// Formatters
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'
const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'
const formatCurrency = (amount) => amount ? new Intl.NumberFormat('en-US', { style: 'currency', currency: employee.currency || 'USD' }).format(amount) : '—'
const formatSkills = (skills) => Array.isArray(skills) ? skills.join(', ') : (skills || '—')
const getInitials = (name) => name ? name.trim().split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : '??'

// Actions
const goBack = () => router.visit('/employees')
const exportPDF = () => alert('PDF Export coming soon!')

// CLEAN PRINT — NO SHADOWS, RINGS, BORDERS
const printOnlyThis = () => {
  const content = document.getElementById('print_content')
  if (!content) return

  const printWindow = window.open('', 'Print', 'width=1000,height=800')

  // Collect all styles
  let stylesHtml = ''
  document.querySelectorAll('link[rel="stylesheet"], style').forEach(el => {
    if (el.tagName === 'LINK') {
      stylesHtml += `<link rel="stylesheet" href="${el.href}" />`
    } else {
      stylesHtml += `<style>${el.innerHTML}</style>`
    }
  })

  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>${employee.person_name} - Employee Profile</title>
      ${stylesHtml}
      <style>
        @page { margin: 1cm; size: A4 portrait; }
        body {
          margin: 0;
          padding: 20px;
          font-family: Arial, Helvetica, sans-serif;
          background: white !important;
          color: #000 !important;
          line-height: 1.6;
        }
        /* REMOVE ALL SHADOWS & RINGS ON PRINT */
        *, *::before, *::after {
          box-shadow: none !important;
          -webkit-box-shadow: none !important;
          text-shadow: none !important;
          border: none !important;
        }
        .shadow, .shadow-lg, .ring, .ring-4 { box-shadow: none !important; }
        img { border-radius: 50%; border: 3px solid #333; }
        #print_content {
          box-shadow: none !important;
          border: none !important;
          border-radius: 0 !important;
          background: white !important;
        }
        .print\\:hidden { display: none !important; }
        h3 { font-size: 24px; margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: bold; }
      </style>
    </head>
    <body>
      ${content.outerHTML}
    </body>
    </html>
  `)

  printWindow.document.close()
  printWindow.focus()

  printWindow.onload = () => {
    setTimeout(() => {
      printWindow.print()
      // printWindow.close()  // Uncomment if you want auto-close after print
    }, 800)
  }
}
</script>

<!-- Optional: Extra safety for normal Ctrl+P -->
<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #print_content, #print_content * {
    visibility: visible;
  }
  #print_content {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    box-shadow: none !important;
    border: none !important;
    background: white !important;
  }
  *, *::before, *::after {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
  }
  .print\\:hidden, nav, header, footer, aside {
    display: none !important;
  }
}
</style>