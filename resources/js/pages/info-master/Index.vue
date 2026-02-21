<template>
    <AppLayout :title="currentType?.toUpperCase() + ' Management'">
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <div class="p-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600">
                            <component :is="getIcon(currentType)" class="w-6 h-6" />
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                                Master Data Management
                            </h1>
<!--                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Master Data Management</p>-->
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />

                        <Link
                            :href="`/info-masters/create?type=${currentType}`"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all shadow-lg shadow-blue-500/20"
                        >
                            <Plus class="w-4 h-4" />
                            <span class="text-sm font-black uppercase tracking-tighter">Add New</span>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">S.L</th>
                        <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>

                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Details</th>
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Code</th>
                        <th v-if="parents?.length" class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Parent / Group</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:border-gray-700">
                    <tr v-for="(item, i) in entries.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <td class="px-6 py-4 text-xs font-mono text-gray-400">
                            {{ (entries.current_page - 1) * entries.per_page + i + 1 }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 transition-opacity">
                                <Link
                                    :href="`/info-masters/edit/${item.id}`"
                                    class="cursor-pointer inline-flex items-center p-1.5 rounded transition-all duration-200
                   bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100
                   dark:text-blue-400 dark:hover:bg-blue-900/50 border border-blue-100 dark:border-blue-800"
                                >
                                    <Edit class="w-4 h-4" />
                                </Link>

                                <DeleteDialog
                                    :url="`/info-masters/delete/${item.id}`"
                                    :record-name="item.name"
                                    @deleted="handleDelete"
                                />
                            </div>
                        </td>
                        <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase rounded-md border border-blue-100 dark:border-blue-800">
                                    {{ item.type }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                                {{ item.name }}
                            </div>
                            <div v-if="item.description" class="text-[10px] text-gray-500 italic truncate max-w-[200px] mt-0.5">
                                {{ item.description }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono text-gray-600 dark:text-gray-400 uppercase border border-gray-200 dark:border-gray-700">
                                    {{ item.code || '---' }}
                                </span>
                        </td>

                        <td v-if="parents?.length" class="px-6 py-4">
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-600 dark:text-gray-400">
                                <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                                {{ item.parent?.name || 'Unassigned' }}
                            </div>
                        </td>


                    </tr>

                    <tr v-if="entries.data.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <component :is="getIcon(currentType)" class="w-12 h-12 text-gray-200 mb-4" />
                                <p class="font-bold uppercase text-xs tracking-widest">No Records Found</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <Pagination :links="entries.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/custom/Pagination.vue';
import PerPageSelect from '@/components/custom/PerPageSelect.vue';
import DeleteDialog from '@/components/custom/DeleteDialog.vue';
import { Link } from '@inertiajs/vue3';
import { Plus, Edit, Globe, MapPin, Truck, Tag, Hash } from 'lucide-vue-next';
import { usePagination } from '@/composables/usePagination';

const props = defineProps({
    entries: Object,
    currentType: String,
    parents: Array,
});

const { perPage, update: updatePerPage } = usePagination();

const getIcon = (type: string) => {
    const icons = {
        country: Globe,
        city: MapPin,
        transport: Truck,
        purpose: Tag
    };
    return icons[type] || Hash;
};

const handleDelete = ({ success }: { success: boolean }) => {
    // Optional: Add toast or notification logic here
};
</script>
