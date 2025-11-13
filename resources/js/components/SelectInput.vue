<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>

    <div class="relative">
      <select
        :value="modelValue"
        @change="onChange($event.target.value)"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-50"
        :class="{ 'border-red-500': error }"
        :disabled="loading"
      >
        <!-- <option value="">-- Select {{ label }} --</option> -->
        <option v-for="opt in displayOptions" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>

      <!-- Loading spinner -->
      <div v-if="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: [String, Number],
  label: { type: String, required: true },
  // Either endpoint OR options (not both)
  endpoint: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  required: Boolean,
  error: String,
})

const emit = defineEmits(['update:modelValue', 'update:name'])

const loading = ref(false)
const apiOptions = ref([])     // [[id, name], …] from API
const nameMap = ref({})        // id → name

// ------------------------------------------------------------------
// Determine which options to display
// ------------------------------------------------------------------
const displayOptions = computed(() => {
  // Static array passed → use it
  if (props.options.length > 0) {
    return props.options.map(opt => {
      if (typeof opt === 'string') {
        return { value: opt, label: opt }
      }
      if (Array.isArray(opt) && opt.length === 2) {
        return { value: opt[0], label: opt[1] }
      }
      return opt // already {value, label}
    })
  }

  // API mode
  return apiOptions.value.map(([id, name]) => ({ value: id, label: name }))
})

// ------------------------------------------------------------------
// Fetch from API (only if endpoint is provided)
// ------------------------------------------------------------------
onMounted(() => {
  if (props.endpoint) fetchOptions()
})
watch(() => props.endpoint, () => {
  if (props.endpoint) fetchOptions()
})

function fetchOptions() {
  loading.value = true
  router.get(
    props.endpoint,
    {},
    {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        const key = Object.keys(page.props).find(k => Array.isArray(page.props[k]))
        const list = page.props[key] ?? []

        apiOptions.value = list.map(item => [item.id, item.name])
        nameMap.value = Object.fromEntries(apiOptions.value)

        // Auto-emit name if editing
        if (props.modelValue && nameMap.value[props.modelValue]) {
          emit('update:name', nameMap.value[props.modelValue])
        }
      },
      onError: () => console.error(`Failed to load ${props.label}`),
      onFinish: () => loading.value = false
    }
  )
}

// ------------------------------------------------------------------
// Handle selection
// ------------------------------------------------------------------
function onChange(value) {
  let name = ''

  // Static options
  if (props.options.length > 0) {
    const found = displayOptions.value.find(o => o.value == value)
    name = found ? found.label : ''
  }
  // API options
  else if (nameMap.value[value]) {
    name = nameMap.value[value]
  }

  emit('update:modelValue', value)
  emit('update:name', name)
}

// ------------------------------------------------------------------
// Sync name when modelValue changes (edit mode)
// ------------------------------------------------------------------
watch(
  () => props.modelValue,
  (val) => {
    if (!val) return

    // Static
    if (props.options.length > 0) {
      const opt = displayOptions.value.find(o => o.value == val)
      if (opt) emit('update:name', opt.label)
    }
    // API
    else if (nameMap.value[val]) {
      emit('update:name', nameMap.value[val])
    }
  },
  { immediate: true }
)
</script>