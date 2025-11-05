<script setup lang="ts">
import { ref, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);
let hideTimer: ReturnType<typeof setTimeout> | null = null;

/* ────────────────────── SHOW ────────────────────── */
const show = () => {
  if (hideTimer) clearTimeout(hideTimer);
  isLoading.value = true;
};

/* ────────────────────── HIDE ────────────────────── */
const hide = () => {
  hideTimer = setTimeout(() => (isLoading.value = false), 80);
};

/* ────────────────────── INERTIA EVENTS ────────────────────── */
router.on('start', show);
router.on('finish', hide);
router.on('success', hide);
router.on('error', hide);

/* ────────────────────── PAGINATION (preserveState) ────────────────────── */
router.on('before', (visit) => {
  if (!visit) return;
  const url = new URL(visit.url, window.location.origin);
  if (visit.method?.toLowerCase() === 'get' && url.searchParams.has('page')) {
    show();
  }
});

/* ────────────────────── CLEANUP ────────────────────── */
onBeforeUnmount(() => {
  if (hideTimer) clearTimeout(hideTimer);
});
</script>

<template>
  <teleport to="body">
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isLoading"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/30 dark:bg-black/50 backdrop-blur-sm pointer-events-none"
      >
        <!-- MODERN DUAL-RING SPINNER -->
        <div class="relative w-16 h-16">
          <!-- Outer Ring -->
        <div
  class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-400 border-l-blue-300 animate-spin"
  style="animation-duration: 1.2s; animation-timing-function: linear;"
></div>

<!-- Inner Ring (reverse spin, deeper blue) -->
<div
  class="absolute inset-2 rounded-full border-4 border-transparent border-b-blue-600 border-r-blue-500 animate-spin"
  style="animation-duration: 1.8s; animation-direction: reverse; animation-timing-function: linear;"
></div>
          <!-- Center Glow -->
          <div
            class="absolute inset-4 rounded-full bg-white/20 blur-md animate-pulse"
          ></div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<style scoped>
/* Optional: Add a subtle pulse to the whole loader */
@keyframes pulse {
  0%, 100% { opacity: 0.9; }
  50% { opacity: 1; }
}
</style>