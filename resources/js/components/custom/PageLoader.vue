<script setup lang="ts">
import { ref, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);
let hideTimer: ReturnType<typeof setTimeout> | null = null;

const show = () => {
  hideTimer && clearTimeout(hideTimer);
  isLoading.value = true;
};

const hide = () => {
  hideTimer = setTimeout(() => (isLoading.value = false), 80);
};

['start', 'finish', 'success', 'error'].forEach(event => {
  router.on(event, event === 'start' ? show : hide);
});

router.on('before', visit => {
  if (visit?.method?.toLowerCase() === 'get' && new URL(visit.url, window.location.origin).searchParams.has('page')) {
    show();
  }
});

onBeforeUnmount(() => hideTimer && clearTimeout(hideTimer));
</script>

<template>
  <teleport to="body">
    <transition
      enter-active-class="transition-opacity duration-300"
      leave-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isLoading"
        class="fixed inset-0 flex items-center justify-center pointer-events-none"
      >
        <!-- Fancier loader card -->
        <div class="relative w-32 h-32 flex items-center justify-center">
          <!-- Outer glowing ring -->
          <div class="absolute inset-0 rounded-full border-4 border-t-blue-400 border-l-blue-300 animate-spin-glow shadow-[0_0_20px_rgba(59,130,246,0.5)]"></div>
          <!-- Inner reverse ring -->
          <div class="absolute inset-6 rounded-full border-4 border-b-blue-600 border-r-blue-500 animate-spin-reverse shadow-[0_0_15px_rgba(37,99,235,0.4)]"></div>
          <!-- Center pulsating orb -->
          <div class="absolute inset-12 rounded-full bg-blue-400/30 blur-md animate-pulse-large"></div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<style scoped>
/* Normal spin */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.animate-spin-glow {
  animation: spin 1.2s linear infinite;
}

/* Reverse spin */
@keyframes spin-reverse {
  0% { transform: rotate(360deg); }
  100% { transform: rotate(0deg); }
}
.animate-spin-reverse {
  animation: spin-reverse 1.8s linear infinite;
}

/* Large pulsing center orb */
@keyframes pulse-large {
  0%, 100% { opacity: 0.7; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.2); }
}
.animate-pulse-large {
  animation: pulse-large 1.5s ease-in-out infinite;
}
</style>
