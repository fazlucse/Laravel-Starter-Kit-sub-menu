<template>
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>

        <input
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :readonly="readonly"
            :required="required"
            class="w-full px-4 py-2.5 border
                   border-gray-300 dark:border-gray-600
                   rounded-lg
                   text-gray-900 dark:text-white
                   bg-white dark:bg-gray-800
                   focus:ring-2 focus:ring-indigo-500
                   disabled:bg-gray-100 dark:disabled:bg-gray-700
                   transition"
        />
    </div>
</template>

<script setup>
defineProps({
    modelValue: [String, Number],
    label: String,
    type: { type: String, default: 'text' },
    readonly: Boolean,
    required: Boolean
})
defineEmits(['update:modelValue'])
</script>
