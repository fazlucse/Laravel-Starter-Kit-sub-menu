<!-- resources/js/Components/custom/ToastAlert.vue -->
<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="transform opacity-0 translate-x-4"
    enter-to-class="transform opacity-100 translate-x-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="transform opacity-100 translate-x-0"
    leave-to-class="transform opacity-0 translate-x-4"
  >
    <div
      v-if="show"
      :class="[
        'fixed top-4 right-4 max-w-sm w-full p-4 rounded-lg shadow-lg text-sm font-medium flex items-center gap-3 z-50',
        type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
      ]"
      role="alert"
    >
      <!-- Icon -->
      <svg
        v-if="type === 'success'"
        class="w-5 h-5 flex-shrink-0"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path fill-rule="evenodd"
          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
          clip-rule="evenodd" />
      </svg>
      <svg
        v-else
        class="w-5 h-5 flex-shrink-0"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path fill-rule="evenodd"
          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
          clip-rule="evenodd" />
      </svg>

      <!-- Message -->
      <span class="flex-1">{{ message }}</span>

      <!-- Close button -->
      <button
        @click="close"
        class="ml-auto text-white hover:opacity-70 focus:outline-none"
        aria-label="Close"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </Transition>
</template>

<script setup lang="ts">
defineProps<{
  type: 'success' | 'error'
  message: string
  show: boolean
}>()

const emit = defineEmits(['update:show'])

function close() {
  emit('update:show', false)
}
</script>