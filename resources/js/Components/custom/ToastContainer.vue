<!-- resources/js/Components/custom/feedback/ToastContainer.vue -->
<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <transition-group name="toast" tag="div">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        @click="remove(toast.id)"
        class="max-w-sm w-full p-4 rounded-lg shadow-lg cursor-pointer text-sm font-medium"
        :class="toastClass(toast.type)"
      >
        <div class="flex justify-between items-center">
          <span>{{ toast.message }}</span>
          <button class="ml-4 text-lg opacity-70 hover:opacity-100">&times;</button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast'
const { toasts, remove } = useToast()

const toastClass = (type: string) => ({
  success: 'bg-green-100 text-green-800 border border-green-200',
  error:   'bg-red-100   text-red-800   border border-red-200',
}[type] ?? '')
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all .3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>