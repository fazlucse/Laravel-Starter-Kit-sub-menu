<template>
    <label class="flex items-center gap-3 cursor-pointer">
        <input
            type="radio"
            :value="value"
            :checked="modelValue === value"
            @change="$emit('update:modelValue', value)"
            class="w-5 h-5 text-indigo-600 focus:ring-indigo-500"
        />
        <span class="font-medium text-gray-700">{{ label }}</span>
    </label>
</template>

<script setup>
defineProps({
    modelValue: String,
    value: String,
    label: String
})
defineEmits(['update:modelValue'])
</script>
