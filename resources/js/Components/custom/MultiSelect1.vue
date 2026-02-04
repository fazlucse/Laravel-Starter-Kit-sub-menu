<!-- components/custom/MultiSelect.vue -->
<template>
    <div class="relative inline-block w-full" ref="root">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="w-full bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg px-4 py-3 text-left hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition-all shadow-sm min-h-[48px] flex items-center justify-between"
            :class="{ 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-800': isOpen }"
        >
            <div class="flex flex-wrap gap-2 items-center flex-1 pr-2">
        <span v-if="selectedItems.length === 0" class="text-gray-400">
          {{ placeholder }}
        </span>
                <template v-else>
          <span
              v-for="item in selectedItems"
              :key="item[idKey]"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-medium"
          >
            {{ getLabel(item) }}
            <button
                v-if="multiple"
                type="button"
                @click.stop="removeItem(item)"
                class="ml-1 hover:bg-indigo-300 dark:hover:bg-indigo-700 rounded-full p-0.5"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </span>
                </template>
            </div>
            <ChevronDown
                class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform flex-shrink-0 ml-2"
                :class="{ 'rotate-180': isOpen }"
            />
        </button>

        <!-- SINGLE Floating Dropdown - Attached to Field -->
        <Teleport to="body">
            <div
                v-if="isOpen"
                ref="floating"
                class="fixed z-[9999] bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg shadow-2xl overflow-hidden flex flex-col"
                :style="floatingStyle"
                @click.stop
            >
                <!-- Search -->
                <div class="sticky top-0 p-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 z-10">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-indigo-500 text-sm"
                            ref="searchInput"
                        />
                    </div>
                </div>

                <!-- Options -->
                <div class="flex-1 overflow-y-auto max-h-96">
                    <template v-if="filteredItems.length">
                        <button
                            v-for="item in filteredItems"
                            :key="item[idKey]"
                            type="button"
                            @click="toggleItem(item)"
                            class="w-full text-left px-5 py-3.5 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors flex items-center gap-4 border-b border-gray-100 dark:border-gray-800 last:border-0"
                        >
                            <div
                                class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0"
                                :class="isSelected(item) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-400 dark:border-gray-600'"
                            >
                                <Check v-if="isSelected(item)" class="w-4 h-4 text-white" />
                            </div>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">
                {{ getLabel(item) }}
              </span>
                        </button>
                    </template>
                    <div v-else class="py-12 text-center text-gray-500 dark:text-gray-400">
                        No items found
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex gap-3 z-10">
                    <button
                        v-if="multiple"
                        type="button"
                        @click="clearAll"
                        class="flex-1 px-5 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition font-medium"
                    >
                        Clear All
                    </button>
                    <button
                        type="button"
                        @click="isOpen = false"
                        class="flex-1 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium shadow-sm"
                    >
                        Done
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { Search, ChevronDown, Check, X } from 'lucide-vue-next'

const props = defineProps({
    items: { type: Array, required: true },
    modelValue: { type: [Array, Object, null], default: () => [] },
    multiple: { type: Boolean, default: false },
    labelKey: { type: String, default: 'name' },
    idKey: { type: String, default: 'id' },
    placeholder: { type: String, default: 'Select...' }
})

const emit = defineEmits(['update:modelValue'])

const root = ref(null)
const floating = ref(null)
const searchInput = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')

const floatingStyle = ref({})

const updatePosition = () => {
    if (!root.value || !floating.value) return

    const trigger = root.value.getBoundingClientRect()
    const dropdownHeight = floating.value.offsetHeight || 450

    const spaceBelow = window.innerHeight - trigger.bottom - 10
    const spaceAbove = trigger.top - 10

    const openUpward = spaceBelow < dropdownHeight && spaceAbove >= dropdownHeight

    floatingStyle.value = {
        position: 'fixed',
        left: `${trigger.left}px`,
        width: `${trigger.width}px`,
        maxHeight: '80vh',

        // ONE dropdown only — attached perfectly
        top: openUpward ? `${trigger.top - dropdownHeight - 8}px` : `${trigger.bottom + 8}px`,
        transform: 'none'
    }

    nextTick(() => searchInput.value?.focus())
}

watch(isOpen, (open) => {
    if (open) {
        nextTick(updatePosition)
    } else {
        searchQuery.value = ''
    }
})

const reposition = () => isOpen.value && updatePosition()
onMounted(() => {
    window.addEventListener('scroll', reposition, true)
    window.addEventListener('resize', reposition)
})
onBeforeUnmount(() => {
    window.removeEventListener('scroll', reposition, true)
    window.removeEventListener('resize', reposition)
})

onClickOutside(root, () => {
    isOpen.value = false
    searchQuery.value = ''
})

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items
    const q = searchQuery.value.toLowerCase()
    return props.items.filter(item => getLabel(item).toLowerCase().includes(q))
})

const selectedItems = computed({
    get() {
        if (props.multiple) return Array.isArray(props.modelValue) ? props.modelValue : []
        return props.modelValue ? [props.modelValue] : []
    },
    set(val) {
        if (props.multiple) emit('update:modelValue', val)
        else emit('update:modelValue', val[0] ?? null)
    }
})

const isSelected = (item) => selectedItems.value.some(i => i[props.idKey] === item[props.idKey])
const getLabel = (item) => item?.[props.labelKey] ?? 'Unknown'

const toggleItem = (item) => {
    if (!props.multiple) {
        selectedItems.value = [item]
        isOpen.value = false
        return
    }
    if (isSelected(item)) {
        selectedItems.value = selectedItems.value.filter(i => i[props.idKey] !== item[props.idKey])
    } else {
        selectedItems.value = [...selectedItems.value, item]
    }
}

const removeItem = (item) => {
    selectedItems.value = selectedItems.value.filter(i => i[props.idKey] !== item[props.idKey])
}

const clearAll = () => selectedItems.value = []
</script>

<style scoped>
.max-h-96::-webkit-scrollbar {
    width: 6px;
}
.max-h-96::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.3);
    border-radius: 3px;
}
.max-h-96::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.5);
}
</style>
