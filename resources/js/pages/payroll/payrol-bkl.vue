<!-- resources/js/Pages/Payroll/Report.vue -->
<template>
    <AppLayout
        title="Payroll Report"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Payroll', href: '/payroll' },
      { title: mode === 'create' ? 'Generate New Payroll' : 'Payroll Report', href: '#' },
    ]"
    >
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header & Status -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-xl p-6 mb-8 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-4">
                                <h1 class="text-3xl font-bold">Payroll Report</h1>
                                <span
                                    :class="statusClasses[postStatus]"
                                    class="px-4 py-1.5 rounded-full text-sm font-bold flex items-center gap-2"
                                >
                  {{ statusIcons[postStatus] }} {{ postStatus.toUpperCase() }}
                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 text-sm">
                                <div>
                                    <p class="font-semibold opacity-90">Department</p>
                                    <p>{{ form.department || 'All Departments' }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold opacity-90">Office</p>
                                    <p>{{ form.office || 'All Offices' }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold opacity-90">Payroll Date</p>
                                    <p>{{ form.payrollDate }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold opacity-90">Payroll Month</p>
                                    <p>{{ form.payrollMonth }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold opacity-90">Prepared By</p>
                                    <p>{{ form.preparedBy }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold opacity-90">Approved By</p>
                                    <p>{{ form.approvedBy || 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Notes & Statutory Components -->
                            <div class="mt-5 space-y-3">
                                <p v-if="form.notes" class="text-sm">
                                    <span class="font-semibold">Notes:</span> {{ form.notes }}
                                </p>

                                <div v-if="selectedIndianOptions.length > 0">
                                    <p class="font-semibold text-sm mb-2">Indian Statutory Components:</p>
                                    <div class="flex flex-wrap gap-2">
                    <span
                        v-for="opt in selectedIndianOptions"
                        :key="opt"
                        class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs font-medium"
                    >
                      {{ indianPostOptions.find(o => o.value === opt)?.label }}
                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button
                                @click="downloadCSV"
                                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 px-5 py-2.5 rounded-lg transition-colors font-medium shadow-sm"
                            >
                                <Download class="w-5 h-5" />
                                Download CSV
                            </button>

                            <button
                                @click="$inertia.visit('/payroll/generate')"
                                class="bg-white text-indigo-700 px-5 py-2.5 rounded-lg hover:bg-gray-100 transition-colors font-medium"
                            >
                                New Report
                            </button>
                        </div>
                    </div>

                    <!-- Status Change Controls -->
                    <div class="mt-6 pt-5 border-t border-white border-opacity-30">
                        <p class="text-sm font-medium mb-3">Change Status:</p>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-if="postStatus !== 'draft'"
                                @click="changeStatus('draft')"
                                class="bg-gray-600 hover:bg-gray-700 px-5 py-2 rounded-lg text-white font-medium transition-colors flex items-center gap-2"
                            >
                                📝 Revert to Draft
                            </button>

                            <button
                                v-if="postStatus === 'draft'"
                                @click="changeStatus('posted')"
                                class="bg-blue-700 hover:bg-blue-800 px-5 py-2 rounded-lg text-white font-medium transition-colors flex items-center gap-2"
                            >
                                📤 Post Payroll
                            </button>

                            <button
                                v-if="postStatus === 'posted'"
                                @click="changeStatus('approved')"
                                class="bg-green-600 hover:bg-green-700 px-5 py-2 rounded-lg text-white font-medium transition-colors flex items-center gap-2"
                            >
                                ✅ Approve Payroll
                            </button>

                            <div
                                v-if="postStatus === 'approved'"
                                class="bg-green-700 px-5 py-2 rounded-lg text-white font-medium flex items-center gap-2"
                            >
                                ✅ Payroll Approved & Finalized
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Table -->
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Emp ID</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Name</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Department</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-white">Office</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-white">Base Salary</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-white">Bonuses</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-white">Gross Salary</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-white">Deductions</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-white">Tax</th>

                                <th
                                    v-for="opt in selectedIndianOptions"
                                    :key="opt"
                                    class="px-4 py-4 text-right text-xs font-semibold text-white"
                                >
                                    {{ indianPostOptions.find(o => o.value === opt)?.label }}
                                </th>

                                <th
                                    v-if="selectedIndianOptions.length > 0"
                                    class="px-6 py-4 text-right text-sm font-semibold text-white"
                                >
                                    Indian Deductions
                                </th>

                                <th class="px-6 py-4 text-right text-sm font-semibold text-white font-bold">Net Salary</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(emp, index) in payrollData" :key="emp.id" :class="index % 2 === 0 ? 'bg-gray-50' : ''">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ emp.empId }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ emp.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ emp.department }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ emp.office }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">${{ emp.baseSalary.toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">+${{ emp.totalBonus.toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold">${{ emp.grossSalary.toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-orange-600">-${{ emp.totalDeduction.toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">-${{ emp.taxAmount.toFixed(2) }}</td>

                                <td
                                    v-for="opt in selectedIndianOptions"
                                    :key="opt"
                                    class="px-4 py-4 whitespace-nowrap text-right text-sm text-purple-600"
                                >
                                    -${{ (emp.indianDeductions[opt] || 0).toFixed(2) }}
                                </td>

                                <td
                                    v-if="selectedIndianOptions.length > 0"
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-purple-700"
                                >
                                    -${{ emp.totalIndianDeductions.toFixed(2) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-indigo-700 text-base">
                                    ${{ emp.netSalary.toFixed(2) }}
                                </td>
                            </tr>
                            </tbody>
                            <tfoot class="bg-gray-100 font-medium">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-lg font-bold">TOTAL ({{ payrollData.length }} employees)</td>
                                <td class="px-6 py-4 text-right">${{ total.baseSalary.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-right text-green-600">${{ total.bonus.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-right">${{ total.grossSalary.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-right text-orange-600">${{ total.deduction.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-right text-red-600">${{ total.tax.toFixed(2) }}</td>

                                <td
                                    v-for="opt in selectedIndianOptions"
                                    :key="opt"
                                    class="px-4 py-4 text-right text-purple-600"
                                >
                                    -${{ total.indian[opt]?.toFixed(2) || '0.00' }}
                                </td>

                                <td
                                    v-if="selectedIndianOptions.length > 0"
                                    class="px-6 py-4 text-right text-purple-700 font-bold"
                                >
                                    -${{ total.indianTotal.toFixed(2) }}
                                </td>

                                <td class="px-6 py-4 text-right text-indigo-700 text-lg font-bold">
                                    ${{ total.netSalary.toFixed(2) }}
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Download } from 'lucide-vue-next'

// Props (passed from Laravel/Inertia controller)
const props = defineProps({
    payrollData: Array,
    form: Object,
    postStatus: String,
    selectedIndianOptions: Array,
    indianPostOptions: Array,
    mode: {
        type: String,
        default: 'view'
    }
})

// Status styles & icons
const statusClasses = {
    draft: 'bg-gray-600',
    posted: 'bg-blue-600',
    approved: 'bg-green-600'
}

const statusIcons = {
    draft: '📝',
    posted: '📤',
    approved: '✅'
}

// Totals calculation
const total = computed(() => {
    return props.payrollData.reduce((acc, emp) => {
        acc.baseSalary += Number(emp.baseSalary)
        acc.bonus += emp.totalBonus
        acc.grossSalary += emp.grossSalary
        acc.deduction += emp.totalDeduction
        acc.tax += emp.taxAmount
        acc.netSalary += emp.netSalary

        // Indian deductions totals
        props.selectedIndianOptions.forEach(opt => {
            const amount = emp.indianDeductions[opt] || 0
            acc.indian[opt] = (acc.indian[opt] || 0) + amount
            acc.indianTotal += amount
        })

        return acc
    }, {
        baseSalary: 0,
        bonus: 0,
        grossSalary: 0,
        deduction: 0,
        tax: 0,
        indian: {},
        indianTotal: 0,
        netSalary: 0
    })
})

// Download CSV (client-side example - you can also do it server-side)
const downloadCSV = () => {
    // Implementation similar to your original React version
    // You can use the same logic, just adapted to Vue + current props
    alert('CSV download functionality would be implemented here (similar to React version)')
}

// Status change handler (would typically make an Inertia patch/put request)
const changeStatus = (newStatus) => {
    if (confirm(`Are you sure you want to change status to "${newStatus}"?`)) {
        // Example: Inertia.put('/payroll/status', { id: payrollId, status: newStatus })
        alert(`Status changed to: ${newStatus} (API call would be made here)`)
    }
}
</script>
