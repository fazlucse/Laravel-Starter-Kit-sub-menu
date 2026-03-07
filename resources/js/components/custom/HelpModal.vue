<script setup lang="ts">
import { ref } from 'vue';
import {
    HelpCircle, X, BookOpen, Smartphone, ShieldCheck,
    MousePointer2, UserCheck, CalendarDays, Zap, Lock,
    CheckSquare, Info, InfoIcon, LayoutList, ClipboardPen
} from 'lucide-vue-next';

// Define Props to handle grouped sections
interface HelpSection {
    label: string;
    content: string;
    icon: string;
}

interface HelpGroup {
    group_title: string;
    sections: HelpSection[];
}

const props = defineProps<{
    doc: {
        title: string;
        groups: HelpGroup[];
    }
}>();

const isOpen = ref(false);

// Icon mapping for JSON icons
const iconMap: any = {
    Smartphone, ShieldCheck, MousePointer2, UserCheck,
    CalendarDays, Zap, Lock, CheckSquare, Info, InfoIcon,
    LayoutList, ClipboardPen
};
</script>

<template>
    <button
        @click="isOpen = true"
        type="button"
        class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl border border-blue-100 dark:border-blue-800 hover:bg-blue-100 transition-all group"
    >
        <HelpCircle class="w-4 h-4 group-hover:rotate-12 transition-transform" />
        <span class="text-[10px] font-black uppercase tracking-widest hidden md:inline">User Guide</span>
    </button>

    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="isOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 md:p-10 bg-gray-900/60 backdrop-blur-md">

                <div class="bg-white dark:bg-gray-900 w-[95%] md:w-[90%] max-w-[1400px] h-[90vh] rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col">

                    <div class="px-8 py-6 flex justify-between items-center border-b dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-600 rounded-2xl text-white shadow-lg shadow-blue-500/30">
                                <BookOpen class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black uppercase tracking-tight text-gray-900 dark:text-white">
                                    {{ doc.title }}
                                </h3>
                                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-[0.2em]">Operational Documentation</p>
                            </div>
                        </div>
                        <button @click="isOpen = false" class="p-2.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
                            <X class="w-6 h-6 text-gray-400" />
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 md:p-12 custom-scrollbar">

                        <div v-for="(group, gIndex) in doc.groups" :key="gIndex" class="mb-16 last:mb-0">

                            <div class="flex items-center gap-6 mb-10">
                                <h2 class="text-xs font-black uppercase tracking-[0.4em] text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-5 py-2.5 rounded-xl whitespace-nowrap">
                                    {{ group.group_title }}
                                </h2>
                                <div class="h-px w-full bg-gray-100 dark:bg-gray-800"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-x-16 gap-y-10">
                                <div v-for="(section, sIndex) in group.sections" :key="sIndex" class="flex gap-6 group/item">
                                    <div class="flex-shrink-0">
                                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-[1.25rem] group-hover/item:bg-blue-600 group-hover/item:text-white transition-all duration-300 shadow-sm">
                                            <component :is="iconMap[section.icon] || InfoIcon" class="w-6 h-6" />
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black uppercase text-gray-900 dark:text-white mb-2 tracking-wide">
                                            {{ section.label }}
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                                            {{ section.content }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800/30 border-t dark:border-gray-800 flex justify-between items-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">
                            Helpful Tip: Press <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-600">ESC</kbd> to close this manual
                        </p>
                        <button
                            @click="isOpen = false"
                            class="px-12 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl hover:scale-105 transition-transform"
                        >
                            Got it, Close Manual
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Modal Animation */
.modal-fade-enter-active, .modal-fade-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-fade-enter-from, .modal-fade-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
