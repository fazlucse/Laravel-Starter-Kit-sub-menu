<template>
    <AppLayout
        title="Payroll Control"
        :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'Payroll', href: '/payroll' }]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <div class="p-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ isCreating ? 'Generate Payroll' : 'Payroll' }}
                    </h1>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-64" v-if="!isCreating">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <Search class="w-4 h-4" />
                            </span>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search batches..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm focus:ring-blue-500"
                            />
                        </div>

                        <PerPageSelect v-if="!isCreating" v-model="perPage" @update:modelValue="updatePerPage" />

                        <Link
                            v-if="!isCreating && canCreate"
                            href="/payroll/generate"
                            class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition active:scale-95"
                        >
                            <Plus class="w-4 h-4" />
                            <span class="hidden sm:inline">Generate Payroll</span>
                        </Link>
                        <button
                            v-if="isCreating"
                            @click="isCreating = false"
                            class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <ArrowLeft class="w-4 h-4" />
                            <span>Back</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-hidden bg-white dark:bg-gray-800">

                <div v-if="!isCreating" class="h-full overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-widest">S.L</th>
                            <th class="px-4 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Actions</th>
                            <th class="px-4 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Batch ID</th>
                            <th class="px-4 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Office / Dept</th>
                            <th class="px-4 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Month</th>
                            <th class="px-4 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-widest">Staff</th>
                            <th class="px-4 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Gross Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Net Payable</th>
                            <th class="px-4 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Created Info</th>
                            <th class="px-4 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Status</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(item, i) in historyData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-4 py-3 text-sm text-gray-500 font-mono">
                                {{ (payrollHistory.current_page - 1) * payrollHistory.per_page + i + 1 }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button v-if="canEdit && item.status !== 'approved'" @click="approveBatch(item.id)" class="p-1.5 cursor-pointer text-emerald-600 hover:bg-emerald-50 rounded transition">
                                        <CheckCircle class="w-4 h-4" />
                                    </button>
                                    <Link :href="`/payroll/batch/${item.id}`" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <DeleteDialog
                                        v-if="canDelete && item.status !== 'approved'"
                                        :url="`/payroll/delete/${item.id}`"
                                        record-name="Payroll Batch"
                                    />
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-black text-blue-600 font-mono">
                                #{{ item.id.toString().padStart(5, '0') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-bold text-gray-900 dark:text-white uppercase">{{ item.com_name }}</div>
                                <div class="text-[11px] text-gray-500 uppercase font-medium">{{ item.department_name || 'All Departments' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">{{ formatMonth(item.payroll_month) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-bold text-xs">
                                    {{ item.total_staff }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-mono text-gray-600 dark:text-gray-400">
                                {{ formatCurr(item.total_gross_amount) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-mono font-black text-blue-600 dark:text-blue-400">
                                {{ formatCurr(item.total_net_disbursement) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate max-w-[120px]">
                                    {{ item.prepared_by_name || 'Admin' }}
                                </div>
                                <div class="text-[10px] text-gray-500 font-mono">
                                    {{ formatDateTime(item.created_at) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="getStatusClass(item.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase border">
                                    {{ item.status }}
                                </span>
                            </td>

                        </tr>
                        </tbody>
                    </table>

                    <div v-if="!historyData.length" class="text-center py-20 text-gray-500 italic">
                        No payroll records found.
                    </div>
                </div>

                <div v-else class="h-full overflow-y-auto p-6 lg:p-12 bg-gray-50 dark:bg-gray-900">
                    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Salary Month</label>
                                    <input type="month" v-model="form.payrollMonth" class="w-full h-12 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-4" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Company / Office</label>
                                    <select v-model="form.office" class="w-full h-12 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-4">
                                        <option value="">All Locations</option>
                                        <option v-for="o in offices" :key="o" :value="o">{{ o }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Department Filter</label>
                                    <select v-model="form.department" class="w-full h-12 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-4">
                                        <option value="">All Departments</option>
                                        <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Processing Status</label>
                                    <select v-model="form.postOption" class="w-full h-12 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-4">
                                        <option value="draft">Save as Draft</option>
                                        <option value="posted">Post for Approval</option>
                                    </select>
                                </div>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 flex gap-4">
                                <Calculator class="w-6 h-6 text-blue-500 shrink-0" />
                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                    This action will generate payroll for all matching active employees.
                                    Basic salary, allowances, and fixed deductions will be snapshotted.
                                </p>
                            </div>

                            <div class="flex justify-end gap-3 pt-6 border-t dark:border-gray-700">
                                <button @click="isCreating = false" class="px-6 py-2.5 font-bold text-gray-500">Cancel</button>
                                <button
                                    @click="submitForm"
                                    :disabled="form.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl font-bold flex items-center gap-2 shadow-lg shadow-blue-500/30"
                                >
                                    <Save v-if="!form.processing" class="w-4 h-4" />
                                    <LoadingSpinner v-else />
                                    Run Payroll
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Pagination v-if="!isCreating" :links="payrollHistory.links" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PerPageSelect from '@/components/custom/PerPageSelect.vue'
import Pagination from '@/components/custom/Pagination.vue'
import DeleteDialog from '@/components/custom/DeleteDialog.vue'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import { Plus, Search, Eye, CheckCircle, ArrowLeft, Save, Calculator } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'
import { computed, ref, watch } from 'vue'

// Props using defineModel for reactive sync
const payrollHistory = defineModel('payrollHistory', { required: true }) as any
const departments = defineModel('departments') as any
const offices = defineModel('offices') as any

// UI Control
const isCreating = ref(false)
const searchQuery = ref('')

// Permissions
const { authUser } = usePage().props as any
const canCreate = computed(() => authUser?.permissions.includes('payroll.create') ?? false)
const canEdit   = computed(() => authUser?.permissions.includes('payroll.edit') ?? false)
const canDelete = computed(() => authUser?.permissions.includes('payroll.delete') ?? false)

// Pagination logic
const { perPage, update: updatePerPage } = usePagination()

// Helper: Format Currency
const formatCurr = (value: number) => {
    if (!value || value === 0) return '—';
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value).replace(/^/, '৳ ');
}

// Helper: Format Month
const formatMonth = (dateStr: string) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    });
}

// Helper: Format Date Time
const formatDateTime = (dateTimeStr: string) => {
    if (!dateTimeStr) return '—';
    return new Date(dateTimeStr).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

// Form for Generation
const form = useForm({
    department: '',
    office: '',
    payrollMonth: new Date().toISOString().slice(0, 7),
    postOption: 'draft',
})

const historyData = computed(() => payrollHistory.value?.data || [])

// Watch search query
watch(searchQuery, (val) => {
    router.get('/payroll', { search: val }, { preserveState: true, replace: true })
})

const submitForm = () => {
    form.post('/payroll/generate', {
        preserveScroll: true,
        onSuccess: () => {
            isCreating.value = false
            form.reset()
        }
    })
}

const approveBatch = (id: number) => {
    if(confirm('Approve this payroll?')) {
        router.patch(`/payroll/${id}/approve`)
    }
}

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        posted: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-500 dark:border-amber-800',
        approved: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
