<template>
    <fieldset class="border rounded-lg p-4">
        <legend class="px-2 text-sm font-medium text-gray-700">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </legend>

        <!-- Location Type Radios -->
        <div class="flex flex-wrap gap-6 mb-4">
            <label v-for="opt in locationOptions" :key="opt" class="flex items-center space-x-2 cursor-pointer">
                <input
                    type="radio"
                    :value="opt"
                    :checked="modelValue === opt"
                    @change="$emit('update:modelValue', opt)"
                    class="w-5 h-5 text-blue-600"
                />
                <span>{{ opt }}</span>
            </label>
        </div>

        <!-- If 'Other', show existing/new selection -->
        <div v-if="modelValue === 'Other'" class="flex gap-8 mb-4">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input
                    type="radio"
                    :value="'Existing Location'"
                    :checked="subLocationType === 'Existing Location'"
                    @change="$emit('update:subLocationType', 'Existing Location')"
                    class="w-5 h-5 text-blue-600"
                />
                <span>Existing Location</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input
                    type="radio"
                    :value="'New Location'"
                    :checked="subLocationType === 'New Location'"
                    @change="$emit('update:subLocationType', 'New Location')"
                    class="w-5 h-5 text-blue-600"
                />
                <span>New Location</span>
            </label>
        </div>

        <!-- New location input -->
        <input
            v-if="subLocationType === 'New Location'"
            type="text"
            :value="newLocationName"
            @input="$emit('update:newLocationName', $event.target.value)"
            placeholder="Enter new location"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
    </fieldset>
</template>

<script setup>
const props = defineProps({
    label: String,
    required: Boolean,
    modelValue: String,
    locationOptions: Array,
    subLocationType: String,
    newLocationName: String
})

const emit = defineEmits([
    'update:modelValue',
    'update:subLocationType',
    'update:newLocationName'
])
</script>
