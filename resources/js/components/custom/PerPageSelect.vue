<template>
  <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
    <span>Show</span>

    <select
      :value="modelValue"
      @change="$emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
      class="border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
    >
      <option
        v-for="n in options"
        :key="n"
        :value="n"
        :selected="n === modelValue"
      >
        {{ n }}
      </option>
    </select>

    <span>per page</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

// Fix: Use defineProps() to access props
const props = defineProps<{
  modelValue: number
  options?: number[]
}>()

defineEmits<{
  (e: 'update:modelValue', value: number): void
}>()

// Fix: Use props.options, not props.options directly in computed
const defaultOptions = [10, 25, 50, 100, 200, 300, 500, 1000] as const
const options = computed(() => props.options ?? defaultOptions)
</script>