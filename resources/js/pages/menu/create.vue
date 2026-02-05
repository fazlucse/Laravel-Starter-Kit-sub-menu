<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

defineProps({
  people: Object
})

const deletingId = ref(null)

// === PER PAGE: Read from URL safely ===
const urlParams = new URLSearchParams(window.location.search)
const perPage = ref(urlParams.get('per_page') || '10')

// === Update URL when dropdown changes ===
function updatePerPage() {
  const params = new URLSearchParams(window.location.search)
  params.set('per_page', perPage.value)
  params.delete('page') // Reset to page 1 when changing per_page

  router.get(
    `${window.location.pathname}?${params.toString()}`,
    {},
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

// === Keep dropdown in sync when navigating (back/forward, pagination) ===
watch(
  () => window.location.search,
  () => {
    const params = new URLSearchParams(window.location.search)
    const newPerPage = params.get('per_page') || '10'
    if (perPage.value !== newPerPage) {
      perPage.value = newPerPage
    }
  }
)

// === Delete ===
function confirmDelete(id) {
  if (confirm('Are you sure you want to delete this person?')) {
    deletingId.value = id
    router.delete(`/people/${id}`, {
      onFinish: () => (deletingId.value = null),
      preserveScroll: true,
    })
  }
}

// === Avatar Helpers ===
function personInitials(name) {
  return name
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

function avatarBgColor(name) {
  const colors = [
    'bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-blue-500',
    'bg-indigo-500', 'bg-purple-500', 'bg-pink-500'
  ]
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash % colors.length)]
}
</script>
<template>
  <Head :title="menu ? 'Edit Menu' : 'Create Menu'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-8 border border-sidebar-border/70 dark:border-sidebar-border max-w-3xl mx-auto">

        <form @submit.prevent="submit" class="space-y-6">

          <!-- Name -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <label class="text-right font-medium text-gray-700 dark:text-gray-300 pt-2">
              Name <span class="text-red-500">*</span>
            </label>
            <div class="md:col-span-2">
              <input
                v-model="form.name"
                required
                :class="[
                  'w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
                  form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                  'dark:bg-gray-700 dark:text-white'
                ]"
                placeholder="Enter menu name"
              />
              <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
              </p>
            </div>
          </div>

          <!-- Route -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <label class="text-right font-medium text-gray-700 dark:text-gray-300 pt-2">
              Route
            </label>
            <div class="md:col-span-2">
              <input
                v-model="form.route"
                :class="[
                  'w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
                  form.errors.route ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                  'dark:bg-gray-700 dark:text-white'
                ]"
                placeholder="e.g. /settings"
              />
              <p v-if="form.errors.route" class="text-red-500 text-sm mt-1">
                {{ form.errors.route }}
              </p>
            </div>
          </div>

          <!-- Icon -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <label class="text-right font-medium text-gray-700 dark:text-gray-300 pt-2">
              Icon
            </label>
            <div class="md:col-span-2">
              <input
                v-model="form.icon"
                :class="[
                  'w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
                  form.errors.icon ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                  'dark:bg-gray-700 dark:text-white'
                ]"
                placeholder="e.g. settings"
              />
              <p v-if="form.errors.icon" class="text-red-500 text-sm mt-1">
                {{ form.errors.icon }}
              </p>
            </div>
          </div>

          <!-- Order -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <label class="text-right font-medium text-gray-700 dark:text-gray-300 pt-2">
              Order
            </label>
            <div class="md:col-span-2">
              <input
                type="number"
                v-model.number="form.order"
                :class="[
                  'w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
                  form.errors.order ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                  'dark:bg-gray-700 dark:text-white'
                ]"
                min="0"
              />
              <p v-if="form.errors.order" class="text-red-500 text-sm mt-1">
                {{ form.errors.order }}
              </p>
            </div>
          </div>

          <!-- Parent Menu -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            <label class="text-right font-medium text-gray-700 dark:text-gray-300 pt-2">
              Parent Menu
            </label>
            <div class="md:col-span-2">
              <select
                v-model="form.parent_id"
                :class="[
                  'w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
                  form.errors.parent_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
                  'dark:bg-gray-700 dark:text-white'
                ]"
              >
                <option :value="null">None</option>
                <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                  {{ parent.name }}
                </option>
              </select>
              <p v-if="form.errors.parent_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.parent_id }}
              </p>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end gap-3 mt-8">
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium px-5 py-2 rounded-md transition flex items-center gap-2"
            >
              <span v-if="form.processing" class="animate-spin">Loading</span>
              {{ menu ? 'Update' : 'Create' }}
            </button>
            <Link
              href="/menus"
              class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-medium px-5 py-2 rounded-md transition"
            >
              Cancel
            </Link>
          </div>

          <!-- Global Error (if any) -->
          <div v-if="form.hasErrors && !Object.keys(form.errors).length" class="text-red-500 text-sm">
            Please fix the errors above.
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>
