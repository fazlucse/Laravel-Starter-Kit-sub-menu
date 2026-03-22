<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import {
    HelpCircle, X, BookOpen, Smartphone, ShieldCheck,
    MousePointer2, UserCheck, CalendarDays, Zap, Lock,
    CheckSquare, InfoIcon, LayoutList, ClipboardPen, FileSpreadsheet, Image as ImageIcon
} from "lucide-vue-next";

// Interfaces to ensure data safety
interface ExcelFormat {
    title: string;
    headers: string[];
    rows: any[][];
    note?: string;
}

interface HelpSection {
    label: string;
    content: string;
    icon: string;
    image?: string;
}

interface HelpGroup {
    group_title: string;
    sections: HelpSection[];
}

const props = defineProps<{
    doc: {
        title: string
        description?: string
        tips?: string[]
        excel_format?: ExcelFormat
        groups: HelpGroup[]
    }
}>()

const isOpen = ref(false)
const close = () => isOpen.value = false

const iconMap: any = {
    Smartphone, ShieldCheck, MousePointer2, UserCheck,
    CalendarDays, Zap, Lock, CheckSquare, LayoutList,
    ClipboardPen, FileSpreadsheet, ImageIcon
}

const scrollToSection = (id: string) => {
    const el = document.getElementById(id)
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" })
}

const handleEsc = (e: KeyboardEvent) => { if (e.key === "Escape") close() }
onMounted(() => window.addEventListener("keydown", handleEsc))
onUnmounted(() => window.removeEventListener("keydown", handleEsc))
</script>

<template>
    <button @click="isOpen = true" class="flex items-center gap-2 px-3 py-2 text-xs font-semibold bg-blue-50 text-blue-600 rounded-md border border-blue-200 hover:bg-blue-100 transition cursor-pointer">
        <HelpCircle class="w-4 h-4"/> User Guide
    </button>

    <Teleport to="body">
        <Transition name="modal">
            <div v-if="isOpen && doc" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-2 sm:p-4">
                <div class="bg-white dark:bg-gray-950 w-[95%] h-[95%] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden rounded-2xl">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-600 text-white rounded-lg shadow-lg shadow-blue-500/20">
                                <BookOpen class="w-5 h-5"/>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ doc.title }}</h3>
                        </div>
                        <button @click="close" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition cursor-pointer">
                            <X class="w-6 h-6 text-gray-400"/>
                        </button>
                    </div>

                    <div class="flex flex-1 overflow-hidden flex-col md:flex-row">
                        <div class="w-full md:w-64 border-b md:border-b-0 md:border-r border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-900/50 p-5 overflow-y-auto custom-scrollbar">
                            <div class="space-y-6">
                                <div v-if="doc.description">
                                    <h4 @click="scrollToSection('description')" class="cursor-pointer text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2">Overview</h4>
                                </div>

                                <div v-if="doc.excel_format">
                                    <h4 @click="scrollToSection('excel-preview')" class="cursor-pointer text-[10px] font-black uppercase tracking-widest text-green-600 mb-2">Excel Structure</h4>
                                </div>

                                <div v-for="(group, gIndex) in doc.groups" :key="gIndex">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">{{ group.group_title }}</h4>
                                    <ul class="space-y-2">
                                        <li v-for="(section, sIndex) in group.sections" :key="sIndex"
                                            @click="scrollToSection(`section-${gIndex}-${sIndex}`)"
                                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 cursor-pointer transition truncate flex items-center gap-2">
                                            <div class="size-1 bg-gray-300 rounded-full"></div> {{ section.label }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 sm:p-10 custom-scrollbar space-y-12">

                            <section v-if="doc.description" id="description" class="text-base text-gray-600 dark:text-gray-400 leading-relaxed border-l-4 border-blue-500 pl-4 py-1">
                                {{ doc.description }}
                            </section>

                            <section v-if="doc.excel_format" id="excel-preview" class="p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30">
                                <div class="flex items-center gap-2 mb-4">
                                    <FileSpreadsheet class="w-5 h-5 text-green-600" />
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ doc.excel_format.title }}</h3>
                                </div>
                                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <table class="w-full text-left text-xs font-mono border-collapse bg-white dark:bg-black">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                        <tr>
                                            <th v-for="h in doc.excel_format.headers" :key="h" class="p-3 border-b border-gray-200 dark:border-gray-700 font-bold text-gray-700 dark:text-gray-200">{{ h }}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="(row, i) in doc.excel_format.rows" :key="i">
                                            <td v-for="cell in row" :key="cell" class="p-3 text-gray-600 dark:text-gray-400">{{ cell }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-if="doc.excel_format.note" class="mt-4 text-xs font-medium text-orange-600 flex items-center gap-2">
                                    <InfoIcon class="w-4 h-4" /> {{ doc.excel_format.note }}
                                </p>
                            </section>

                            <div v-for="(group, gIndex) in doc.groups" :key="gIndex" class="space-y-6">
                                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight border-b-2 border-gray-100 dark:border-gray-800 pb-2">
                                    {{ group.group_title }}
                                </h2>

                                <div class="grid gap-6">
                                    <div
                                        v-for="(section, sIndex) in group.sections"
                                        :key="sIndex"
                                        :id="`section-${gIndex}-${sIndex}`"
                                        class="p-6 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900/50 hover:border-blue-200 dark:hover:border-blue-900 transition-all shadow-sm"
                                    >
                                        <div class="flex gap-4 mb-4">
                                            <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl h-fit">
                                                <component :is="iconMap[section.icon] || InfoIcon" class="w-5 h-5 text-blue-600"/>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-1">{{ section.label }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ section.content }}</p>
                                            </div>
                                        </div>

                                        <div v-if="section.image" class="mt-4 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                                            <img
                                                :src="section.image"
                                                class="w-full h-auto object-contain max-h-[450px] hover:scale-[1.01] transition-transform duration-500"
                                                alt="Visual Guide"
                                            />
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
.modal-enter-active, .modal-leave-active { transition: all .3s ease-out; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.98); }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
