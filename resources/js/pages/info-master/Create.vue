<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {useForm, Link, router} from '@inertiajs/vue3';
import {watch} from 'vue';
import {
    ChevronLeft,
    Save,
    Info,
    Hash,
    Type,
    Layers,
    GitBranch
} from 'lucide-vue-next';

const props = defineProps({
    currentType: String,
    masterTypes: Array < string >, // Now receiving this from the Controller
    parents: Array < {id: number, name: string} >,
    parentLabel: String
});

const form = useForm({
    type: props.currentType || 'country',
    name: '',
    code: '',
    description: '',
    parent_id: null as number | null,
});

// Helper function to make DB types look pretty
const formatLabel = (text: string) => {
    return text.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Sync parents when type changes
watch(() => form.type, (newType) => {
    router.reload({
        data: {type: newType},
        only: ['parents', 'parentLabel'],
        preserveState: true
    });
});

const submit = () => {
    form.post('/info-masters/store', {
        onSuccess: () => form.reset('name', 'code', 'description'),
    });
};
</script>

<template>
    <AppLayout title="Add Master Data">
        <div class="py-2 px-4 sm:px-6 lg:px-2 p-2 sm:p-4">
            <div class="max-w-3xl mx-auto p-2 sm:p-3">

                <div class="flex items-center gap-4 mb-8">
                    <Link
                        :href="`/info-masters?type=${form.type}`"
                        class="p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <ChevronLeft class="w-5 h-5 text-gray-500"/>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                            New Master Entry
                        </h1>
                        <p class="text-[10px] text-blue-600 font-black uppercase tracking-widest">General
                            Configuration</p>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 sm:p-12 space-y-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">
                                    <Layers class="w-3 h-3"/>
                                    Entry Type
                                </label>
                                <select
                                    v-model="form.type"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 p-4 text-sm font-bold focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none"
                                >
                                    <option v-for="t in masterTypes" :key="t" :value="t">
                                        {{ formatLabel(t) }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">
                                    <Hash class="w-3 h-3"/>
                                    Unique Code
                                </label>
                                <input
                                    v-model="form.code"
                                    type="text"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 p-4 text-sm font-mono uppercase focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    placeholder="E.G. BD-01"
                                />
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label
                                    class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">
                                    <Type class="w-3 h-3"/>
                                    Display Name
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 p-4 text-sm font-bold focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    placeholder="Enter full name..."
                                    required
                                />
                                <div v-if="form.errors.name"
                                     class="text-red-500 text-[10px] font-bold uppercase mt-1 ml-2">{{
                                        form.errors.name
                                    }}
                                </div>
                            </div>

                            <div v-if="parents && parents.length > 0" class="md:col-span-2 space-y-2">
                                <label
                                    class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">
                                    <GitBranch class="w-3 h-3"/>
                                    {{ parentLabel || 'Parent' }}
                                </label>
                                <select
                                    v-model="form.parent_id"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 p-4 text-sm font-bold focus:ring-4 focus:ring-blue-500/10 transition-all"
                                >
                                    <option :value="null">-- Select Root --</option>
                                    <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label
                                    class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1">
                                    <Info class="w-3 h-3"/>
                                    Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    placeholder="Additional details..."
                                ></textarea>
                            </div>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row items-center justify-end gap-6 pt-8 border-t border-gray-100 dark:border-gray-800">
                            <Link
                                :href="`/info-masters?type=${form.type}`"
                                class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="cursor-pointer w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl flex items-center justify-center gap-3 shadow-xl shadow-blue-500/20 active:scale-95 transition-all"
                            >
                                <Save class="w-5 h-5"/>
                                <span class="text-sm font-black uppercase tracking-widest">Save Record</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
