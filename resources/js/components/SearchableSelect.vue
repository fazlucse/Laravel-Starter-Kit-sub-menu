<template>
    <div class="relative w-full" ref="dropdownRef">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
        </label>

        <div
            @click="toggleDropdown"
            class="w-full border border-gray-300 dark:border-gray-700 dark:bg-white dark:text-gray-900 rounded-md shadow-sm p-2.5 cursor-pointer flex justify-between items-center transition-all hover:border-blue-500 bg-white"
        >
            <span :class="{ 'text-gray-400': !modelValue }">
                {{ selectedLabel || placeholder }}
            </span>
            <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{'rotate-180': isDropdownOpen}" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>

        <div
            v-if="isDropdownOpen"
            :class="[
                'absolute left-0 right-0 z-[9999] bg-white border border-gray-300 rounded-md shadow-2xl min-w-[250px]',
                openUpward ? 'bottom-full mb-2' : 'top-full mt-1'
            ]"
        >
            <div class="p-2 border-b sticky top-0 bg-gray-50">
                <input
                    type="text"
                    v-model="searchQuery"
                    ref="searchInput"
                    placeholder="Search..."
                    class="w-full text-sm p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none"
                    @click.stop
                />
            </div>

            <ul class="max-h-[250px] overflow-y-auto">
                <li
                    v-for="item in filteredItems"
                    :key="item[valueKey]"
                    @click="selectItem(item)"
                    class="px-4 py-3 text-sm hover:bg-blue-600 hover:text-white cursor-pointer border-b last:border-0"
                >
                    <div class="font-bold">{{ item[labelKey] }}</div>
                    <div v-if="subLabelKey" class="text-xs opacity-75">{{ item[subLabelKey] }}</div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    modelValue: [String, Number],
    items: Array,
    label: String,
    placeholder: { type: String, default: 'Select Option' },
    labelKey: { type: String, default: 'person_name' },
    valueKey: { type: String, default: 'id' },
    subLabelKey: { type: String, default: 'person_id' }
})

const emit = defineEmits(['update:modelValue'])

const dropdownRef = ref(null)
const searchInput = ref(null)
const isDropdownOpen = ref(false)
const searchQuery = ref('')
const openUpward = ref(false) // State to track direction

// Compute display name
const selectedLabel = computed(() => {
    const item = props.items.find(i => i[props.valueKey] === props.modelValue)
    return item ? `${item[props.labelKey]}` : ''
})

// Filter items
const filteredItems = computed(() => {
    const q = searchQuery.value.toLowerCase()
    return props.items.filter(item =>
        String(item[props.labelKey]).toLowerCase().includes(q) ||
        String(item[props.subLabelKey]).toLowerCase().includes(q)
    )
})

const toggleDropdown = async (event) => {
    if (!isDropdownOpen.value) {
        // --- SPACE CALCULATION LOGIC ---
        const rect = dropdownRef.value.getBoundingClientRect()
        const spaceBelow = window.innerHeight - rect.bottom
        const dropdownHeight = 320 // max-h (250) + search bar + padding

        // If space below is less than dropdown height, open UP
        openUpward.value = spaceBelow < dropdownHeight

        isDropdownOpen.value = true
        await nextTick()
        searchInput.value?.focus()
    } else {
        isDropdownOpen.value = false
    }
}

const selectItem = (item) => {
    emit('update:modelValue', item[props.valueKey])
    isDropdownOpen.value = false
    searchQuery.value = ''
}

// Close when clicking outside
const closeOnOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isDropdownOpen.value = false
    }
}

onMounted(() => window.addEventListener('mousedown', closeOnOutside))
onUnmounted(() => window.removeEventListener('mousedown', closeOnOutside))
</script>
