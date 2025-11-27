<template>
  <AppLayout
    title="Attendance"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Attendance', href: '/attendance' }
    ]"
  >
    <div class="flex flex-col h-[calc(100vh-5rem)]">

      <!-- Header -->
      <div class="p-2 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Attendance</h1>

          <div class="flex items-center gap-3 w-full sm:w-auto">
            <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />

            <Link
              v-if="canCreate"
              href="/attendance/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
            >
              <Plus class="w-4 h-4" />
              <span class="hidden sm:inline">Add Attendance</span>
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
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Person ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Employee ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Name</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Designation</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Department</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Division</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Date</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Office In</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Office Out</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Emp In</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Emp Out</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Early In</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Late In</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Early Out</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Late Out</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Status</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Actions</th>
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="(a, i) in attendance.data" :key="a.id" class="hover:bg-gray-100 dark:hover:bg-gray-800">
              <td class="px-4 py-2 text-sm">{{ (attendance.current_page - 1) * attendance.per_page + i + 1 }}</td>
              <td class="px-4 py-2">
                <Avatar :src="a.employee?.photo" :name="a.employee?.person_name || 'Unknown'" class="w-10 h-10" />
              </td>
              <td class="px-4 py-2 text-sm">{{ a.employee?.person_id }}</td>
              <td class="px-4 py-2 text-sm">{{ a.employee?.employee_id }}</td>
              <td class="px-4 py-2 text-sm">{{ a.employee?.person_name }}</td>
              <td class="px-4 py-2 text-sm">{{ a.employee?.designation_name }}</td>
              <td class="px-4 py-2 text-sm">{{ a.employee?.department_name }}</td>
              <td class="px-4 py-2 text-sm">{{ a.employee?.division_name }}</td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">{{ a.add_time ? formatDate(a.add_time) : '—' }}</td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">{{ formatTime(a.office_in_time) }}</td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">{{ formatTime(a.office_out_time) }}</td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">{{ formatTime(a.emp_in_time) }}</td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">{{ formatTime(a.emp_out_time) }}</td>
              <!-- Early / Late In / Out -->
              <td class="px-4 py-2 text-sm whitespace-nowrap">
                {{ getTimeDiff(a.office_in_time, a.emp_in_time) < 0 ? formatDiff(getTimeDiff(a.office_in_time, a.emp_in_time)) : '—' }}
              </td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">
                {{ getTimeDiff(a.office_in_time, a.emp_in_time) > 0 ? formatDiff(getTimeDiff(a.office_in_time, a.emp_in_time)) : '—' }}
              </td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">
                {{ getTimeDiff(a.office_out_time, a.emp_out_time) < 0 ? formatDiff(getTimeDiff(a.office_out_time, a.emp_out_time)) : '—' }}
              </td>
              <td class="px-4 py-2 text-sm whitespace-nowrap">
                {{ getTimeDiff(a.office_out_time, a.emp_out_time) > 0 ? formatDiff(getTimeDiff(a.office_out_time, a.emp_out_time)) : '—' }}
              </td>
              <td class="px-4 py-2 text-sm">{{ a.status }}</td>
              <td class="px-4 py-2 text-right">
                <DeleteDialog
                  v-if="canDelete"
                  :url="`/attendance/${a.id}`"
                  record-name="Attendance"
                  @deleted="handleDelete"
                />
              </td>
            </tr>

            <tr v-if="!attendance.data.length">
              <td colspan="20" class="text-center py-4 text-gray-500 dark:text-gray-400">No attendance found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="attendance.links" />
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

const attendance = defineModel('attendance', { required: true }) as any
const { perPage, update: updatePerPage } = usePagination()

const { authUser } = usePage().props
console.log('authUser.permissions:', authUser?.permissions)

const canDelete = computed(() => authUser?.permissions?.includes('attendance.delete') ?? false)
const canCreate = computed(() => authUser?.permissions?.includes('attendance.create') ?? false)
const handleDelete = ({ success }: { success: boolean }) => {
  if (success) window.location.reload()
}

function formatDate(dateStr: string) {
  const date = new Date(dateStr)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}-${month}-${year}`
}

function formatTime(timeStr: string | null) {
  if (!timeStr) return '—'
  const [hour, minute] = timeStr.split(':').map(Number)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 === 0 ? 12 : hour % 12
  return `${hour12}:${minute.toString().padStart(2, '0')} ${ampm}`
}

// Compute time difference in minutes (positive = late, negative = early)
function getTimeDiff(officeTime: string | null, empTime: string | null) {
  if (!officeTime || !empTime) return 0
  const [oh, om] = officeTime.split(':').map(Number)
  const [eh, em] = empTime.split(':').map(Number)
  const officeMinutes = oh * 60 + om
  const empMinutes = eh * 60 + em
  return empMinutes - officeMinutes
}

// Format minutes into h/m string
function formatDiff(diff: number) {
  if (diff === 0) return '0'
  const absDiff = Math.abs(diff)
  const h = Math.floor(absDiff / 60)
  const m = absDiff % 60
  return `${h > 0 ? h + 'h ' : ''}${m}m`
}
</script>
