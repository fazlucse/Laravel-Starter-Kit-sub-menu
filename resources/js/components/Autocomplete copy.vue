<!-- resources/js/Components/Autocomplete.vue -->
<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>

    <div class="relative">
      <div class="flex">
        <input
          ref="input"
          type="text"
          :value="displayValue"
          @input="onInput($event.target.value)"
          @focus="onFocus"
          :placeholder="selectedId ? '' : `Search ${label.toLowerCase()}...`"
          :readonly="!!selectedId"
          class="flex-1 px-3 py-2 pr-10 border rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-50"
          :class="{ 
            'border-red-500 focus:border-red-500 focus:ring-red-500': hasError,
            'bg-gray-50 cursor-not-allowed': selectedId,
            'cursor-text': !selectedId 
          }"
          autocomplete="off"
        />

        <!-- Clear Button -->
        <button
          v-if="selectedId"
          @click="clear"
          type="button"
          class="cursor-pointer px-3 bg-red-50 border border-l-0 border-red-300 rounded-r-md hover:bg-red-100 transition"
        >
          <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Dropdown -->
      <div
        v-if="showDropdown && results.length && !selectedId"
        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto"
      >
        <button
          v-for="item in results"
          :key="item.id"
          @mousedown.prevent="selectItem(item)"
          class="w-full px-3 py-2 text-left hover:bg-indigo-50 flex items-center gap-3"
        >
          <img v-if="is_photo && item.photo" :src="item.photo" class="w-8 h-8 rounded-full object-cover" />
          <div class="flex-1">
            <div class="font-medium">{{ item.name }}</div>
            <div v-if="item.email" class="text-xs text-gray-500">{{ item.email }}</div>
            <div v-else-if="item.phone" class="text-xs text-gray-500">{{ item.phone }}</div>
          </div>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading && !selectedId" class="absolute inset-y-0 right-10 flex items-center pr-3">
        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- ERROR MESSAGE - ONLY AFTER SUBMIT -->
    <p v-if="hasError" class="mt-1 text-xs text-red-600">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue'
import axios from 'axios'
import debounce from 'lodash/debounce'

const props = defineProps({
  modelValue: [String, Number],
  label: { type: String, required: true },
  endpoint: { type: String, required: true },
  required: Boolean,
  error: String,           // from Inertia (server error)
  is_photo: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'update:name'])

const input = ref<HTMLInputElement | null>(null)
const search = ref('')
const selectedId = ref<number | null>(null)
const selectedName = ref('')
const results = ref<any[]>([])
const showDropdown = ref(false)
const loading = ref(false)

// Track if form has been submitted
const wasSubmitted = ref(false)

// ——— Display value ———
const displayValue = computed(() => selectedId.value ? selectedName.value : search.value)

// ——— ERROR: Only show after submit OR server error ———
const hasError = computed(() => {
  return !!props.error || (wasSubmitted.value && props.required && !selectedId.value)
})

const errorMessage = computed(() => {
  return props.error || (wasSubmitted.value && props.required && !selectedId.value)
    ? 'This field is required.'
    : ''
})

// ——— Search ———
const debouncedSearch = debounce(async (query: string) => {
  if (!query.trim() || selectedId.value) {
    results.value = []
    return
  }
  loading.value = true
  try {
    const { data } = await axios.get(props.endpoint, { params: { q: query } })
    const payload = data?.person ?? data?.data ?? data?.items ?? data
    results.value = Array.isArray(payload) ? payload : []
  } catch (e) {
    results.value = []
  } finally {
    loading.value = false
  }
}, 300)

function onInput(v: string) {
  if (selectedId.value) return
  search.value = v
  debouncedSearch(v)
  showDropdown.value = true
}

function onFocus() {
  if (selectedId.value) return
  showDropdown.value = true
}

function selectItem(item: any) {
  selectedId.value = item.id
  selectedName.value = item.name
  search.value = ''
  showDropdown.value = false
  emit('update:modelValue', item.id)
  emit('update:name', item.name)
  nextTick(() => input.value?.blur())
}

function clear() {
  selectedId.value = null
  selectedName.value = ''
  search.value = ''
  results.value = []
  showDropdown.value = false
  emit('update:modelValue', null)
  emit('update:name', '')
  nextTick(() => input.value?.focus())
}

// ——— Sync v-model (edit mode) ———
watch(() => props.modelValue, async (id) => {
  if (!id) {
    selectedId.value = null
    selectedName.value = ''
    search.value = ''
    return
  }
  if (selectedId.value === id) return

  const found = results.value.find(r => r.id == id)
  if (found) {
    selectedId.value = id
    selectedName.value = found.name
    search.value = ''
    return
  }

  try {
    const { data } = await axios.get(props.endpoint, { params: { id } })
    const payload = data?.person?.[0] ?? data?.data?.[0] ?? data?.[0]
    if (payload) {
      selectedId.value = payload.id
      selectedName.value = payload.name
      results.value = [payload]
    }
  } catch (e) {
    console.error('Failed to load selected item', e)
  }
}, { immediate: true })

// ——— EXPOSE: Call this from parent on submit ———
defineExpose({
  markSubmitted() {
    wasSubmitted.value = true
  },
  resetSubmitted() {
    wasSubmitted.value = false
  }
})
</script>