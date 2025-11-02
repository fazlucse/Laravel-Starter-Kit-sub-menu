<!-- resources/js/Components/custom/SearchPopup.vue -->
<template>
  <div>
    <!-- Search Button -->
    <button
      @click="open"
      class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
    >
      <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <span class="text-sm">Search...</span>
    </button>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-start justify-center pt-20 p-4 bg-black/50" @click.self="close">
        <div
          class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 space-y-4 animate-in fade-in zoom-in duration-200"
          @keydown.esc="close"
        >
          <!-- Header -->
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Search People</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Search Input -->
          <div class="relative">
            <input
              v-model="localSearch"
              @input="debouncedSearch"
              type="text"
              placeholder="Type to search..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
              ref="input"
              :disabled="form.processing"
            />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <div v-if="form.processing" class="absolute right-3 top-1/2 -translate-y-1/2">
              <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
            </div>
          </div>

          <!-- Clear Button -->
          <button
            v-if="localSearch"
            @click="clear"
            class="text-xs text-blue-600 hover:underline"
          >
            Clear
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'

const props = defineProps<{
  modelValue: string
  action: string
  preserve?: Record<string, any>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const isOpen = ref(false)
const localSearch = ref(props.modelValue)
const input = ref<HTMLInputElement | null>(null)

const form = useForm({
  search: props.modelValue,
  ...(props.preserve || {})
})

const open = () => {
  isOpen.value = true
  nextTick(() => input.value?.focus())
}

const close = () => {
  isOpen.value = false
}

const clear = () => {
  localSearch.value = ''
  emit('update:modelValue', '')
  form.clearErrors()
  searchNow()
}

// Debounced search
const searchNow = debounce(() => {
  form.search = localSearch.value
  Object.entries(props.preserve || {}).forEach(([k, v]) => (form as any)[k] = v)

  form.post(props.action, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['people'],
  })
}, 300)

const debouncedSearch = () => {
  emit('update:modelValue', localSearch.value)
  searchNow()
}

// Sync external changes
watch(() => props.modelValue, (val) => {
  if (val !== localSearch.value) {
    localSearch.value = val
  }
})

// ESC to close
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') close()
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))
</script>