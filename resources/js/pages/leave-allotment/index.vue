<template>
  <AppLayout
    title="Leave Allotment"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Leave Allotment', href: '/leave-allotment' }
    ]"
  >
    <div class="flex flex-col h-[calc(100vh-5rem)]">

      <!-- Header -->
      <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Leave Allotment</h1>

          <div class="flex items-center gap-3 w-full sm:w-auto">
            <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />

            <Link
              v-if="canCreate"
              href="/leave-allotments/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
            >
              <Plus class="w-4 h-4" />
              <span class="hidden sm:inline">Add Leave Allotment</span>
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
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Photo</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Employee</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Person ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Designation</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Department</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Division</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Annual Leave</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Casual Leave</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Sick Leave</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Maternal Leave</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Paternal Leave</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Earned Leave</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">LWP</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Remarks</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Actions</th>
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="(l, i) in leaveAllotment.data" :key="l.id" class="hover:bg-gray-100 dark:hover:bg-gray-800">
              <td class="px-4 py-2 text-sm">{{ (leaveAllotment.current_page - 1) * leaveAllotment.per_page + i + 1 }}</td>
              <td class="px-4 py-2">
                <Avatar :src="l.photo" :name="l.name || 'Unknown'" class="w-10 h-10" />
              </td>
              <td class="px-4 py-2 text-sm">{{ l.name }}</td>
              <td class="px-4 py-2 text-sm">{{ l.person_id }}</td>
              <td class="px-4 py-2 text-sm">{{ l.designation_name }}</td>
              <td class="px-4 py-2 text-sm">{{ l.department_name }}</td>
              <td class="px-4 py-2 text-sm">{{ l.division_name }}</td>
              <td class="px-4 py-2 text-sm">{{ l.annual_utilized }}/{{ l.annual_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.casual_utilized }}/{{ l.casual_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.sick_utilized }}/{{ l.sick_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.maternal_utilized }}/{{ l.maternal_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.paternal_utilized }}/{{ l.paternal_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.earn_utilized }}/{{ l.earn_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.lwp_utilized }}/{{ l.lwp_allotment }}</td>
              <td class="px-4 py-2 text-sm">{{ l.remarks }}</td>
              <td class="px-4 py-2 text-right">
                <DeleteDialog
                  v-if="canDelete"
                  :url="`/leave-allotment/${l.id}`"
                  record-name="Leave Allotment"
                  @deleted="handleDelete"
                />
              </td>
            </tr>

            <tr v-if="!leaveAllotment.data.length">
              <td colspan="16" class="text-center py-4 text-gray-500 dark:text-gray-400">No leave allotment found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="leaveAllotment.links" />
    </div>
  </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/custom/Pagination.vue'
import PerPageSelect from '@/Components/custom/PerPageSelect.vue'
import DeleteDialog from '@/Components/custom/DeleteDialog.vue'
import Avatar from '@/Components/custom/Avatar.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import { computed } from 'vue'
import { usePagination } from '@/composables/usePagination'

const leaveAllotment = defineModel('leaveAllotment', { required: true }) as any
const { perPage, update: updatePerPage } = usePagination()

const { authUser } = usePage().props
console.log('authUser:', authUser)
console.log('permissions:', authUser?.permissions)
const canCreate = computed(() => authUser?.permissions?.includes('leave-allotment.create') ?? false)
const canDelete = computed(() => authUser?.permissions?.includes('leave-allotment.delete') ?? false)
const handleDelete = ({ success }: { success: boolean }) => {
  if (success) window.location.reload()
}
</script>
