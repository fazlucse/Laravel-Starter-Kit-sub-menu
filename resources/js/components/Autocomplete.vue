<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>

    <div class="relative">
      <input
        type="text"
        :value="search"
        @input="onInput($event.target.value)"
        @focus="showDropdown = true"
        @blur="delayHide"
        :placeholder="`Search ${label.toLowerCase()}...`"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
        :class="{ 'border-red-500': error }"
        autocomplete="off"
      />

      <!-- Dropdown -->
      <div
        v-if="showDropdown && results.length"
        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto"
      >
        <button
          v-for="item in results"
          :key="item.id"
          @mousedown.prevent="selectItem(item)"
          class="w-full px-3 py-2 text-left hover:bg-indigo-50 flex items-center gap-3"
        >
          <img
            v-if="item.photo"
            :src="item.photo"
            class="w-8 h-8 rounded-full object-cover"
            alt="photo"
          />
          <div>
            <div class="font-medium">{{ item.name }}</div>
            <div v-if="item.email" class="text-xs text-gray-500">{{ item.email }}</div>
          </div>
        </button>
      </div>

      <!-- Loading -->
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
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'

const props = defineProps({
  modelValue: [String, Number],
  label: { type: String, required: true },
  endpoint: { type: String, required: true },
  required: Boolean,
  error: String,
})

const emit = defineEmits(['update:modelValue', 'update:name'])

const search = ref('')
const results = ref([])
const showDropdown = ref(false)
const loading = ref(false)

// Debounced search
const debouncedSearch = debounce((query) => {
  if (!query.trim()) {
    results.value = []
    return
  }

  loading.value = true
  router.get(
    props.endpoint,
    { q: query },
    {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        const key = Object.keys(page.props).find(k => Array.isArray(page.props[k]))
        results.value = page.props[key] || []
      },
      onFinish: () => loading.value = false
    }
  )
}, 300)

// Input handler
function onInput(value) {
  search.value = value
  debouncedSearch(value)
}

// Select item
function selectItem(item) {
  emit('update:modelValue', item.id)
  emit('update:name', item.name)
  search.value = item.name
  showDropdown.value = false
}

// Hide dropdown on blur (with delay for click)
function delayHide() {
  setTimeout(() => {
    showDropdown.value = false
  }, 200)
}

// Sync search input when editing
watch(
  () => props.modelValue,
  (id) => {
    if (id && results.value.length) {
      const found = results.value.find(r => r.id == id)
      if (found) search.value = found.name
    }
  },
  { immediate: true }
)
</script>