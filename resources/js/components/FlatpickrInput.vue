<template>
  <div>
    <!-- LABEL – white in dark mode -->
    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-600 dark:text-red-500">*</span>
    </label>

    <!-- FLATPICKR INPUT -->
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      ref="fp"
      class="
        w-full px-4 py-2 rounded-lg
        border border-gray-300 dark:border-gray-600
        bg-white dark:bg-gray-800
        text-gray-900 dark:text-gray-100
        placeholder-gray-400 dark:placeholder-gray-300
        focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400
        focus:border-blue-500 dark:focus:border-blue-400
        transition-colors duration-200
      "
      :class="{ 'border-red-500 dark:border-red-500': error }"
      :placeholder="mode === 'time' ? 'HH:MM' : 'YYYY-MM-DD'"
    />

    <!-- ERROR -->
    <p v-if="error" class="mt-1 text-xs text-red-600 dark:text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'
import 'flatpickr/dist/themes/dark.css'

// 1. Define Props Interface
interface Props {
  modelValue?: string
  label?: string
  required?: boolean
  error?: string
  mode?: 'date' | 'time'
}

// 2. Use withDefaults() for default values
const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: '',
  required: false,
  error: '',
  mode: 'date'
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const fp = ref<HTMLInputElement | null>(null)
let picker: any = null

onMounted(() => {
  initPicker()
})

watch(() => props.mode, () => {
  if (picker) picker.destroy()
  initPicker()
})

function initPicker() {
  const isDark = document.documentElement.classList.contains('dark')

  picker = flatpickr(fp.value!, {
    enableTime: props.mode === 'time',
    noCalendar: props.mode === 'time',
    dateFormat: props.mode === 'time' ? 'H:i' : 'Y-m-d',
    time_24hr: true,
    theme: isDark ? 'dark' : undefined,
    onChange: (selectedDates: Date[], dateStr: string) => {
      emit('update:modelValue', dateStr)
    }
  })
}
</script>