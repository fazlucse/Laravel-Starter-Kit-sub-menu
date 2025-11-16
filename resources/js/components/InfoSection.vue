<template>
  <div>
    <h5
      class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b-2 pb-1 inline-block"
      :style="{ borderColor: 'var(--color-primary-500)' }"
    >
      {{ title }}
    </h5>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-sm">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps(['title'])
</script>