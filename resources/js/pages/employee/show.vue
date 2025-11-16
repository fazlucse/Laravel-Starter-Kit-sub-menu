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

        <!-- ==== CARD ==== -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden print:shadow-none print:border print:border-gray-300">

           <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex flex-wrap  gap-3 border-t print:hidden">
                        
              <!-- Back button -->
              <button
                @click="goBack"
                class="cursor-pointer inline-flex items-center gap-1 text-sm font-medium hover:underline"
                title="Back to list"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
                Back
              </button>

              <!-- Print button -->
              <button
                @click="printPage"
                class="cursor-pointer inline-flex items-center gap-1 text-sm font-medium hover:underline"
                title="Print this page"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
              </button>
           
            <button @click="exportPDF"
                    class="cursor-pointer inline-flex items-center gap-1 text-sm font-medium hover:underline">
              Export PDF
            </button>
              <h4 class="text-xl font-semibold mb-0">Employee Details</h4>

          </div>
          <div
            class="flex items-center justify-between px-6 py-4 text-white"
            :style="{ background: 'linear-gradient(to right, var(--color-primary-600), var(--color-primary-700))' }"
          >


            <span class="bg-white/20 text-xs font-medium px-3 py-1 rounded-full backdrop-blur-sm">
              ID: #{{ employee.code }}
            </span>
          </div>

          <div class="p-6 space-y-8">

            <!-- ==== PROFILE ==== -->
            <div class="flex flex-col sm:flex-row items-center gap-6">
              <div class="flex-shrink-0">
                <img
                  :src="employee.photo || 'https://via.placeholder.com/120'"
                  alt="Employee Photo"
                  class="w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-white shadow-lg"
                />
              </div>
              <div class="text-center sm:text-left flex-1">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ employee.person_name }}</h3>
                <p class="text-gray-600 dark:text-gray-300">{{ employee.designation }} | {{ employee.company }}</p>
                <div class="flex flex-wrap gap-2 justify-center sm:justify-start mt-2">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-medium"
                    :class="employee.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  >{{ employee.status }}</span>
                  <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">{{ employee.employment_type }}</span>
                  <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-3 py-1 rounded-full">{{ employee.currency }}</span>
                </div>
              </div>
            </div>

            <!-- ==== SECTIONS ==== -->
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
              <InfoItem label="Company" :value="`${employee.company} (ID: ${employee.company_id})`" />
              <InfoItem label="Financial Company" :value="`${employee.financial_company} (ID: ${employee.financial_company_id})`" />
              <InfoItem label="Division" :value="employee.division" />
              <InfoItem label="Department" :value="employee.department" />
              <InfoItem label="Designation" :value="employee.designation" />
              <InfoItem label="Employee Code" :value="employee.code" />
              <InfoItem label="Employee ID" :value="employee.employee_id" />
              <InfoItem label="Work Location" :value="employee.location" />
              <InfoItem label="Shift" :value="employee.shift" />
              <InfoItem label="Reporting Manager" :value="employee.reporting_manager" />
              <InfoItem label="2nd Reporting Manager" :value="employee.reporting_manager_2" />
              <InfoItem label="Department Head" :value="employee.department_head" />
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
              <InfoItem label="Salary Stopped?">
                <Badge :active="!employee.salary_stopped">No</Badge>
              </InfoItem>
            </InfoSection>

            <InfoSection title="Banking & Tax Information">
              <InfoItem label="Bank Name" :value="employee.bank_name" />
              <InfoItem label="Account Number" :value="employee.account_number" />
              <InfoItem label="IFSC / Routing" :value="employee.routing" />
              <InfoItem label="PAN / SSN" :value="employee.tax_id" />
              <InfoItem label="Tax Status" :value="employee.tax_status" />
              <InfoItem label="Tax Deduction">
                <Badge :active="employee.tax_deduction">Yes</Badge>
              </InfoItem>
              <InfoItem label="Passport Number" :value="employee.passport" />
            </InfoSection>

            <InfoSection title="Emergency Contact & Others">
              <InfoItem label="Emergency Contact" :value="employee.emergency_contact" />
              <InfoItem label="Emergency Phone" :value="employee.emergency_phone" />
              <InfoItem label="Skills">
                <span class="text-gray-600 dark:text-gray-400">{{ formatSkills(employee.skills) }}</span>
              </InfoItem>
              <InfoItem label="Office In/Out Time" :value="employee.office_time" />
              <InfoItem label="Late Time (mins)" :value="employee.late_time" />
              <InfoItem label="Employee Status">
                <Badge :active="employee.status === 'Active'">{{ employee.status }}</Badge>
              </InfoItem>
            </InfoSection>

            <!-- Audit -->
            <div class="text-xs text-gray-500 dark:text-gray-400 border-t pt-4 mt-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>Created By: {{ employee.created_by }} on {{ formatDateTime(employee.created_at) }}</div>
                <div>Last Updated By: {{ employee.updated_by }} on {{ formatDateTime(employee.updated_at) }}</div>
              </div>
            </div>

          </div>

          <!-- Footer (screen only) -->
       
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

const { employee } = usePage().props

/* ---------- FORMATTERS ---------- */
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'
const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '—'
const formatCurrency = (a) => a ? `$${parseFloat(a).toLocaleString('en-US', { minimumFractionDigits: 2 })}` : '—'

const formatSkills = (skills) => {
  if (Array.isArray(skills) && skills.length) return skills.join(', ')
  if (typeof skills === 'string' && skills.trim()) return skills
  return '—'
}

/* ---------- ACTIONS ---------- */
const goBack = () => router.visit('/employees')
const printPage = () => window.print()
const editProfile = () => alert('Edit Profile – Coming Soon!')
const exportPDF = () => alert('PDF Export – Coming Soon!')
</script>

<style scoped>
:root {
  --color-primary-500: #3b82f6;
  --color-primary-600: #2563eb;
  --color-primary-700: #1d4ed8;
}

/* ---- LABEL SPACING ---- */
.info-item span:first-child::after {
  content: ":\00a0";   /* colon + non‑breaking space */
}

/* ---- PRINT STYLES ---- */
@media print {
  body > *:not(.print\\:block) { display: none !important; }
  .print\\:block { display: block !important; }

  .bg-white { background: white !important; }
  .shadow-lg { box-shadow: none !important; }
  .rounded-lg { border-radius: 0 !important; }

  .text-gray-900, .text-gray-700, .text-gray-600 { color: #000 !important; }
  [style*='var(--color-primary'] { background: #2563eb !important; }
}
</style>