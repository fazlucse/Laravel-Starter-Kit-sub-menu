<template>
  <div class="flex items-center space-x-2">
    <input
      :checked="modelValue"
      @change="$emit('update:modelValue', $event.target.checked ? 1 : 0)"
      type="checkbox"
      class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
    />
    <label class="text-sm text-gray-700">{{ label }}</label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [Number, Boolean],
  label: String,
})
</script>