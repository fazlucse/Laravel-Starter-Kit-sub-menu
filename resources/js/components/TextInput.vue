<template>
  <div>
    <!-- LABEL – white in dark mode -->
    <label
      class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1"
    >
      {{ label }}
      <span v-if="required" class="text-red-600 dark:text-red-500">*</span>
    </label>

    <!-- INPUT -->
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :type="type"
      class="
        w-full px-3 py-2 rounded-md
        border border-gray-300 dark:border-gray-600
        bg-white dark:bg-gray-800
        text-gray-900 dark:text-gray-100
        placeholder-gray-400 dark:placeholder-gray-500
        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400
        focus:border-indigo-500 dark:focus:border-indigo-400
        transition-colors duration-200
      "
      :class="{ 'border-red-500 dark:border-red-500': error }"
      :placeholder="placeholder"
    />

    <!-- ERROR -->
    <p v-if="error" class="mt-1 text-xs text-red-600 dark:text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  label: String,
  type: { type: String, default: 'text' },
  required: Boolean,
  error: String,
  placeholder: { type: String, default: '' }
})
</script>