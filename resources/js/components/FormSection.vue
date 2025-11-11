<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-indigo-100 rounded-lg">
        <i :class="`fas fa-${icon} text-indigo-600 text-lg`"></i>
      </div>
      <h2 class="text-xl font-semibold text-gray-800">{{ title }}</h2>
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