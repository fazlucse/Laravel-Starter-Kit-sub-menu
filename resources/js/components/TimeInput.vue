<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      type="time"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  label: String,
  required: Boolean,
})
</script>