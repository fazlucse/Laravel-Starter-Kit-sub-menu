<template>
  <div class="relative">
    <!-- LABEL -->
    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-600 dark:text-red-500">*</span>
    </label>

    <!-- INPUT + ICONS -->
    <div class="relative">
      <input
        type="text"
        :value="displayValue"
        @input="onInput"
        @focus="onFocus"
        @blur="delayHide"
        :placeholder="`Search ${label.toLowerCase()}...`"
        :readonly="isLocked"
        class="
          w-full px-4 py-2 pr-10 rounded-lg
          border border-gray-300 dark:border-gray-600
          bg-white dark:bg-gray-800
          text-gray-900 dark:text-gray-100
          placeholder-gray-400 dark:placeholder-gray-300
          focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400
          focus:border-transparent
          transition-colors duration-200
        "
        :class="{
          'border-red-500 dark:border-red-500': error,
          'cursor-not-allowed bg-gray-50 dark:bg-gray-700': isLocked,
          'text-gray-900 dark:text-gray-100': selectedName,
          'text-gray-500 dark:text-gray-400': !selectedName && !search
        }"
        autocomplete="off"
      />

      <!-- CLEAR BUTTON -->
      <button
        v-if="selectedName"
        @click="clearSelection"
        class="cursor-pointer absolute inset-y-0 right-0 flex items-center pr-3 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition"
        type="button"
        title="Clear selection"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- SPINNER -->
      <div v-if="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="animate-spin h-4 w-4 text-blue-500 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- DROPDOWN RESULTS -->
    <div
      v-if="showDropdown && results.length"
      class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-auto"
    >
      <button
        v-for="item in results"
        :key="item.id"
        @mousedown.prevent="selectItem(item)"
        class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
      >
        <img v-if="item.photo" :src="item.photo" class="w-8 h-8 rounded-full object-cover" />
        <div class="min-w-0 flex-1">
          <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
            {{ item.name }}
          </div>
          <div v-if="item.email" class="text-xs text-gray-500 dark:text-gray-400 truncate">
            {{ item.email }}
          </div>
        </div>
      </button>
    </div>

    <!-- NO RESULTS -->
    <div
      v-if="showDropdown && !loading && !results.length && search"
      class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3 text-sm text-gray-500 dark:text-gray-400"
    >
      No results found.
    </div>

    <!-- ERROR -->
    <p v-if="error" class="mt-1 text-xs text-red-600 dark:text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onBeforeUnmount } from "vue"
import debounce from "lodash/debounce"
import api from "@/api"

interface Person {
  id: number
  name: string
  email?: string
  photo?: string
}

// Props
const props = defineProps<{
  modelValue: number | string | null
  label: string
  endpoint: string
  required?: boolean
  error?: string
  initialName?: string
}>()

// Emits
const emit = defineEmits<{
  (e: "update:modelValue", id: number | null): void
  (e: "update:name", name: string): void
}>()

// State
const search = ref("")
const results = ref<Person[]>([])
const showDropdown = ref(false)
const loading = ref(false)
const selectedName = ref("")
const selectedId = ref<number | null>(null)

let abortController: AbortController | null = null

const cancelPrevious = () => {
  abortController?.abort()
  abortController = null
}

// Computed
const displayValue = computed(() => selectedName.value || search.value || "")
const isLocked = computed(() => !!selectedName.value)

// Search API
const searchApi = debounce(async (query: string) => {
  if (!query.trim()) {
    results.value = []
    return
  }

  cancelPrevious()
  loading.value = true
  abortController = new AbortController()

  try {
    const { data } = await api.get(props.endpoint, {
      params: { q: query },
      signal: abortController.signal,
    })

    const payload = Array.isArray(data) ? data : data[Object.keys(data)[0]] || []
    results.value = payload
  } catch (err: any) {
    if (err.name !== "CanceledError") console.error("Search error:", err)
    results.value = []
  } finally {
    loading.value = false
    abortController = null
  }
}, 300)

// Input handlers
function onInput(e: Event) {
  if (isLocked.value) return
  const val = (e.target as HTMLInputElement).value
  search.value = val
  searchApi(val)
}

function onFocus() {
  if (!isLocked.value) showDropdown.value = true
}

function delayHide() {
  setTimeout(() => {
    showDropdown.value = false
  }, 200)
}

// Select & Clear
function selectItem(item: Person) {
  selectedId.value = item.id
  selectedName.value = item.name
  emit("update:modelValue", item.id)
  emit("update:name", item.name)
  search.value = ""
  showDropdown.value = false
}

function clearSelection() {
  selectedId.value = null
  selectedName.value = ""
  emit("update:modelValue", null)
  emit("update:name", "")
  search.value = ""
  results.value = []
  showDropdown.value = false
}

// WATCH: Sync modelValue + initialName
watch(
  () => [props.modelValue, props.initialName],
  ([id, initName]) => {
    const idNum = id ? Number(id) : null

    if (idNum && initName && selectedId.value !== idNum) {
      selectedName.value = initName
      selectedId.value = idNum
      emit("update:name", initName)
      return
    }

    if (idNum === null && selectedId.value !== null) {
      clearSelection()
      return
    }

    if (idNum === selectedId.value) return
  },
  { immediate: true }
)

onBeforeUnmount(() => cancelPrevious())
</script>

<style scoped>
button:focus { outline: none; }
</style>