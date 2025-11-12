<!-- resources/js/components/PersonAutocomplete.vue -->
<template>
  <div class="relative">
    <!-- Label -->
    <label class="block text-sm font-semibold text-gray-700 mb-2">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Input -->
    <div class="relative">
      <input
        type="text"
        v-model="search"
        @input="onInput"
        @focus="showDropdown = true"
        @blur="delayHide"
        :placeholder="`Search ${label.toLowerCase()}...`"
        class="w-full px-4 py-2 pr-10 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        :class="{ 'border-red-500': error }"
        autocomplete="off"
      />

      <!-- Local Search Spinner -->
      <div v-if="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- Dropdown -->
    <div
      v-if="showDropdown && results.length"
      class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
    >
      <button
        v-for="item in results"
        :key="item.id"
        @mousedown.prevent="selectItem(item)"
        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-3"
      >
        <img v-if="item.photo" :src="item.photo" class="w-8 h-8 rounded-full object-cover" />
        <div class="min-w-0 flex-1">
          <div class="font-medium text-gray-900 truncate">{{ item.name }}</div>
          <div v-if="item.email" class="text-xs text-gray-500 truncate">{{ item.email }}</div>
        </div>
      </button>
    </div>

    <!-- No Results -->
    <div
      v-if="showDropdown && !loading && !results.length && search"
      class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500"
    >
      No results found.
    </div>

    <!-- Error -->
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'

interface Person {
  id: number
  name: string
  email?: string
  photo?: string
}

const props = defineProps<{
  modelValue: number | string | null
  label: string
  endpoint: string
  required?: boolean
  error?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', id: number): void
  (e: 'update:name', name: string): void
}>()

const search = ref('')
const results = ref<Person[]>([])
const showDropdown = ref(false)
const loading = ref(false)
const selectedName = ref('')

// Debounced search
const searchApi = debounce((query: string) => {
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
      onSuccess: (response) => {
        const data = response.data
        results.value = Array.isArray(data) ? data : data[Object.keys(data)[0]] || []
      },
      onError: () => {
        results.value = []
      },
      onFinish: () => loading.value = false
    }
  )
}, 300)

function onInput() {
  selectedName.value = ''
  searchApi(search.value)
}

function selectItem(item: Person) {
  emit('update:modelValue', item.id)
  emit('update:name', item.name)
  selectedName.value = item.name
  search.value = item.name
  showDropdown.value = false
}

function delayHide() {
  setTimeout(() => showDropdown.value = false, 200)
}

// Auto-fill on edit
watch(
  () => props.modelValue,
  (id) => {
    if (!id) {
      selectedName.value = ''
      search.value = ''
      return
    }

    const cached = results.value.find(r => r.id === id)
    if (cached) {
      selectedName.value = cached.name
      search.value = cached.name
      return
    }

    loading.value = true
    router.get(
      `${props.endpoint}/${id}`,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (res) => {
          const item = res.data
          if (item?.id === id) {
            selectedName.value = item.name
            search.value = item.name
            results.value = [item]
          }
        },
        onFinish: () => loading.value = false
      }
    )
  },
  { immediate: true }
)
</script>