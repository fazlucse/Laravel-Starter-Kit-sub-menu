<template>
    <Transition name="slide-fade">
        <div v-if="show"
             :class="[
           'fixed top-5 right-5 z-[100] flex items-center p-4 mb-4 w-full max-w-xs rounded-lg shadow-2xl border-l-4',
           type === 'error' ? 'bg-red-50 text-red-800 border-red-500 dark:bg-gray-800 dark:text-red-400' : 'bg-green-50 text-green-800 border-green-500 dark:bg-gray-800 dark:text-green-400'
         ]">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
                <component :is="type === 'error' ? 'AlertCircle' : 'CheckCircle2'" class="w-5 h-5" />
            </div>
            <div class="ml-3 text-sm font-normal">{{ message }}</div>
            <button @click="close" class="ml-auto -mx-1.5 -my-1.5 bg-transparent text-gray-400 hover:text-gray-900 rounded-lg p-1.5 inline-flex h-8 w-8">
                <X class="w-5 h-5" />
            </button>
        </div>
    </Transition>
</template>

<script setup>
import { AlertCircle, CheckCircle2, X } from 'lucide-vue-next';

defineProps({
    show: Boolean,
    message: String,
    type: { type: String, default: 'success' } // 'success' or 'error'
});

const emit = defineEmits(['close']);
const close = () => emit('close');
</script>

<style scoped>
.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to {
    transform: translateX(20px);
    opacity: 0;
}
</style>
