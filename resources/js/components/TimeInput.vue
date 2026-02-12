<template>
    <div class="w-full">
        <label v-if="label" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">
            {{ label }} <span v-if="required" class="text-red-600 font-bold">*</span>
        </label>

        <div class="relative">
            <input
                ref="inputRef"
                :value="displayValue"
                @click="openPicker"
                readonly
                type="text"
                placeholder="12:00 PM"
                class="w-full px-3 py-2 pr-10 rounded-md cursor-pointer text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200"
                :class="{ 'border-red-500': error }"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="isOpen"
                ref="dropdownRef"
                :class="['fixed z-[9999] bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden select-none',
                 isMobile ? 'rounded-t-2xl bottom-0 left-0 right-0 w-full' : 'rounded-xl w-[280px]']"
                :style="isMobile ? {} : dropdownStyle"
            >
                <div class="p-4 relative bg-white dark:bg-gray-900">
                    <div v-if="isMobile" class="w-12 h-1 bg-gray-300 dark:bg-gray-700 rounded-full mx-auto mb-4"></div>
                    <div class="selection-highlight"></div>

                    <div class="flex items-center justify-between h-[240px] relative z-10">
                        <div class="scroll-col" ref="hourScrollRef" @scroll="handleScroll($event, 'hour')" @mousedown="startDrag($event, hourScrollRef)">
                            <div class="spacer"></div>
                            <div v-for="h in hours" :key="h" :class="['item', { active: selectedHour === h }]" @click="selectItem(h, 'hour')">{{ h.toString().padStart(2, '0') }}</div>
                            <div class="spacer"></div>
                        </div>
                        <div class="text-xl font-bold text-gray-400 mx-1">:</div>
                        <div class="scroll-col" ref="minuteScrollRef" @scroll="handleScroll($event, 'minute')" @mousedown="startDrag($event, minuteScrollRef)">
                            <div class="spacer"></div>
                            <div v-for="m in minutes" :key="m" :class="['item', { active: selectedMinute === m }]" @click="selectItem(m, 'minute')">{{ m.toString().padStart(2, '0') }}</div>
                            <div class="spacer"></div>
                        </div>
                        <div class="scroll-col period-width" ref="periodScrollRef" @scroll="handleScroll($event, 'period')" @mousedown="startDrag($event, periodScrollRef)">
                            <div class="spacer"></div>
                            <div v-for="p in ['AM', 'PM']" :key="p" :class="['item', { active: selectedPeriod === p }]" @click="selectItem(p, 'period')">{{ p }}</div>
                            <div class="spacer"></div>
                        </div>
                    </div>
                </div>

                <div class="footer-container">
                    <button @click="clearTime" class="btn-secondary">Clear</button>
                    <div class="flex-actions">
                        <button @click="isOpen = false" class="btn-secondary">Cancel</button>
                        <button @click="applyTime" class="btn-primary">OK</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-[9998] bg-black/40 backdrop-blur-[2px]"></div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps(['modelValue', 'label', 'required', 'error'])
const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const isMobile = ref(false)
const inputRef = ref<HTMLElement | null>(null)
const dropdownRef = ref<HTMLElement | null>(null)
const hourScrollRef = ref<HTMLElement | null>(null)
const minuteScrollRef = ref<HTMLElement | null>(null)
const periodScrollRef = ref<HTMLElement | null>(null)

const selectedHour = ref(12)
const selectedMinute = ref(0)
const selectedPeriod = ref('PM')

// Initial hidden state
const dropdownStyle = ref<any>({
    top: '-2000px',
    left: '-2000px',
    opacity: '0',
    visibility: 'hidden'
})

const hours = Array.from({ length: 12 }, (_, i) => i + 1)
const minutes = Array.from({ length: 60 }, (_, i) => i)

const displayValue = computed(() => {
    if (!props.modelValue) return ''
    const [h, m] = props.modelValue.split(':').map(Number)
    const p = h >= 12 ? 'PM' : 'AM'
    const h12 = h % 12 || 12
    return `${h12.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')} ${p}`
})

function positionDropdown() {
    if (!inputRef.value || !dropdownRef.value) return
    isMobile.value = window.innerWidth < 640
    if (isMobile.value) return

    const inputRect = inputRef.value.getBoundingClientRect()
    const dropdownHeight = dropdownRef.value.offsetHeight
    const viewportHeight = window.innerHeight
    const safetyBuffer = 20 // Space between footer and taskbar

    // Check available space below
    const spaceBelow = viewportHeight - inputRect.bottom - safetyBuffer
    const spaceAbove = inputRect.top - safetyBuffer

    let topPosition: number

    // If dropdown height exceeds space below, try to flip to top
    if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
        topPosition = inputRect.top + window.scrollY - dropdownHeight - 8
    } else {
        topPosition = inputRect.bottom + window.scrollY + 8

        // Final check: if it still doesn't fit at the bottom,
        // force it to move up just enough to be visible
        if (topPosition + dropdownHeight > viewportHeight + window.scrollY - safetyBuffer) {
            topPosition = viewportHeight + window.scrollY - dropdownHeight - safetyBuffer
        }
    }

    dropdownStyle.value = {
        top: `${Math.max(window.scrollY + safetyBuffer, topPosition)}px`,
        left: `${inputRect.left + window.scrollX}px`,
        opacity: '1',
        visibility: 'visible'
    }
}

