<template>
    <AppLayout
        title="Movement Register"
        :breadcrumbs="[
          { title: 'Dashboard', href: '/' },
          { title: 'Movement Register', href: '/movement-registers' }
        ]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Movement Register</h1>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button
                            v-if="hasPendingSelected && canApprove"
                            @click="bulkStatusUpdate(2)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all shadow-md"
                        >
                            <CheckCircle class="w-4 h-4" />
                            <span>Approve ({{ selectedPendingCount }})</span>
                        </button>

                        <button
                            v-if="hasPendingSelected && canApprove"
                            @click="bulkStatusUpdate(22)"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all shadow-md"
                        >
                            <XCircle class="w-4 h-4" />
                            <span>Reject ({{ selectedPendingCount }})</span>
                        </button>

                        <button
                            v-if="hasApprovedSelected"
                            @click="printSelected"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all shadow-md"
                        >
                            <Printer class="w-4 h-4" />
                            <span>Print Approved ({{ selectedApprovedCount }})</span>
                        </button>

                        <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />

                        <Link
                            v-if="canCreate"
                            href="/movement-registers/create"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <Plus class="w-4 h-4" />
                            <span class="hidden sm:inline">Add Movement</span>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2 text-left">
                            <input type="checkbox" @change="toggleAll" :checked="isAllSelected" class="rounded border-gray-300 dark:bg-gray-700" />
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">S.L</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Actions</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Purpose</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">From / To</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Times</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Bill</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Status</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(m, i) in movements.data" :key="m.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-2">
                            <input
                                v-if="m.movement_ended_at && m.status !== 22"
                                type="checkbox"
                                v-model="selectedMovements"
                                :value="m.id"
                                class="rounded border-gray-300 dark:bg-gray-700"
                            />
                            <span v-else class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ (movements.current_page - 1) * movements.per_page + i + 1 }}</td>

                        <td class="px-4 py-2 text-sm whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <Link v-if="m.status === 0" :href="`/movement-registers/${m.id}/edit`" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md border border-blue-200" title="Edit">
                                    <Edit class="w-4 h-4" />
                                </Link>
                                <Link
                                    :href="`/movement-registers/${m.id}`"
                                    class="p-1.5 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700"
                                    title="View Details"
                                >
                                    <Eye class="w-4 h-4" />
                                </Link>
<!--                                <span v-else class="p-1.5 text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 cursor-not-allowed" :title="m.status === 2 ? 'Approved items are locked' : 'Rejected items are locked'">-->
<!--                                    <Lock class="w-4 h-4" />-->
<!--                                </span>-->

                                <template v-if="canApprove && m.movement_ended_at && m.status === 0">
                                    <button @click="statusAction(m.id, 2)" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-md border border-emerald-200" title="Approve">
                                        <CheckCircle class="w-4 h-4" />
                                    </button>
                                    <button @click="statusAction(m.id, 22)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md border border-red-200" title="Reject">
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                </template>

                                <DeleteDialog
                                    v-if="canDelete && (m.status !== 2  && m.status !== 22)"
                                    :url="`/movement-registers/${m.id}`"
                                    record-name="Movement"
                                    @deleted="handleDelete"
                                />
                            </div>
                        </td>

                        <td class="px-4 py-2 text-sm font-mono text-gray-400">{{ m.id }}</td>
                        <td class="px-4 py-2 text-sm">
                            <div class="font-medium text-gray-900 dark:text-white">{{ Array.isArray(m.purpose) ? m.purpose.map(p => p.name).join(', ') : m.purpose }}</div>
                            <div class="text-xs text-gray-500">{{ Array.isArray(m.transport_mode) ? m.transport_mode.map(t => t.name).join(', ') : (m.transport_mode || 'N/A') }}</div>
                        </td>
                        <td class="px-4 py-2 text-sm">
                            <div class="text-blue-600 dark:text-blue-400 font-medium whitespace-nowrap">F: {{ m.origin_location_name }}</div>
                            <div class="text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">T: {{ m.destination_location_name || '---' }}</div>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400">
                            <div class="whitespace-nowrap font-mono">S: {{ formatDateTime(m.movement_started_at) }}</div>
                            <div v-if="m.movement_ended_at" class="whitespace-nowrap font-mono">E: {{ formatDateTime(m.movement_ended_at) }}</div>
                            <div v-else class="text-amber-500 font-bold animate-pulse">In Progress...</div>
                        </td>
                        <td class="px-4 py-2 text-sm font-bold">{{ m.conveyance_amount || 0 }}</td>
                        <td class="px-4 py-2 text-sm">
                            <span v-if="!m.movement_ended_at" class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] uppercase font-black border border-amber-200">
                                In Progress
                            </span>
                            <span v-else :class="{
                                'bg-yellow-100 text-yellow-700 border-yellow-200': m.status === 0,
                                'bg-green-100 text-green-700 border-green-200': m.status === 2,
                                'bg-red-100 text-red-700 border-red-200': m.status === 22
                            }" class="px-2 py-0.5 rounded-full text-[10px] uppercase font-black border">
                                {{ m.status === 0 ? 'Pending' : m.status === 2 ? 'Approved' : 'Rejected' }}
                            </span>
                            <div v-if="m.status === 22 && m.rejection_reason" class="text-[9px] text-red-500 italic mt-1 leading-tight max-w-[120px]">
                                {{ m.rejection_reason }}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="movements.links" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/custom/Pagination.vue'
