<!-- resources/js/Pages/person/index.vue -->
<template>
  <AppLayout
    title="People"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'People', href: '/people' }
    ]"
  >
    <div class="flex flex-col h-[calc(100vh-5rem)]">

      <!-- Header + Controls -->
      <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">People</h1>

          <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
            <!-- SEARCH COMPONENT -->
                <!-- <SearchInput
                v-model="search"
                action="/people.index"
                :preserve="{ per_page: perPage.value }"
              /> -->
              <SearchPopup
                    v-model="search"
                    action="/people"
                    :preserve="{ per_page: perPage.value }"
                  />
            <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />
            <Link
              href="/people/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition flex items-center gap-2 w-full sm:w-auto justify-center"
            >
              <Plus class="w-4 h-4" />
              Add Person
            </Link>
          </div>
        </div>
      </div>

      <!-- Table / Cards -->
      <div class="flex-1 overflow-hidden bg-white dark:bg-gray-800">
        <!-- Desktop -->
        <div class="hidden sm:block h-full overflow-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">S.L</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Photo</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Designation</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">City</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(person, index) in people.data" :key="person.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  {{ (people.current_page - 1) * people.per_page + index + 1 }}
                </td>
                <td class="px-4 py-3">
                  <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-300 dark:border-gray-600">
                    <img v-if="person.avatar" :src="`/storage/${person.avatar}`" class="w-full h-full object-cover" :alt="person.name" />
                    <div v-else class="w-full h-full flex items-center justify-center text-white text-xs font-bold uppercase" :class="avatarBgColor(person.name)">
                      {{ personInitials(person.name) }}
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ person.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ person.designation || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ person.email || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ person.phone || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ person.city || '—' }}</td>
                <td class="px-4 py-3 text-right text-sm">
                  <div class="flex justify-end gap-1">
                    <Link :href="`/people/${person.id}`" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"><Eye class="w-4 h-4" /></Link>
                    <Link :href="`/people/${person.id}/edit`" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded transition"><Edit class="w-4 h-4" /></Link>
                    <DeleteDialog :url="`/people/${person.id}`" record-name="Person" @deleted="handleDelete" />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="!people?.data?.length" class="text-center py-12 text-gray-500 dark:text-gray-400">
            No people found. <Link href="/people/create" class="text-blue-600 underline">Add the first one</Link>.
          </div>
        </div>

        <!-- Mobile -->
        <div class="sm:hidden h-full overflow-y-auto">
          <div v-for="person in people.data" :key="person.id" class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full overflow-hidden border flex-shrink-0">
                <img v-if="person.avatar" :src="`/storage/${person.avatar}`" class="w-full h-full object-cover" :alt="person.name" />
                <div v-else class="w-full h-full flex items-center justify-center text-white text-sm font-bold" :class="avatarBgColor(person.name)">
                  {{ personInitials(person.name) }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 dark:text-white truncate">{{ person.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ person.designation || '—' }}</p>
              </div>
              <div class="flex gap-1">
                <Link :href="`/people/${person.id}`" class="p-2 text-blue-600"><Eye class="w-5 h-5" /></Link>
                <Link :href="`/people/${person.id}/edit`" class="p-2 text-yellow-600"><Edit class="w-5 h-5" /></Link>
                <DeleteDialog :url="`/people/${person.id}`" record-name="Person" @deleted="handleDelete" />
              </div>
            </div>
            <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 space-y-1">
              <div><strong>Email:</strong> {{ person.email || '—' }}</div>
              <div><strong>Phone:</strong> {{ person.phone || '—' }}</div>
              <div><strong>City:</strong> {{ person.city || '—' }}</div>
            </div>
          </div>
          <div v-if="!people?.data?.length" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No people found. <Link href="/people/create" class="text-blue-600 underline">Add the first one</Link>.
          </div>
        </div>
      </div>

      <Pagination :links="people.links" />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from '@/Components/custom/SearchPopup.vue'  // ← NEW
import PerPageSelect from '@/Components/custom/PerPageSelect.vue'
import Pagination from '@/Components/custom/Pagination.vue'
import DeleteDialog from '@/Components/custom/DeleteDialog.vue'
import { Link, router } from '@inertiajs/vue3'
import { Eye, Edit, Plus } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'
import { useToast } from '@/composables/useToast'
import { ref } from 'vue'

defineProps<{
  people: {
    data: any[]
    current_page: number
    per_page: number
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  perPage: number
  defaultPerPage: number
}>()

const { perPage, update: updatePerPage } = usePagination()
const { success, error } = useToast()

// SEARCH TERM (bound to SearchInput)
const search = ref('')

/* ---------- HELPERS ---------- */
const personInitials = (name: string) =>
  name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() || '??'

const avatarBgColor = (name: string) => {
  const colors = ['bg-red-500','bg-yellow-500','bg-green-500','bg-blue-500','bg-indigo-500','bg-purple-500','bg-pink-500']
  let hash = 0
  for (let i = 0; i < (name?.length ?? 0); i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colors[Math.abs(hash % colors.length)]
}

function handleDelete({ success: ok, message }: { success: boolean; message?: string }) {
  ok ? success(message ?? 'Person deleted.') : error('Failed to delete.')
  router.reload({ only: ['people'], preserveScroll: true })
}
</script>

<style scoped>
nav { gap: 0.25rem; }
</style>