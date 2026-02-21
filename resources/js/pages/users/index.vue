<template>
    <AppLayout
        title="User Management"
        :breadcrumbs="[
          { title: 'Dashboard', href: '/' },
          { title: 'Settings', href: '#' },
          { title: 'Users', href: '/users' }
        ]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)]">
            <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />

                        <Link
                            href="/users/create"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md transition-all"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Add User</span>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">S.L</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Actions</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">User / Email</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Dept / Division</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Mobile</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Access Days</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Status</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(user, i) in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-2 text-sm text-gray-500">
                            {{ (users.current_page - 1) * users.per_page + i + 1 }}
                        </td>

                        <td class="px-4 py-2 text-sm whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <Link :href="`/users/edit/${user.id}`" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md border border-blue-200" title="Edit">
                                    <Edit class="w-4 h-4" />
                                </Link>
                                <DeleteDialog
                                    :url="`/users/delete/${user.id}`"
                                    record-name="User"
                                    @deleted="handleDelete"
                                />
                            </div>
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <div class="font-bold text-gray-900 dark:text-white">{{ user.name }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ user.email }}</div>
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <div class="text-gray-700 dark:text-gray-300 font-medium">{{ user.department_name || 'N/A' }}</div>
                            <div class="text-[10px] text-gray-500 uppercase">{{ user.division_name || 'No Division' }}</div>
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[10px] font-bold border border-gray-200 dark:border-gray-700">
                                {{ user.role }}
                            </span>
                        </td>

                        <td class="px-4 py-2 text-sm text-center">
                            <Smartphone v-if="user.mobile_access" class="w-4 h-4 text-green-500" />
                            <SmartphoneOff v-else class="w-4 h-4 text-red-400" />
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <div class="flex flex-wrap gap-1 max-w-[150px]">
                                <span v-for="day in user.access_days" :key="day" class="text-[9px] px-1 bg-blue-50 text-blue-600 border border-blue-100 rounded">
                                    {{ day }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <span :class="{
                                'bg-green-100 text-green-700 border-green-200': user.status === 'Active',
                                'bg-red-100 text-red-700 border-red-200': user.status === 'Inactive'
                            }" class="px-2 py-0.5 rounded-full text-[10px] uppercase font-black border">
                                {{ user.status }}
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <Pagination :links="users.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/custom/Pagination.vue'
import PerPageSelect from '@/components/custom/PerPageSelect.vue'
import DeleteDialog from '@/components/custom/DeleteDialog.vue'
import { Link } from '@inertiajs/vue3'
// Change SmartphoneOff to PhoneOff
import { Plus, Edit, Smartphone, PhoneOff } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'

const props = defineProps<{
    users: any
}>()

const { perPage, update: updatePerPage } = usePagination()

const handleDelete = () => {
    window.location.reload()
}
</script>
