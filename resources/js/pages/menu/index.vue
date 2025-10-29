<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { props } = usePage();
const menus = ref(props.menus || []);

// Breadcrumbs for navigation
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Menus', href: '/menus' },
];

// Delete menu function
function destroy(id: number) {
  if (confirm('Are you sure you want to delete this menu?')) {
    router.delete(`/menus/${id}`, {
      onSuccess: () => {
        menus.value = menus.value.filter(menu => menu.id !== id);
      },
      onError: (errors) => {
        alert('Failed to delete menu');
        console.error(errors);
      }
    });
  }
}
</script>

<template>
  <Head title="Menus" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 flex flex-col gap-4">

      <!-- Add Menu Button -->
      <div>
        <Link
          href="/menus.create"
          class="bg-blue-600 text-white px-4 py-2 rounded inline-block"
        >
          Add Menu
        </Link>
      </div>

      <!-- Menus Table -->
      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <table class="min-w-full border-collapse border border-gray-300">
          <thead class="bg-gray-100">
            <tr>
              <th class="border p-2 text-left">Name</th>
              <th class="border p-2 text-left">Route</th>
              <th class="border p-2 text-left">Icon</th>
              <th class="border p-2 text-left">Order</th>
              <th class="border p-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="menu in menus" :key="menu.id" class="hover:bg-gray-50">
              <td class="border p-2">{{ menu.name }}</td>
              <td class="border p-2">{{ menu.route }}</td>
              <td class="border p-2">{{ menu.icon }}</td>
              <td class="border p-2">{{ menu.order }}</td>
              <td class="border p-2">
                <Link
                  :href="`/menus/${menu.id}/edit`"
                  class="bg-yellow-500 text-white px-2 py-1 rounded mr-2"
                >
                  Edit
                </Link>
                <button
                  @click="destroy(menu.id)"
                  class="bg-red-600 text-white px-2 py-1 rounded"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="menus.length === 0">
              <td colspan="5" class="text-center p-4 text-gray-500">No menus found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
