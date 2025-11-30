<template>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :readonly="readonly"
            :required="required"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100 transition"
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
