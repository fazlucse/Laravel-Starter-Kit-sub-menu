<template>
  <div>
    <!-- LABEL -->
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-600 dark:text-red-500">*</span>
    </label>

    <!-- INPUT + ICON -->
    <div class="relative">
      <input
        ref="fp"
        :value="displayValue"
        @input="handleManualInput"
        type="text"
        placeholder="12:30 PM"
        class="
          w-full px-3 py-2 pr-10 rounded-md
          border border-gray-300 dark:border-gray-600
          bg-white dark:bg-gray-800
          text-gray-900 dark:text-gray-100
          placeholder-gray-400 dark:placeholder-gray-300
          focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400
          focus:border-indigo-500 dark:focus:border-indigo-400
          transition-colors duration-200
        "
        :class="{ 'border-red-500 dark:border-red-500': error }"
      />

      <!-- CLOCK ICON -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>

    <!-- ERROR -->
    <p v-if="error" class="mt-1 text-xs text-red-600 dark:text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'
import 'flatpickr/dist/themes/dark.css'

interface Props {
  modelValue?: string | null
  label?: string
  required?: boolean
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: '',
  required: false,
  error: ''
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const fp = ref<HTMLInputElement | null>(null)
let picker: any = null

// Display: 12-hour with AM/PM
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const [h, m] = props.modelValue.split(':').map(Number)
  const period = h >= 12 ? 'PM' : 'AM'
  const hour12 = h % 12 || 12
  return `${hour12.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')} ${period}`
})

// Manual input
function handleManualInput(e: Event) {
  const raw = (e.target as HTMLInputElement).value.trim().toUpperCase()
  if (!raw) {
    emit('update:modelValue', '')
    return
  }

  const match = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/)
  if (!match) return

  let [, hourStr, minuteStr, period] = match
  let hour = parseInt(hourStr)
  const minute = parseInt(minuteStr)

  if (hour < 1 || hour > 12 || minute > 59) return

  if (period === 'PM' && hour !== 12) hour += 12
  if (period === 'AM' && hour === 12) hour = 0

  const time24 = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`
  emit('update:modelValue', time24)
}

onMounted(() => {
  initPicker()
})

watch(() => props.modelValue, (val) => {
  if (picker && val) {
    picker.setDate(`1970-01-01 ${val}`, false)
  }
})

function initPicker() {
  const isDark = document.documentElement.classList.contains('dark')

  picker = flatpickr(fp.value!, {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'h:i K',     // 12-hour + AM/PM
    time_24hr: false,
    minuteIncrement: 1,
    theme: isDark ? 'dark' : undefined,
    static: false,
    appendTo: document.body,

    // Custom buttons
    onReady: () => {
      addCustomButtons()
    },
    onOpen: () => {
      addCustomButtons()
    },

    onChange: (selectedDates: Date[], dateStr: string) => {
      const match = dateStr.match(/(\d{1,2}):(\d{2}) (AM|PM)/)
      if (!match) return

      let [, hourStr, minuteStr, period] = match
      let hour = parseInt(hourStr)
      const minute = parseInt(minuteStr)

      if (period === 'PM' && hour !== 12) hour += 12
      if (period === 'AM' && hour === 12) hour = 0

      const time24 = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`
      emit('update:modelValue', time24)
    }
  })

  if (props.modelValue) {
    picker.setDate(`1970-01-01 ${props.modelValue}`, false)
  }
}

function addCustomButtons() {
  const container = picker.calendarContainer
  let footer = container.querySelector('.flatpickr-footer')

  if (!footer) {
    footer = document.createElement('div')
    footer.className = 'flatpickr-footer p-2 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2'
    container.appendChild(footer)
  } else {
    footer.innerHTML = ''
  }

  // OK Button
  const okBtn = document.createElement('button')
  okBtn.type = 'button'
  okBtn.textContent = 'OK'
  okBtn.className = 'px-4 py-1.5 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition'
  okBtn.onclick = () => {
    picker.close()
  }
  footer.appendChild(okBtn)

  // Clear Button
  const clearBtn = document.createElement('button')
  clearBtn.type = 'button'
  clearBtn.textContent = 'Clear'
  clearBtn.className = 'px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition'
  clearBtn.onclick = () => {
    emit('update:modelValue', '')
    picker.clear()
    picker.close()
  }
  footer.appendChild(clearBtn)
}
</script>