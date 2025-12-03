<!-- components/common/MultiSelect.vue -->
<template>
    <div class="relative" ref="dropdownRef">
        <!-- Trigger Button -->
        <button
            @click="isOpen = !isOpen"
            class="w-full bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg px-4 py-3 text-left hover:border-indigo-500 dark:hover:border-indigo-400 focus:outline-none focus:border-indigo-500 transition-all shadow-sm min-h-[48px] flex items-center justify-between"
        >
            <div class="flex flex-wrap gap-2 items-center flex-1">
                <template v-if="selectedItems.length === 0">
                    <span class="text-gray-400">Select {{ multiple ? 'items' : 'item' }}...</span>
                </template>
                <template v-else>
          <span
              v-for="item in selectedItems"
              :key="item[idKey]"
              class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-medium"
          >
            {{ getLabel(item) }}
            <button
                v-if="multiple"
                @click.stop="removeItem(item)"
                class="hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-full p-0.5 transition"
            >
              <X class="w-3 h-3" />
            </button>
          </span>
                </template>
            </div>
            <ChevronDown
                class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform ml-2"
                :class="{ 'rotate-180': isOpen }"
            />
        </button>

        <!-- Dropdown Menu -->
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
                class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-lg shadow-xl overflow-hidden"
            >
                <!-- Search -->
                <div class="p-3 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all"
                            @click.stop
                        />
                    </div>
                </div>

                <!-- Items List -->
                <div class="max-h-64 overflow-y-auto">
                    <template v-if="filteredItems.length > 0">
                        <button
                            v-for="item in filteredItems"
                            :key="item[idKey]"
                            @click="toggleItem(item)"
                            class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 last:border-0"
                        >
                            <div
                                class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                :class="isSelected(item) ? 'bg-indigo-500 border-indigo-500' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                            >
                                <Check v-if="isSelected(item)" class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ getLabel(item) }}</div>
                                <div v-if="item.category" class="text-sm text-gray-500 dark:text-gray-400">{{ item.category }}</div>
                            </div>
                        </button>
                    </template>
                    <div v-else class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        No items found
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex gap-2">
                    <button
                        v-if="multiple"
                        @click="clearAll"
                        class="flex-1 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition font-medium"
                    >
                        Clear All
                    </button>
                    <button
                        @click="isOpen = false"
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium"
                    >
                        Done
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onClickOutside } from 'vue'
import { Search, ChevronDown, X, Check } from 'lucide-vue-next'

const props = defineProps({
    items: { type: Array, required: true },
    modelValue: { type: Array, default: () => [] },
    multiple: { type: Boolean, default: true },
    labelKey: { type: String, default: 'name' },
    idKey: { type: String, default: 'id' },
    placeholder: { type: String, default: 'Select items...' }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')
const dropdownRef = ref(null)

onClickOutside(dropdownRef, () => (isOpen.value = false))

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items
    const q = searchQuery.value.toLowerCase()
    return props.items.filter(item => {
        const label = getLabel(item).toLowerCase()
        const category = item.category?.toLowerCase() || ''
        return label.includes(q) || category.includes(q)
    })
})

const selectedItems = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})

const isSelected = (item) => {
    return selectedItems.value.some(i => i[props.idKey] === item[props.idKey])
}

const getLabel = (item) => item[props.labelKey] || item.name || 'Unknown'

const toggleItem = (item) => {
    if (!props.multiple) {
        selectedItems.value = [item]
        isOpen.value = false
        return
    }

    if (isSelected(item)) {
        removeItem(item)
    } else {
        selectedItems.value = [...selectedItems.value, item]
    }
}

const removeItem = (item) => {
    selectedItems.value = selectedItems.value.filter(i => i[props.idKey] !== item[props.idKey])
}

const clearAll = () => {
    selectedItems.value = []
    searchQuery.value = ''
}
</script>
