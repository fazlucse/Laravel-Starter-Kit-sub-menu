<template>
    <div class="relative" ref="dropdownRef">
        <!-- Label (optional) -->
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Trigger Button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="w-full bg-white dark:bg-gray-900 border-2 rounded-lg px-4 py-3 text-left hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition-all shadow-sm min-h-[48px] flex items-start justify-between"
            :class="[
                isOpen ? 'border-indigo-500' : (hasError ? 'border-red-500' : 'border-gray-300 dark:border-gray-700')
            ]"
        >
            <div class="flex flex-wrap gap-2 items-center flex-1 max-h-[200px] overflow-y-auto pr-2 scrollbar-thin">
                <!-- Placeholder -->
                <span v-if="selectedItems.length === 0" class="text-gray-400">
                    {{ placeholder }}
                </span>

                <!-- Selected Items (Tags) -->
                <template v-else>
                    <span
                        v-for="item in selectedItems"
                        :key="item[idKey]"
                        class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-medium whitespace-nowrap"
                    >
                        {{ getLabel(item) }}
                        <button
                            v-if="multiple"
                            type="button"
                            @click.stop="removeItem(item)"
                            class="hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-full p-0.5 transition"
                        >
                            <X class="w-3 h-3" />
                        </button>
                    </span>
                </template>
            </div>
            <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                <button
                    v-if="selectedItems.length > 0"
                    type="button"
                    @click.stop="clearAll"
                    class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition"
                    title="Clear all"
                >
                    <X class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </button>
                <ChevronDown
                    class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform"
                    :class="{ 'rotate-180': isOpen }"
                />
            </div>
        </button>

        <!-- Error Message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errorMessage }}
        </p>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            leave-active-class="transition duration-150 ease-in"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-[9999] w-full bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg shadow-xl overflow-hidden max-h-[80vh] flex flex-col"
                :class="dropdownPosition === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
            >
                <!-- Search -->
                <div class="p-3 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-inherit z-10">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-indigo-500 transition-all"
                            @click.stop
                            ref="searchInput"
                        />
                    </div>
                </div>

                <!-- Options -->
                <div class="max-h-48 overflow-y-auto flex-1">
                    <template v-if="filteredItems.length">
                        <button
                            v-for="item in filteredItems"
                            :key="item[idKey]"
                            type="button"
                            @click="toggleItem(item)"
                            class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 last:border-0"
                        >
                            <div
                                class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0"
                                :class="isSelected(item) ? 'bg-indigo-500 border-indigo-500' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                            >
                                <Check v-if="isSelected(item)" class="w-4 h-4 text-white" />
                            </div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ getLabel(item) }}
                            </div>
                        </button>
                    </template>
                    <div v-else class="px-4 py-8 text-center text-gray-500">No items found</div>
                </div>

                <!-- Footer -->
                <div class="cursor-pointer p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex gap-2 flex-shrink-0">
                    <button
                        v-if="selectedItems.length > 0"
                        type="button"
                        @click="clearAll"
                        class="flex-1 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition font-medium"
                    >
                        Clear All
                    </button>
                    <button
                        type="button"
                        @click="isOpen = false"
                        class="cursor-pointer flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium"
                    >
                        Done
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { Search, ChevronDown, Check, X } from 'lucide-vue-next'

const props = defineProps({
    items: { type: Array, required: true },
    modelValue: { type: [Array, Object, null], default: () => [] },
    multiple: { type: Boolean, default: false },
    labelKey: { type: String, default: 'name' },
    idKey: { type: String, default: 'id' },
    placeholder: { type: String, default: 'Select item(s)...' },
    label: { type: String, default: '' },
    required: { type: Boolean, default: false },
    errorMessage: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')
const dropdownRef = ref(null)
const searchInput = ref(null)
const dropdownPosition = ref('bottom')

// Computed property to check if there's an error
const hasError = computed(() => {
    return props.errorMessage && props.errorMessage.trim() !== ''
})

onClickOutside(dropdownRef, () => (isOpen.value = false))

watch(isOpen, (open) => {
    if (open) {
        nextTick(() => {
            searchInput.value?.focus()
            checkDropdownPosition()
        })
    } else {
        searchQuery.value = ''
    }
})

const checkDropdownPosition = () => {
    if (!dropdownRef.value) return

    const rect = dropdownRef.value.getBoundingClientRect()
    const spaceBelow = window.innerHeight - rect.bottom
    const spaceAbove = rect.top
    const dropdownHeight = 450

    dropdownPosition.value = spaceBelow < dropdownHeight && spaceAbove > spaceBelow ? 'top' : 'bottom'
}

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items
    const q = searchQuery.value.toLowerCase()
    return props.items.filter(item =>
        getLabel(item).toLowerCase().includes(q)
    )
})

const selectedItems = computed({
    get() {
        if (props.multiple) {
            return Array.isArray(props.modelValue) ? props.modelValue : []
        } else {
            return props.modelValue ? [props.modelValue] : []
        }
    },
    set(value) {
        if (props.multiple) {
            emit('update:modelValue', value)
        } else {
            emit('update:modelValue', value[0] ?? null)
        }
    }
})

const isSelected = (item) => {
    return selectedItems.value.some(selected => selected[props.idKey] === item[props.idKey])
}

const getLabel = (item) => {
    return item?.[props.labelKey] ?? 'Unknown'
}

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

const clearAll = () => {
    selectedItems.value = []
}
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.3);
    border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.5);
}

.max-h-64::-webkit-scrollbar {
    width: 6px;
}
.max-h-64::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}
</style>
