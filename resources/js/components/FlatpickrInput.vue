<template>
  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      ref="fp"
      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
      :class="{ 'border-red-500': error }"
      :placeholder="mode === 'time' ? 'HH:MM' : 'YYYY-MM-DD'"
    />
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'

const props = defineProps({
  modelValue: String,
  label: String,
  required: Boolean,
  error: String,
  mode: { type: String, default: 'date' }, // 'date' or 'time'
})

const emit = defineEmits(['update:modelValue'])
const fp = ref(null)

onMounted(() => {
  flatpickr(fp.value, {
    enableTime: props.mode === 'time',
    noCalendar: props.mode === 'time',
    dateFormat: props.mode === 'time' ? 'H:i' : 'Y-m-d',
    time_24hr: true,
    onChange: (selectedDates, dateStr) => {
      emit('update:modelValue', dateStr)
    }
  })
})
</script>