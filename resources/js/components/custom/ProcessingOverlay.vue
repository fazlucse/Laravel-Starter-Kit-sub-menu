<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next'

defineProps({
    active: Boolean,
    message: {
        type: String,
        default: 'Generating Report...'
    }
})
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="active" class="absolute inset-0 bg-white/60 dark:bg-gray-800/60 z-50 flex items-center justify-center backdrop-blur-[2px] rounded-lg">
            <div class="flex flex-col items-center gap-2 bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700">
                <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide">{{ message }}</span>
            </div>
        </div>
    </Transition>
</template>
