<!-- resources/js/Pages/person/index.vue -->
<template>
  <AppLayout title="People" :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'People', href: '/people' }]">
    <div class="flex flex-col h-[calc(100vh-5rem)]">
      <!-- Header -->
      <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">People</h1>
          <div class="flex items-center gap-3 w-full sm:w-auto">
            <SearchPopup v-model="search" action="/people.index" :preserve="{ per_page: perPage }" @select="onPersonSelected" />
            <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />
            <Link href="/people/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
              <Plus class="w-4 h-4" />
              <span class="hidden sm:inline">Add Person</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Selected -->
      <div v-if="selectedPerson" class="px-6 py-2 bg-green-50 dark:bg-green-900/30 text-sm text-green-800 dark:text-green-200">
        Selected: <strong>{{ selectedPerson.name }}</strong>
      </div>

      <!-- Table / Cards -->
      <div class="flex-1 overflow-hidden bg-white dark:bg-gray-800">
        <!-- Desktop Table -->
        <div class="hidden sm:block h-full overflow-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">S.L</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Photo</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Designation</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phone</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">City</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(p, i) in people.data" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm">{{ (people.current_page - 1) * people.per_page + i + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="w-10 h-10 rounded-full overflow-hidden border">
                    <img v-if="p.avatar" :src="`/storage/${p.avatar}`" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-white text-xs font-bold" :class="bgColor(p.name)">
                      {{ initials(p.name) }}
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm font-medium">{{ p.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ p.designation || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ p.email || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ p.phone || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ p.city || '—' }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex justify-end gap-1">
                    <Link :href="`/people/${p.id}`" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded"><Eye class="w-4 h-4" /></Link>
                    <Link :href="`/people/${p.id}/edit`" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded"><Edit class="w-4 h-4" /></Link>
                    <DeleteDialog :url="`/people/${p.id}`" record-name="Person" @deleted="handleDelete" />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="!people.data.length" class="text-center py-12 text-gray-500">
            No people found. <Link href="/people/create" class="text-blue-600 underline">Add one</Link>.
          </div>
        </div>

        <!-- Mobile Cards -->
        <div class="sm:hidden h-full overflow-y-auto">
          <div v-for="p in people.data" :key="p.id" class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full overflow-hidden border flex-shrink-0">
                <img v-if="p.avatar" :src="`/storage/${p.avatar}`" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-white text-sm font-bold" :class="bgColor(p.name)">
                  {{ initials(p.name) }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium truncate">{{ p.name }}</p>
                <p class="text-sm text-gray-500">{{ p.designation || '—' }}</p>
              </div>
              <div class="flex gap-1">
                <Link :href="`/people/${p.id}`" class="p-2 text-blue-600"><Eye class="w-5 h-5" /></Link>
                <Link :href="`/people/${p.id}/edit`" class="p-2 text-yellow-600"><Edit class="w-5 h-5" /></Link>
                <DeleteDialog :url="`/people/${p.id}`" record-name="Person" @deleted="handleDelete" />
              </div>
            </div>
            <div class="mt-3 text-sm text-gray-600 space-y-1">
              <div><strong>Email:</strong> {{ p.email || '—' }}</div>
              <div><strong>Phone:</strong> {{ p.phone || '—' }}</div>
              <div><strong>City:</strong> {{ p.city || '—' }}</div>
            </div>
          </div>
          <div v-if="!people.data.length" class="text-center py-8 text-gray-500">
            No people found. <Link href="/people/create" class="text-blue-600 underline">Add one</Link>.
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <Pagination :links="people.links" />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchPopup from '@/Components/custom/SearchPopup.vue'
import PerPageSelect from '@/Components/custom/PerPageSelect.vue'
import Pagination from '@/Components/custom/Pagination.vue'
import DeleteDialog from '@/Components/custom/DeleteDialog.vue'
import { Link, router } from '@inertiajs/vue3'
import { Eye, Edit, Plus } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'
import { useToast } from '@/composables/useToast'
import { ref } from 'vue'

defineProps<{
  people: { data: any[]; current_page: number; per_page: number; links: any[] }
  perPage: number
  defaultPerPage: number
}>()

const { perPage, update: updatePerPage } = usePagination()
const { success, error } = useToast()
const search = ref('')
const selectedPerson = ref<any>(null)

const onPersonSelected = (p: any) => {
  selectedPerson.value = p
  search.value = p.name
}

const initials = (name: string) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() || '??'

const bgColor = (name: string) => {
  const colors = ['bg-red-500','bg-yellow-500','bg-green-500','bg-blue-500','bg-indigo-500','bg-purple-500','bg-pink-500']
  let h = 0
  for (let i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h)
  return colors[Math.abs(h % colors.length)]
}

const handleDelete = ({ success: ok }: { success: boolean }) => {
  if (ok) {
    // Show toast AFTER the reload completes
    router.reload({
      only: ['people'],
      onSuccess: () => {
        success('Deleted.')
      },
      onError: () => {
        error('Failed to delete.')
      }
    })
  } else {
    error('Failed.')
  }
}
</script>