import PerPageSelect from '@/components/custom/PerPageSelect.vue'
import DeleteDialog from '@/components/custom/DeleteDialog.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { Plus, Printer, Edit,Eye, CheckCircle, Lock, XCircle } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { usePagination } from '@/composables/usePagination'
import Swal from 'sweetalert2'

const movements = defineModel('movements', { required: true }) as any
const { perPage, update: updatePerPage } = usePagination()

const { authUser } = usePage().props as any
const canCreate = computed(() => authUser?.permissions?.includes('movement.create') ?? false)
const canDelete = computed(() => authUser?.permissions?.includes('movement.delete') ?? false)
const canApprove = canCreate;

const selectedMovements = ref<number[]>([])

// UPDATED: isAllSelected only considers rows that aren't rejected
const isAllSelected = computed(() => {
    const selectable = movements.value.data.filter((m: any) => m.movement_ended_at && m.status !== 22)
    return selectable.length > 0 && selectedMovements.value.length === selectable.length
})

// UPDATED: toggleAll only selects rows that aren't rejected
const toggleAll = (e: any) => {
    if (e.target.checked) {
        selectedMovements.value = movements.value.data
            .filter((m: any) => m.movement_ended_at && m.status !== 22)
            .map((m: any) => m.id)
    } else {
        selectedMovements.value = []
    }
}

const selectedPendingCount = computed(() => movements.value.data.filter((m: any) => selectedMovements.value.includes(m.id) && m.status === 0).length)
const hasPendingSelected = computed(() => selectedPendingCount.value > 0)
const selectedApprovedCount = computed(() => movements.value.data.filter((m: any) => selectedMovements.value.includes(m.id) && m.status === 2).length)
const hasApprovedSelected = computed(() => selectedApprovedCount.value > 0)

// --- Actions ---

const bulkStatusUpdate = async (status: number) => {
    const targetIds = movements.value.data
        .filter((m: any) => selectedMovements.value.includes(m.id) && m.status === 0)
        .map((m: any) => m.id)

    if (targetIds.length === 0) return;

    if (status === 22) {
        const { value: reason } = await Swal.fire({
            title: 'Reject Selected Items?',
            input: 'textarea',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'Type reason here...',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Reject'
        });

        if (reason !== undefined) {
            router.post('/movement-registers/bulk-status-update', { ids: targetIds, status, reason }, {
                onSuccess: () => selectedMovements.value = []
            });
        }
    } else {
        if (confirm(`Approve ${targetIds.length} items?`)) {
            router.post('/movement-registers/bulk-status-update', { ids: targetIds, status }, {
                onSuccess: () => selectedMovements.value = []
            });
        }
    }
}

const statusAction = async (id: number, status: number) => {
    if (status === 22) {
        const { value: reason } = await Swal.fire({
            title: 'Reject this record?',
            input: 'textarea',
            inputLabel: 'Rejection Reason',
            showCancelButton: true,
            confirmButtonColor: '#ef4444'
        });

        if (reason !== undefined) {
            router.post('/movement-registers/bulk-status-update', { ids: [id], status, reason });
        }
    } else {
        if (confirm('Approve this movement?')) {
            router.post('/movement-registers/bulk-status-update', { ids: [id], status });
        }
    }
}

const printSelected = () => {
    const approvedIds = movements.value.data
        .filter((m: any) => selectedMovements.value.includes(m.id) && m.status === 2)
        .map((m: any) => m.id);

    if (approvedIds.length === 0) return alert("Select approved items to print.");
    window.open(`/movement-registers-print-report?ids=${approvedIds.join(',')}`, '_blank');
}

const formatDateTime = (dateStr: string) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: true })
}

const handleDelete = ({ success }: { success: boolean }) => {
    if (success) window.location.reload()
}
</script>
