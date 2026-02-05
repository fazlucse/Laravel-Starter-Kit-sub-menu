<template>
    <AppLayout
        title="Holidays"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Holidays', href: '/holidays' }
    ]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <!-- Header -->
            <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Holidays</h1>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />

                        <Link
                            v-if="canCreate"
                            href="/holidays/create"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <Plus class="w-4 h-4" />
                            <span class="hidden sm:inline">Add Holiday</span>
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Holiday Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Persons</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Department</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Division</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Remarks</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(h, i) in holidays.data" :key="h.id" class="hover:bg-gray-100 dark:hover:bg-gray-800">
                        <td class="px-4 py-2 text-sm">{{ (holidays.current_page - 1) * holidays.per_page + i + 1 }}</td>
                        <td class="px-4 py-2 text-sm">{{ h.holiday_type_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ h.holiday_date }}</td>
                        <td class="px-4 py-2 text-sm">
                <span v-for="person in h.persons" :key="person.id" class="inline-block mr-2">
                  {{ person.name }}
                </span>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ h.department_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ h.division_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ h.remarks }}</td>
                        <td class="px-4 py-2 text-right">
                            <DeleteDialog
                                v-if="canDelete"
                                :url="`/holidays/${h.id}`"
                                record-name="Holiday"
                                @deleted="handleDelete"
                            />
                        </td>
                    </tr>

                    <tr v-if="!holidays.data.length">
                        <td colspan="8" class="text-center py-4 text-gray-500 dark:text-gray-400">No holidays found.</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="holidays.links" />
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

// Props from Inertia
const holidays = defineModel('holidays', { required: true }) as any
const { perPage, update: updatePerPage } = usePagination()

const { authUser } = usePage().props
const canCreate = computed(() => authUser?.permissions?.includes('holiday.create') ?? false)
const canDelete = computed(() => authUser?.permissions?.includes('holiday.delete') ?? false)

const handleDelete = ({ success }: { success: boolean }) => {
    if (success) window.location.reload()
}
</script>
