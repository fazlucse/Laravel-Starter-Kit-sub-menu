<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md dark:hover:shadow-lg transition-shadow duration-200"
  >
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
        <i :class="`fas fa-${icon} text-indigo-600 dark:text-indigo-400 text-lg`"></i>
      </div>
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
        {{ title }}
      </h2>
    </div>
    <div class="space-y-6">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  icon: { type: String, default: 'circle' },
})
</script>