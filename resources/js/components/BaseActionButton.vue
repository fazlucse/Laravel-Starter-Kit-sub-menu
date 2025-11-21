<template>
  <button
    :class="buttonClasses"
    :disabled="disabled || isProcessing"
    @click="$emit('click')"
  >
    <!-- Icon on left -->
    <component
      v-if="icon && iconPosition === 'left' && !isProcessing"
      :is="icon"
      class="h-5 w-5"
    />
    
    <!-- Spinner when processing -->
    <svg
      v-if="isProcessing"
      class="animate-spin h-5 w-5 text-white"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      ></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
      ></path>
    </svg>

    <span v-if="!isProcessing">{{ label }}</span>
    <span v-else>Processing...</span>
    
    <!-- Icon on right -->
    <component
      v-if="icon && iconPosition === 'right' && !isProcessing"
      :is="icon"
      class="h-5 w-5"
    />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  icon: { type: [Object, Function, String], default: null },
  iconPosition: { type: String, default: 'left' },
  type: { type: String, default: 'outline' },
  disabled: { type: Boolean, default: false },
  isProcessing: { type: Boolean, default: false } // NEW
})

const buttonClasses = computed(() => {
  let base = 'inline-flex items-center gap-2 px-4 py-2 rounded transition justify-center'
  let typeClasses = ''

  switch (props.type) {
    case 'primary':
      typeClasses = 'btn btn-primary hover:bg-red-700'
      break
    case 'success':
      typeClasses = 'btn btn-success hover:bg-green-700'
      break
    default:
      typeClasses = 'btn btn-outline hover:bg-gray-200 dark:hover:bg-gray-600'
  }

  return `${base} ${typeClasses} ${props.disabled || props.isProcessing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`
})
</script>
