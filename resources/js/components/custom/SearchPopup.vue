<template>
  <!-- ==== SEARCH ICON (always visible) ==== -->
  <button
    @click="open"
    class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200"
    title="Search people"
  >
    <svg
      class="w-6 h-6 text-gray-600 dark:text-gray-300"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
      />
    </svg>
  </button>

  <!-- ==== ANIMATED MODAL ==== -->
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4"
      @click.self="close"
    >
      <!-- Glass backdrop (animated blur) -->
      <div
        class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-500/10 backdrop-blur-sm transition-opacity duration-300"
        :class="{ 'opacity-0': !isOpen, 'opacity-100': isOpen }"
      />

      <!-- Modal Card (slide + fade) -->
      <div
        class="relative w-full max-w-md bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl p-6 space-y-4 border border-gray-200/50 dark:border-gray-700/50 transform transition-all duration-300"
        :class="{
          'translate-y-[-20px] opacity-0 scale-95': !isOpen,
          'translate-y-0 opacity-100 scale-100': isOpen
        }"
        @keydown.esc="close"
      >
        <!-- ==== HEADER: Title + Animated Hint ==== -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Search
            </h3>
            <!-- Pulsing hint -->
            <span
              class="animate-pulse text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 rounded-full whitespace-nowrap"
            >
              Fill any field then click Search
            </span>
          </div>

          <button
            @click="close"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- ==== SEARCH FIELDS ==== -->
        <div class="space-y-3">
          <input
            v-model="filters.name"
            @keyup.enter="submit"
            type="text"
            placeholder="Name"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
          />
          <input
            v-model="filters.email"
            @keyup.enter="submit"
            type="text"
            placeholder="Email"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
          />
          <input
            v-model="filters.phone"
            @keyup.enter="submit"
            type="text"
            placeholder="Phone"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
          />
          <input
            v-model="filters.designation"
            @keyup.enter="submit"
            type="text"
            placeholder="Designation"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
          />
        </div>

        <!-- ==== ACTION BUTTONS ==== -->
        <div class="flex gap-3 justify-end">
          <button
            @click="reset"
            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 cursor-pointer"
          >
            Reset
          </button>
          <button
            @click="submit"
            :disabled="form.processing"
            class="relative px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 rounded-lg transition-all duration-200 flex items-center gap-2 cursor-pointer overflow-hidden"
          >
            <!-- Ripple effect -->
            <span
              v-if="form.processing"
              class="absolute inset-0 bg-white/30 animate-ping rounded-lg"
            />
            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span>{{ form.processing ? 'Searching...' : 'Search' }}</span>
          </button>
        </div>

        <!-- ==== ANIMATED RESULTS ==== -->
        <div v-if="people.length" class="max-h-64 overflow-y-auto space-y-2 border-t pt-3">
          <div
            v-for="(p, i) in people"
            :key="p.id"
            @click="selectPerson(p)"
            class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-all duration-200"
            :style="{ animationDelay: `${i * 50}ms` }"
            :class="i < 5 ? 'animate-fadeInUp' : ''"
          >
            <div
              class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm"
            >
              {{ p.name.charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ p.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ p.email }} • {{ p.phone || '—' }}
              </p>
              <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                {{ p.designation || '—' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps<{
  modelValue: string
  action: string
  preserve?: Record<string, any>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: string): void
  (e: 'select', p: any): void
}>()

/* ---------- STATE ---------- */
const isOpen = ref(false)
const wasSearched = ref(false)

const filters = ref({
  name: '',
  email: '',
  phone: '',
  designation: '',
})

const page = usePage()
const people = ref<any[]>([])

/* ---------- INERTIA PEOPLE SYNC ---------- */
watch(
  () => page.props.people,
  (val) => {
    people.value = Array.isArray(val) ? val : []
    wasSearched.value = true
  },
  { immediate: true }
)

/* ---------- FORM ---------- */
const form = useForm({
  name: '',
  email: '',
  phone: '',
  designation: '',
  ...(props.preserve || {}),
})

/* ---------- ACTIONS ---------- */
const open = () => {
  isOpen.value = true
  nextTick(() => {
    const firstInput = document.querySelector('input[placeholder="Name"]') as HTMLInputElement
    firstInput?.focus()
  })
}

const close = () => {
  isOpen.value = false
}

const reset = () => {
  filters.value = { name: '', email: '', phone: '', designation: '' }
  form.reset()
  wasSearched.value = false
  people.value = []
  emit('update:modelValue', '')
}

const submit = () => {
  form.name = filters.value.name.trim()
  form.email = filters.value.email.trim()
  form.phone = filters.value.phone.trim()
  form.designation = filters.value.designation.trim()

  Object.entries(props.preserve || {}).forEach(([k, v]) => {
    ;(form as any)[k] = v
  })

  form.post(props.action, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['people'],
    onFinish: () => {
      reset()
      close()
    },
  })
}

const selectPerson = (p: any) => {
  emit('select', p)
  close()
}

/* ---------- SYNC EXTERNAL MODEL ---------- */
watch(
  () => props.modelValue,
  (v) => {
    if (v && !filters.value.name) {
      filters.value.name = v
    }
  }
)

/* ---------- ESC KEY ---------- */
const escHandler = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isOpen.value) close()
}
onMounted(() => document.addEventListener('keydown', escHandler))
onUnmounted(() => document.removeEventListener('keydown', escHandler))
</script>

<style scoped>
/* Fade In Up Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fadeInUp {
  animation: fadeInUp 0.3s ease-out forwards;
}

/* Fade In */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fadeIn {
  animation: fadeIn 0.4s ease-out;
}
</style>