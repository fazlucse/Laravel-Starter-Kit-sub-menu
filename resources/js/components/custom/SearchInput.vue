<template>
  <div class="relative w-full sm:w-64">
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      type="text"
      :placeholder="placeholder"
      class="w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
      @keydown.enter.prevent
      :disabled="form.processing"
    />
    <!-- Search Icon -->
    <svg
      class="absolute left-2.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>

    <!-- Optional: Loading Spinner -->
    <div v-if="form.processing" class="absolute right-2 top-1/2 -translate-y-1/2">
      <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
      </svg>
    </div>
  </div>
</template>

<script setup lang="ts">
import { debounce } from 'lodash-es'
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

// Props
const props = defineProps<{
  modelValue: string
  placeholder?: string
  action: string
  preserve?: Record<string, any>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

// useForm handles CSRF + processing
const form = useForm({
  search: props.modelValue,
  ...(props.preserve || {})
})

// DEBOUNCE ONCE — outside watch
const searchWithForm = debounce(() => {
  form.search = props.modelValue

  // Sync preserve values
  Object.entries(props.preserve || {}).forEach(([key, value]) => {
    // @ts-ignore – dynamic key
    form[key] = value
  })

  form.post(props.action, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['people'],
  })
}, 300)

// WATCH: just call the debounced function
watch(() => props.modelValue, searchWithForm)

// Optional: clear errors on typing
watch(() => props.modelValue, () => form.clearErrors('search'))
</script>