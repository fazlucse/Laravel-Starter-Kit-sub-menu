<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', Number($event.target.value) || 0)"
      type="number"
      step="0.01"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: [Number, String],
  label: String,
  required: Boolean,
})
</script>