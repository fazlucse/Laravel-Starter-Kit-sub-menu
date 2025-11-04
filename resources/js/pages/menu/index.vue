<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, onMounted } from 'vue';

// ---------------------------------------------------------------
// 1. Page props
// ---------------------------------------------------------------
const page = usePage<{
  menus: any[];
  success?: string;
  flash?: { success?: string };
}>();
const menus = ref(page.props.menus ?? []);
const successMessage = ref(page.props.success ?? page.props.flash?.success ?? '');

// ---------------------------------------------------------------
// 2. Loading state (global Inertia progress)
// ---------------------------------------------------------------
const loading = ref(true);

router.on('start', () => (loading.value = true));
router.on('finish', () => (loading.value = false));

watch(
  () => page.props.menus,
  (newMenus) => {
    menus.value = newMenus ?? [];
    loading.value = false;
  },
  { immediate: true }
);

// ---------------------------------------------------------------
// 3. Success alert – WITH CLOSE BUTTON
// ---------------------------------------------------------------
const showSuccess = ref(!!successMessage.value);
let autoHideTimeout: ReturnType<typeof setTimeout> | null = null;

const hideSuccess = () => {
  showSuccess.value = false;
  if (autoHideTimeout) {
    clearTimeout(autoHideTimeout);
    autoHideTimeout = null;
  }
};

const startAutoHide = () => {
  autoHideTimeout = setTimeout(() => {
    hideSuccess();
  }, 3000);
};

// Initial show
onMounted(() => {
  if (showSuccess.value) {
    startAutoHide();
  }
});

// Watch for new flash messages
watch(
  () => page.props.flash?.success,
  (msg) => {
    if (msg) {
      successMessage.value = msg;
      showSuccess.value = true;
      startAutoHide();
    }
  }
);

// ---------------------------------------------------------------
// 4. Breadcrumbs & Delete
// ---------------------------------------------------------------
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Menus', href: '/menus' },
];

function destroy(id: number) {
  if (!confirm('Are you sure you want to delete this menu?')) return;

  router.delete(`/menus/${id}`, {
    onSuccess: () => {
      menus.value = menus.value.filter((m) => m.id !== id);
    },
    onError: () => {
      alert('Failed to delete menu');
    },
  });
}
</script>

<template>
  <Head title="Menus" />

  <!-- ==================== SUCCESS ALERT – WITH CLOSE ICON ==================== -->
  <teleport to="body">
    <transition
      enter-active-class="transition ease-out duration-300 transform"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition ease-in duration-200 transform"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
      <div
        v-if="showSuccess"
        class="fixed top-4 right-4 z-50 max-w-sm w-full bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg flex items-center justify-between space-x-3"
      >
        <div class="flex items-center space-x-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span class="font-medium">{{ successMessage }}</span>
        </div>

        <!-- CLOSE ICON (cross) -->
        <button
          @click="hideSuccess"
          class="text-white hover:text-gray-200 focus:outline-none transition"
          aria-label="Close alert"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </transition>
  </teleport>

  <!-- ==================== MAIN CONTENT ==================== -->
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 flex flex-col gap-4">
      <!-- Add Menu -->
      <div>
        <Link
          href="/menus/create"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-block transition"
        >
          Add Menu
        </Link>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <table class="min-w-full border-collapse border border-gray-300">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="border p-2 text-left">Name</th>
              <th class="border p-2 text-left">Route</th>
              <th class="border p-2 text-left">Icon</th>
              <th class="border p-2 text-left">Order</th>
              <th class="border p-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="menu in menus" :key="menu.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="border p-2">{{ menu.name }}</td>
              <td class="border p-2">{{ menu.route }}</td>
              <td class="border p-2">{{ menu.icon }}</td>
              <td class="border p-2">{{ menu.order }}</td>
              <td class="border p-2">
                <Link
                  :href="`/menus/${menu.id}/edit`"
                  class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded mr-2 transition"
                >
                  Edit
                </Link>
                <button
                  @click="destroy(menu.id)"
                  class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="menus.length === 0">
              <td colspan="5" class="text-center p-4 text-gray-500 dark:text-gray-400">
                No menus found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>