<template>
  <AppLayout
    title="Employees"
    :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'Employees', href: '/employees' }]"
  >
    <div class="flex flex-col h-[calc(100vh-5rem)]">
      <!-- Header -->
      <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Employees</h1>
          <div class="flex items-center gap-3 w-full sm:w-auto">
            <SearchPopup v-model="search" action="/employees.index" :preserve="{ per_page: perPage }" />
            <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />
            <Link v-if="canCreate" href="/employees/create"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
              <Plus class="w-4 h-4" />
              <span class="hidden sm:inline">Add Employee</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="flex-1 overflow-hidden bg-white dark:bg-gray-800">
        <div class="hidden sm:block h-full overflow-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">S.L</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Person Id</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Person Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Employee Code</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Department</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Designation</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Company</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(e, i) in employees.data" :key="e.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm">{{ (employees.current_page - 1) * employees.per_page + i + 1 }}</td>
               <td class="px-4 py-3 text-right">
                  <div class="flex justify-end gap-1">
                    <Link v-if="canEdit" :href="`/employees/${e.id}/edit`" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded transition">
                      <Edit class="w-4 h-4" />
                    </Link>
                    <DeleteDialog v-if="canDelete" :url="`/employees/${e.id}`" record-name="Employee" @deleted="handleDelete" />
                  </div>
                </td>
                <td class="px-4 py-3 text-sm font-medium">{{ e.person_id }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ e.person_name }}</td>
                <td class="px-4 py-3 text-sm">{{ e.employee_code }}</td>
                <td class="px-4 py-3 text-sm">{{ e.department_name || '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ e.designation_name || '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ e.company_name || '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ e.employee_status }}</td>
               
              </tr>
              <tr v-if="!employees.data.length">
                <td colspan="8" class="text-center py-4 text-gray-500">No employees found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination :links="employees.links" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchPopup from '@/Components/custom/SearchPopup.vue'
import PerPageSelect from '@/Components/custom/PerPageSelect.vue'
import Pagination from '@/Components/custom/Pagination.vue'
import DeleteDialog from '@/Components/custom/DeleteDialog.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Plus, Edit } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { usePagination } from '@/composables/usePagination'

const employees = defineModel('employees', { required: true }) as any
const perPageProp = defineModel('perPage', { required: true }) as any
const { perPage, update: updatePerPage } = usePagination()

const { authUser } = usePage().props
const canCreate = computed(() => authUser?.permissions.includes('employee.create') ?? false)
const canEdit = computed(() => authUser?.permissions.includes('employee.edit') ?? false)
const canDelete = computed(() => authUser?.permissions.includes('employee.delete') ?? false)

const search = ref('')

const handleDelete = ({ success }: { success: boolean }) => {
  if (success) {
    window.location.reload()
  }
}
</script>
