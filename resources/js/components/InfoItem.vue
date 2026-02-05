<template>
  <div class="flex items-start gap-2">
    <!-- Label + Colon -->
    <span
      class="font-medium text-gray-700 dark:text-gray-300 min-w-48"
      :class="{ 'min-w-0': $isMobile }"
    >
      {{ label }}:
    </span>

    <!-- Value -->
    <span class="text-gray-900 dark:text-gray-100 flex-1">
      <slot>{{ value ?? '—' }}</slot>
    </span>
  </div>
</template>

<script setup>
defineProps(['label', 'value'])

// Simple mobile detection (optional)
const $isMobile = window.innerWidth < 640
</script>

<style scoped>
/* Use plain CSS instead of @apply */
@media (max-width: 640px) {
  [data-v-scoped] .flex {
    flex-direction: column;
  }
  [data-v-scoped] .min-w-48 {
    min-width: 0 !important;
  }
}
</style>