<template>
  <div v-bind="$attrs">
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
      {{ label }}
    </dt>
    <dd :class="ddClasses">
      <span
        v-if="badge"
        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100"
      >
        {{ value }}
      </span>
      <span v-else-if="strong" class="font-semibold text-blue-600 dark:text-blue-400">
        {{ value }}
      </span>
      <span v-else>{{ value }}</span>
    </dd>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  label: string
  value?: string | number | null
  badge?: boolean
  strong?: boolean
}>()

const ddClasses = computed(() => {
  const base = 'mt-1'
  if (props.strong) return `${base} text-lg font-semibold text-blue-600 dark:text-blue-400`
  return `${base} text-gray-700 dark:text-gray-300`
})
</script>