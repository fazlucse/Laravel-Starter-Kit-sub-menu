<template>
  <AppLayout
    title="People"
    :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'People', href: '/people' }
    ]"
  >
    <div class="p-6 w-full mx-auto">

      <!-- Header + Controls -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">People Directory</h1>

        <div class="flex items-center gap-4">
          <!-- Per Page -->
          <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <span>Show</span>
            <select
              v-model="perPage"
              @change="updatePerPage"
              class="border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>per page</span>
          </div>

          <Link
            href="/people/create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition flex items-center gap-2"
          >
            <Plus class="w-4 h-4" />
            Add Person
          </Link>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">S.L</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Photo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Designation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">City</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="(person, index) in people?.data || []"
                :key="person.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
              >
                <!-- S.L -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  {{ (people.current_page - 1) * people.per_page + index + 1 }}
                </td>

                <!-- Avatar -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-300 dark:border-gray-600">
                    <img
                      v-if="person.avatar"
                      :src="`/storage/${person.avatar}`"
                      class="w-full h-full object-cover"
                      :alt="person.name"
                    />
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center text-white font-bold text-sm uppercase"
                      :class="avatarBgColor(person.name)"
                    >
                      {{ personInitials(person.name) }}
                    </div>
                  </div>
                </td>

                <!-- Name -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ person.name }}</div>
                </td>

                <!-- Designation -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-600 dark:text-gray-300">{{ person.designation || '—' }}</div>
                </td>

                <!-- Email -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-600 dark:text-gray-300">{{ person.email || '—' }}</div>
                </td>

                <!-- Phone -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-600 dark:text-gray-300">{{ person.phone || '—' }}</div>
                </td>

                <!-- City -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-600 dark:text-gray-300">{{ person.city || '—' }}</div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end gap-2">

                    <!-- View -->
                    <Link
                      :href="`/people/${person.id}`"
                      class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 transition"
                    >
                      <Eye class="w-4 h-4" />
                    </Link>

                    <!-- Edit -->
                    <Link
                      :href="`/people/${person.id}/edit`"
                      class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300 dark:hover:bg-yellow-800 transition"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>

                    <!-- Delete with Confirmation Dialog -->

                    <DeleteDialog
                        :url="`/people/${person.id}`"
                        record-name="Person"
                        @deleted="refreshPeople"
                        />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="!people?.data?.length" class="text-center py-12 text-gray-500 dark:text-gray-400">
            No people found.
            <Link href="/people/create" class="text-blue-600 underline">Add the first one</Link>.
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="people?.links?.length" class="mt-6">
          <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <!-- Previous -->
            <template v-if="people.links[0].url">
              <Link :href="people.links[0].url" v-html="people.links[0].label" preserve-scroll
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700" />
            </template>
            <template v-else>
              <span v-html="people.links[0].label"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-600" />
            </template>

            <!-- Pages -->
            <Link
              v-for="link in people.links.slice(1, -1)"
              :key="link.label"
              :href="link.url"
              v-html="link.label"
              preserve-scroll
              :disabled="!link.url"
              :class="[
                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                link.active
                  ? 'z-10 bg-blue-600 border-blue-600 text-white'
                  : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
              ]"
            />

            <!-- Next -->
            <template v-if="people.links[people.links.length - 1].url">
              <Link :href="people.links[people.links.length - 1].url" v-html="people.links[people.links.length - 1].label" preserve-scroll
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700" />
            </template>
            <template v-else>
              <span v-html="people.links[people.links.length - 1].label"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-600" />
            </template>
          </nav>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Eye, Edit, Trash2, Plus } from 'lucide-vue-next'
import DeleteDialog from '@/Components/DeleteDialog.vue'
// Shadcn UI
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

defineProps({
  people: { type: Object, required: true }
})

/* ---------- CSRF ----------
   Get from meta tag (Laravel default) */
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

/* ---------- Per Page ---------- */
const page = usePage()
const perPage = ref(Number(page.props?.value?.query?.per_page) || 10)

function updatePerPage() {
  router.get('/people', { per_page: perPage.value, page: 1 }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

watch(() => page.props?.value?.query?.per_page, (val) => {
  perPage.value = Number(val) || 10
})

/* ---------- Delete State ---------- */
const deleting = ref(false)
const deleteErrors = ref<Record<string, string>>({})
const passwordInput = ref<HTMLInputElement | null>(null)

async function submitDelete(e: Event) {
  const form = e.target as HTMLFormElement
  const formData = new FormData(form)

  deleting.value = true
  deleteErrors.value = {}

  try {
    await router.post(form.action, Object.fromEntries(formData.entries()), {
      preserveScroll: true,
      onError: (errors) => {
        deleteErrors.value = errors
        passwordInput.value?.focus()
      },
      onFinish: () => {
        deleting.value = false
      }
    })
  } catch {
    deleting.value = false
  }
}

/* ---------- Avatar Helpers ---------- */
function personInitials(name: string) {
  return name
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

function avatarBgColor(name: string) {
  const colors = ['bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-blue-500', 'bg-indigo-500', 'bg-purple-500', 'bg-pink-500']
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash % colors.length)]
}
function refreshPeople() {
//   router.reload({ only: ['people'] })
}
</script>