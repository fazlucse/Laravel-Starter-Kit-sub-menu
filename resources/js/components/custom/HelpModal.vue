<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import {
    HelpCircle,
    X,
    BookOpen,
    Smartphone,
    ShieldCheck,
    MousePointer2,
    UserCheck,
    CalendarDays,
    Zap,
    Lock,
    CheckSquare,
    InfoIcon,
    LayoutList,
    ClipboardPen
} from "lucide-vue-next";

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
    doc:{
        title:string
        description?: string
        tips?: string[]
        groups:HelpGroup[]
    }
}>()

const isOpen = ref(false)
const close = () => isOpen.value = false

const iconMap:any = {
    Smartphone,
    ShieldCheck,
    MousePointer2,
    UserCheck,
    CalendarDays,
    Zap,
    Lock,
    CheckSquare,
    LayoutList,
    ClipboardPen
}

const scrollToSection = (id:string)=>{
    const el = document.getElementById(id)
    if(el){
        el.scrollIntoView({
            behavior:"smooth",
            block:"start"
        })
    }
}

const handleEsc = (e:KeyboardEvent)=>{ if(e.key === "Escape") close() }

onMounted(()=>{ window.addEventListener("keydown",handleEsc) })
onUnmounted(()=>{ window.removeEventListener("keydown",handleEsc) })
</script>

<template>

    <!-- Trigger Button -->
    <button
        @click="isOpen = true"
        class="flex items-center gap-2 px-3 py-2 text-xs font-semibold bg-blue-50 text-blue-600 rounded-md border border-blue-200 hover:bg-blue-100 transition cursor-pointer"
    >
        <HelpCircle class="w-4 h-4"/>
        User Guide
    </button>

    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-2 sm:p-4"
            >
                <div class="bg-white dark:bg-gray-900 w-[95%] h-[95%] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-600 text-white rounded-md">
                                <BookOpen class="w-4 h-4"/>
                            </div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ doc.title }}
                            </h3>
                        </div>
                        <button
                            @click="close"
                            class="p-2 hover:bg-gray-200 dark:hover:bg-gray-800 transition cursor-pointer"
                        >
                            <X class="w-5 h-5 text-gray-500"/>
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="flex flex-1 overflow-hidden flex-col md:flex-row">

                        <!-- SIDEBAR -->
                        <div class="w-full md:w-64 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-4 overflow-y-auto">
                            <div class="space-y-4">

                                <!-- Description -->
                                <div v-if="doc.description">
                                    <h4 @click="scrollToSection('description')" class="cursor-pointer text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                        Description
                                    </h4>

                                </div>

                                <!-- Groups & Sections -->
                                <div v-for="(group,gIndex) in doc.groups" :key="gIndex">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                        {{ group.group_title }}
                                    </h4>
                                    <ul class="space-y-1">
                                        <li
                                            v-for="(section,sIndex) in group.sections"
                                            :key="sIndex"
                                            @click="scrollToSection(`section-${gIndex}-${sIndex}`)"
                                            class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 cursor-pointer transition"
                                        >
                                            {{ section.label }}
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>

                        <!-- CONTENT -->
                        <div class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar">

                            <!-- Description -->
                            <div v-if="doc.description" id="description"
                                 class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-md text-sm sm:text-base text-gray-700 dark:text-gray-300">
                                {{ doc.description }}
                            </div>

                            <!-- Tips -->
                            <div v-if="doc.tips && doc.tips.length" class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-md text-sm sm:text-base text-gray-700 dark:text-gray-300">
                                <h4 class="font-semibold mb-1">Tips:</h4>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li v-for="(tip,i) in doc.tips" :key="i" :id="`tip-${i}`">{{ tip }}</li>
                                </ul>
                            </div>

                            <!-- Groups & Sections -->
                            <div v-for="(group,gIndex) in doc.groups" :key="gIndex" class="mb-6">
                                <h2 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white mb-2 border-b border-gray-200 dark:border-gray-800 pb-1">
                                    {{ group.group_title }}
                                </h2>
                                <div class="space-y-2">
                                    <div
                                        v-for="(section,sIndex) in group.sections"
                                        :key="sIndex"
                                        :id="`section-${gIndex}-${sIndex}`"
                                        class="flex gap-3 p-3 border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                                    >
                                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20">
                                            <component
                                                :is="iconMap[section.icon] || InfoIcon"
                                                class="w-4 h-4 text-blue-600"
                                            />
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-0.5">
                                                {{ section.label }}
                                            </h4>
                                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                                {{ section.content }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>

</template>

<style scoped>
.modal-enter-active,
.modal-leave-active{ transition: all .25s ease; }
.modal-enter-from,
.modal-leave-to{ opacity:0; transform:scale(.96); }

.custom-scrollbar::-webkit-scrollbar{ width:5px; }
.custom-scrollbar::-webkit-scrollbar-thumb{ background:#cbd5e1; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb{ background:#334155; }
</style>
