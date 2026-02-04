<!-- resources/js/components/form/FormSelect.vue -->
<template>
    <div class="relative">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Search Input -->
        <input
            type="text"
            v-model="displayValue"
            @focus="showDropdown = true"
            @blur="hideDropdown"
            :placeholder="placeholder"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
             placeholder-gray-400 text-gray-900"
        />

        <!-- Dropdown Options -->
        <div
            v-if="showDropdown && filteredOptions.length > 0"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto"
        >
            <button
                v-for="opt in filteredOptions"
                :key="opt.id"
                @mousedown.prevent="select(opt)"
                class="w-full text-left px-4 py-2.5 hover:bg-indigo-100 focus:bg-indigo-100 transition text-gray-800"
                :class="{ 'bg-indigo-100 font-medium': opt.id === modelValue }"
            >
                {{ opt.name }}
            </button>
        </div>

        <!-- No Results -->
        <div
            v-if="showDropdown && filteredOptions.length === 0 && searchQuery"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg p-4 text-center text-gray-500 shadow-xl"
        >
            No results found
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    options: {
        type: Array,
        default: () => []
    },
    required: Boolean,
    placeholder: {
        type: String,
        default: 'Type to search or select...'
    }
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')
const showDropdown = ref(false)

// Safe options
const safeOptions = computed(() => props.options || [])

// Filtered results
const filteredOptions = computed(() => {
    if (!searchQuery.value.trim()) return safeOptions.value
    return safeOptions.value.filter(opt =>
        opt?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

// Display value inily in input (shows selected name or blank)
const displayValue = computed({
    get() {
        if (!props.modelValue) return ''
        const selected = safeOptions.value.find(o => o.id === props.modelValue)
        return selected ? selected.name : ''
    },
    set(val) {
        searchQuery.value = val
    }
})

// When modelValue changes from outside (e.g. form reset), update display
watch(() => props.modelValue, () => {
    if (!props.modelValue) {
        searchQuery.value = ''
    }
}, { immediate: true })

const select = (opt) => {
    emit('update:modelValue', opt.id)
    searchQuery.value = opt.name
    showDropdown.value = false
}

const hideDropdown = () => {
    setTimeout(() => {
        showDropdown.value = false
        // Restore selected name when closing without selection
        if (props.modelValue) {
            const selected = safeOptions.value.find(o => o.id === props.modelValue)
            if (selected) searchQuery.value = selected.name
        }
    }, 150)
}
</script>
