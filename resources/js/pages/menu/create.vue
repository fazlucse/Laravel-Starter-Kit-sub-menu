<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'

// Props from Laravel
const { props } = usePage()
const menu = props.menu || null
const parents = props.parents || []

// Form state
const form = ref({
  name: menu?.name || '',
  route: menu?.route || '',
  icon: menu?.icon || '',
  order: menu?.order || 0,
  parent_id: menu?.parent_id || null,
})

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Menus', href: '/menus' },
  { title: menu ? 'Edit Menu' : 'Create Menu', href: menu ? `/menus/${menu.id}/edit` : '/menus/create' },
]

// Submit form
function submit() {
  if (menu) {
    router.put(`/menus/${menu.id}`, form.value)
  } else {
    router.post('/menus', form.value)
  }
}
</script>

<template>
  <Head :title="menu ? 'Edit Menu' : 'Create Menu'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 flex flex-col gap-6">

      <h1 class="text-2xl font-bold">{{ menu ? 'Edit Menu' : 'Create Menu' }}</h1>

      <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 border border-sidebar-border/70 dark:border-sidebar-border">
        <form @submit.prevent="submit" class="flex flex-col gap-4">

          <div>
            <label class="block mb-1 font-medium">Name</label>
            <input
              v-model="form.name"
              required
              class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium">Route</label>
            <input
              v-model="form.route"
              class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium">Icon</label>
            <input
              v-model="form.icon"
              class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium">Order</label>
            <input
              type="number"
              v-model="form.order"
              class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium">Parent Menu</label>
            <select
              v-model="form.parent_id"
              class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-white"
            >
              <option :value="null">None</option>
              <option v-for="parent in parents" :key="parent.id" :value="parent.id">{{ parent.name }}</option>
            </select>
          </div>

          <div class="flex gap-2 mt-4">
            <button
              type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded"
            >
              {{ menu ? 'Update' : 'Create' }}
            </button>

            <Link
              href="/menus"
              class="bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded"
            >
              Cancel
            </Link>
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>
