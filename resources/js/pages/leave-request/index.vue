<template>
    <AppLayout
        title="Leave Requests"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Leave Request', href: '/leave-request' }
    ]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <!-- Header -->
            <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Leave Request</h1>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage"/>

                        <Link
                            v-if="canCreate"
                            href="/leave-request/create"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <Plus class="w-4 h-4"/>
                            <span class="hidden sm:inline">Add Leave Request</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            S.L
                        </th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Actions
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Employee
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Person ID
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Department
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Division
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            AL
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            CL
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            SL
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            MAT
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            PAT
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Total
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Status
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Remarks
                        </th>

                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(l, i) in leaveRequests.data" :key="l.id"
                        class="hover:bg-gray-100 dark:hover:bg-gray-800">
                        <td class="px-4 py-2 text-sm">
                            {{ (leaveRequests.current_page - 1) * leaveRequests.per_page + i + 1 }}
                        </td>
                        <!--                        <td class="px-4 py-2 text-right">-->
                        <!--                            <DeleteDialog-->
                        <!--                                v-if="canDelete"-->
                        <!--                                :url="`/leave-requests/${l.id}`"-->
                        <!--                                record-name="Leave Request"-->
                        <!--                                @deleted="handleDelete"-->
                        <!--                            />-->
                        <!--                        </td>-->
                        <td class="px-4 py-2 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="`/leave-request/show/${l.id}`"
                                    class="cursor-pointer inline-flex items-center p-1.5 rounded transition-all duration-200
           text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                    title="View Details"
                                >
                                    <Eye class="w-4 h-4"/>
                                </Link>

                                <Link
                                    v-if="l.status === 0"
                                    :href="`/leave-request/${l.id}/edit`"
                                    class="cursor-pointer inline-flex items-center p-1.5 rounded transition-all duration-200
           text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/50"
                                >
                                    <Pencil class="w-4 h-4"/>
                                </Link>

                                <!--                                <button-->
                                <!--                                    v-if="l.status === 0 && canApprove"-->
                                <!--                                    @click="approveRequest(l.id)"-->
                                <!--                                    class="text-green-600 hover:text-green-800 p-1"-->
                                <!--                                    title="Approve"-->
                                <!--                                >-->
                                <!--                                    <CheckCircle class="w-4 h-4" />-->
                                <!--                                </button>-->
                                <DeleteDialog
                                    v-if="canDelete && l.status === 0"
                                    :url="`/leave-request/${l.id}`"
                                    record-name="Leave Request"
                                    @deleted="handleDelete"
                                />
                            </div>
                        </td>
                        <td class="px-4 py-2 text-sm"> {{ l.employee?.person_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.person_id }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.employee?.department_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.employee?.division_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.al_leave }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.cl_leave }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.sl_leave }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.mat_leave }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.pat_leave }}</td>
                        <td class="px-4 py-2 text-sm">{{ l.total_leave }}</td>
                        <td class="px-4 py-2 text-sm">
                <span
                    :class="{
                    'text-yellow-500': l.status === 0,
                    'text-green-500': l.status === 2,
                    'text-red-500': l.status === 22
                  }"
                >
                  {{ l.status === 0 ? 'Pending' : l.status === 2 ? 'Approved' : 'Rejected' }}
                </span>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ l.remarks }}</td>

                    </tr>

                    <tr v-if="!leaveRequests.data.length">
                        <td colspan="14" class="text-center py-4 text-gray-500 dark:text-gray-400">No leave requests
                            found.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="leaveRequests.links"/>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/custom/Pagination.vue'
import PerPageSelect from '@/components/custom/PerPageSelect.vue'
import DeleteDialog from '@/components/custom/DeleteDialog.vue'

import {Link, usePage} from '@inertiajs/vue3'
import {Plus, Eye, Pencil, CheckCircle} from 'lucide-vue-next'
import {computed} from 'vue'
import {usePagination} from '@/composables/usePagination'

const leaveRequests = defineModel('leaveRequests', {required: true}) as any
const {perPage, update: updatePerPage} = usePagination()

const {authUser} = usePage().props
const canCreate = computed(() => authUser?.permissions?.includes('leave-request.create') ?? false)
const canDelete = computed(() => authUser?.permissions?.includes('leave-request.delete') ?? false)

const handleDelete = ({success}: { success: boolean }) => {
    if (success) window.location.reload()
}
</script>