function openPicker() {
    if (props.modelValue) {
        const [h, m] = props.modelValue.split(':').map(Number)
        selectedPeriod.value = h >= 12 ? 'PM' : 'AM'
        selectedHour.value = h % 12 || 12
        selectedMinute.value = m
    }

    isOpen.value = true

    nextTick(() => {
        // We must measure and position after the component is in the DOM
        positionDropdown()

        // Sync scroll wheels
        const itemHeight = 48
        if (hourScrollRef.value) hourScrollRef.value.scrollTop = hours.indexOf(selectedHour.value) * itemHeight
        if (minuteScrollRef.value) minuteScrollRef.value.scrollTop = minutes.indexOf(selectedMinute.value) * itemHeight
        if (periodScrollRef.value) periodScrollRef.value.scrollTop = (selectedPeriod.value === 'AM' ? 0 : 1) * itemHeight
    })
}

// --- DRAG SCROLL LOGIC ---
let isDragging = false; let startY = 0; let startScrollTop = 0;
function startDrag(e: MouseEvent, el: HTMLElement | null) {
    if (!el) return; isDragging = true; startY = e.pageY; startScrollTop = el.scrollTop; el.style.scrollSnapType = 'none';
    const move = (mE: MouseEvent) => { if (isDragging) el.scrollTop = startScrollTop - (mE.pageY - startY) };
    const up = () => { isDragging = false; el.style.scrollSnapType = 'y mandatory'; window.removeEventListener('mousemove', move); window.removeEventListener('mouseup', up) };
    window.addEventListener('mousemove', move); window.addEventListener('mouseup', up)
}
function handleScroll(e: Event, type: string) { if (isDragging) return; const el = e.target as HTMLElement; const idx = Math.round(el.scrollTop / 48); if (type === 'hour') selectedHour.value = hours[idx] || 12; if (type === 'minute') selectedMinute.value = minutes[idx] ?? 0; if (type === 'period') selectedPeriod.value = idx === 0 ? 'AM' : 'PM' }
function selectItem(val: any, type: string) { let el = type === 'hour' ? hourScrollRef.value : type === 'minute' ? minuteScrollRef.value : periodScrollRef.value; let idx = type === 'hour' ? hours.indexOf(val) : type === 'minute' ? minutes.indexOf(val) : (val === 'AM' ? 0 : 1); el?.scrollTo({ top: idx * 48, behavior: 'smooth' }) }
function applyTime() { let h = selectedHour.value; if (selectedPeriod.value === 'PM' && h !== 12) h += 12; if (selectedPeriod.value === 'AM' && h === 12) h = 0; emit('update:modelValue', `${h.toString().padStart(2, '0')}:${selectedMinute.value.toString().padStart(2, '0')}`); isOpen.value = false }
function clearTime() { emit('update:modelValue', ''); isOpen.value = false }

onMounted(() => { window.addEventListener('resize', positionDropdown); window.addEventListener('scroll', positionDropdown, true) })
onBeforeUnmount(() => { window.removeEventListener('resize', positionDropdown); window.removeEventListener('scroll', positionDropdown, true) })
</script>

<style scoped>
.scroll-col {
    height: 100%; overflow-y: scroll; scroll-snap-type: y mandatory;
    scrollbar-width: none; cursor: grab; flex: 1; display: flex; flex-direction: column; align-items: center;
}
.scroll-col::-webkit-scrollbar { display: none; }
.period-width { flex-shrink: 0; width: 64px; }
.item {
    height: 48px; min-height: 48px; display: flex; align-items: center; justify-content: center;
    scroll-snap-align: center; font-size: 1.25rem; font-weight: 500; color: #94a3b8; transition: all 0.2s ease; width: 100%;
}
.item.active { color: #4f46e5; font-size: 1.5rem; font-weight: 700; }
.spacer { min-height: 96px; }

.selection-highlight {
    position: absolute; left: 1rem; right: 1rem; top: 112px; height: 48px;
    background-color: rgba(79, 70, 229, 0.1); border-top: 1px solid rgba(79, 70, 229, 0.3);
    border-bottom: 1px solid rgba(79, 70, 229, 0.3); pointer-events: none; border-radius: 4px;
}

.footer-container { display: flex; padding: 0.75rem; gap: 0.5rem; border-top: 1px solid #e2e8f0; background-color: #f8fafc; }
.dark .footer-container { border-color: #334155; background-color: #0f172a; }
.flex-actions { display: flex; gap: 0.5rem; flex: 1; }
.btn-primary { background-color: #4f46e5; color: white; border: none; border-radius: 0.5rem; padding: 0.5rem 1rem; font-weight: 700; font-size: 0.875rem; cursor: pointer; flex: 1; }
.btn-secondary { background-color: #f1f5f9; color: #475569; border: none; border-radius: 0.5rem; padding: 0.5rem 1rem; font-weight: 700; font-size: 0.875rem; cursor: pointer; }
.dark .btn-secondary { background-color: #1e293b; color: #cbd5e1; }
</style>
