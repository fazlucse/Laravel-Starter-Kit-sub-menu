<template>
    <AppLayout
        title="Movement Register"
        :breadcrumbs="[
          { title: 'Dashboard', href: '/' },
          { title: 'Movement Register', href: '/movement-registers' }
        ]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <!-- Header -->
            <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Movement Register</h1>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
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

            <!-- Table -->
            <div class="flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">S.L</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Purpose</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Transport</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">From</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">To</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Start Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">End Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(m, i) in movements.data" :key="m.id" class="hover:bg-gray-100 dark:hover:bg-gray-800">
                        <td class="px-4 py-2 text-sm">{{ (movements.current_page - 1) * movements.per_page + i + 1 }}</td>
                        <td class="px-4 py-2 text-sm">{{ m.purpose }}</td>
                        <td class="px-4 py-2 text-sm">{{ m.transport_mode }}</td>
                        <td class="px-4 py-2 text-sm">{{ m.origin_location_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ m.destination_location_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ m.movement_start_time }}</td>
                        <td class="px-4 py-2 text-sm">{{ m.movemoent_end_time }}</td>
                        <td class="px-4 py-2 text-sm">
                                <span :class="{
                                    'text-yellow-500': m.status === 0,
                                    'text-green-500': m.status === 2,
                                    'text-red-500': m.status === 22
                                }">
                                    {{ m.status === 0 ? 'Pending' : m.status === 2 ? 'Approved' : 'Rejected' }}
                                </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <DeleteDialog
                                v-if="canDelete"
                                :url="`/movement-registers/${m.id}`"
                                record-name="Movement Register"
                                @deleted="handleDelete"
                            />
                        </td>
                    </tr>

                    <tr v-if="!movements.data.length">
                        <td colspan="9" class="text-center py-4 text-gray-500 dark:text-gray-400">No movements found.</td>
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
import { Link, usePage } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import { computed } from 'vue'
import { usePagination } from '@/composables/usePagination'

const movements = defineModel('movements', { required: true }) as any
const { perPage, update: updatePerPage } = usePagination()

const { authUser } = usePage().props
const canCreate = computed(() => authUser?.permissions?.includes('movement.create') ?? false)
const canDelete = computed(() => authUser?.permissions?.includes('movement.delete') ?? false)

const handleDelete = ({ success }: { success: boolean }) => {
    if (success) window.location.reload()
}
</script>
