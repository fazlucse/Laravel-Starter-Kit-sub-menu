<template>
    <fieldset class="border border-gray-300 rounded-lg p-5 bg-gray-50">
        <legend class="px-3 text-lg font-semibold text-gray-800">
            {{ label }} <span class="text-red-500">*</span>
        </legend>

        <div class="flex flex-wrap gap-6 mb-5">
            <RadioOption v-model="local.type" value="Office" label="Office" />
            <RadioOption v-model="local.type" value="Home" label="Home" />
            <RadioOption v-model="local.type" value="Factory" label="Factory" />
            <RadioOption v-model="local.type" value="Sub Factory" label="Sub Factory" />
            <RadioOption v-model="local.type" value="Other" label="Other" />
        </div>


        <div v-if="showFactorySelect" class="mb-4">
            <FormSelect v-model="local.factory" label="Factory Name" :options="factories" required />
        </div>

        <div v-if="local.type === 'Sub Factory'" class="mb-4">
            <FormSelect v-model="local.sub_factory" label="Sub Factory Name" :options="subFactories" required />
        </div>

        <div v-if="local.type === 'Other'">
            <FormInput v-model="local.new_name" label="Other Location Name" required />
        </div>
    </fieldset>
</template>

<script setup>
import { reactive, watch, computed } from 'vue'
import FormSelect from '@/components/movement/FormSelect.vue'
import FormInput from '@/components/movement/FormInput.vue'
import RadioOption from '@/components/movement/RadioOption.vue'

const props = defineProps({
    modelValue: Object,
    label: String,
    factories: Array,
    subFactories: Array
})

const emit = defineEmits(['update:modelValue'])

const local = reactive({ ...props.modelValue })

watch(local, (val) => emit('update:modelValue', { ...val }), { deep: true })
watch(() => props.modelValue, (val) => Object.assign(local, val), { deep: true })

const showFactorySelect = computed(() => {
    return props.modelValue.type === 'Factory' ||
        (props.modelValue.type === 'Other' && props.modelValue.ex_type === 'Existing Location')
})
</script>
