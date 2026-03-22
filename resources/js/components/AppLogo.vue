<script setup lang="ts">
// Fetching dynamic data from .env
import { ref } from 'vue';
const comName    = import.meta.env.VITE_COM_NAME || 'Default Company';
const comAddress = import.meta.env.VITE_COM_ADDRESS || '';
const comLogo = (import.meta.env.VITE_COM_LOGO || '').replace(/['"]+/g, '');
const imageError = ref(false);
const getInitials = (name: string) => {
    if (!name) return 'C';

    return name
        .split(' ')                  // Split by space: ["Your", "Company", "Ltd"]
        .filter(word => word.length > 0) // Remove extra spaces
        .map(word => word[0])        // Take first letter: ["Y", "C", "L"]
        .join('')                    // Join: "YCL"
        .toUpperCase()               // Ensure uppercase
        .substring(0, 3);            // Optional: Limit to 3 characters for small UI
};
</script>

<template>
    <div class="flex items-center gap-3">
        <div class=" -ml-2 flex aspect-square size-12 items-center justify-center rounded-full bg-sidebar-primary overflow-hidden shadow-sm border border-gray-200 dark:border-gray-800">
            <img
                v-if="comLogo && !imageError"
                :src="comLogo"
                :alt="comName"
                @error="imageError = true"
                class="size-7 object-contain"
            />

            <div v-else class="text-lg font-black text-white dark:text-black select-none uppercase">
                {{ getInitials(comName) }}
            </div>
        </div>

        <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-black uppercase tracking-tight text-gray-900 dark:text-white">
                {{ comName }}
            </span>
            <span class="truncate text-[10px] font-bold text-gray-500 dark:text-gray-400">
                {{ comAddress }}
            </span>
        </div>
    </div>
</template>
