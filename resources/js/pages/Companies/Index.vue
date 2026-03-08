<template>
    <AppLayout title="Companies" :breadcrumbs="[{ title: 'Dashboard', href: '/' }, { title: 'Companies', href: '/companies' }]">
        <div class="flex flex-col h-[calc(100vh-5rem)]">

            <div class="p-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Companies</h1>
                    <div class="flex items-center gap-3">
                        <HelpModal
                            :doc="{
                            title: HELP_DOCS.companies.title,
                            description: HELP_DOCS.companies.description,
                            tips:HELP_DOCS.companies.tips,
                            groups: HELP_DOCS.companies.full_docs.groups
                          }"
                        />
                        <PerPageSelect v-model="perPage" @update:modelValue="updatePerPage" />
                        <Link href="/companies/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
                            <Plus class="w-4 h-4" />
                            <span class="hidden sm:inline font-bold uppercase text-xs tracking-widest">Add Company</span>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-950">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 border-separate border-spacing-0">
                    <thead class="bg-white dark:bg-gray-900 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-gray-800">S.L</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-gray-800">Actions</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-gray-800">Company Identity</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-gray-800">Industry</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-gray-800">Communication</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-gray-800">Status</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="(c, i) in companies.data" :key="c.id" v-if="companies.data && companies.data.length > 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <td class="px-4 py-3 text-xs font-bold text-gray-400 italic">
                            {{ (companies.current_page - 1) * companies.per_page + i + 1 }}
                        </td>
                        <td class="px-4 py-3 text-right flex justify-end gap-1.5">
                            <Link :href="`/companies/show/${c.id}`" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 rounded-lg transition-all">
                                <Eye class="w-4 h-4" />
                            </Link>
                            <Link :href="`/companies/edit/${c.id}`" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg transition-all">
                                <Edit2 class="w-4 h-4" />
                            </Link>
                            <DeleteDialog :url="`/companies/delete/${c.id}`" record-name="Company" @deleted="handleDelete" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800 border dark:border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img v-if="c.logo_path" :src="'/' + c.logo_path" class="w-full h-full object-contain p-1" />
                                    <Building2 v-else class="w-5 h-5 text-gray-300" />
                                </div>
                                <div>
                                    <div class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ c.name }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ c.company_code || 'No Code' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-tight">
                            {{ c.type || 'N/A' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs font-bold dark:text-gray-200">{{ c.email || 'No Email' }}</div>
                            <div class="text-[10px] font-medium text-gray-400">{{ c.phone || 'No Phone' }}</div>
                        </td>
                        <td class="px-4 py-3">
                                <span :class="c.status === 'Active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                                      class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm">
                                    {{ c.status }}
                                </span>
                        </td>
                    </tr>

                    <tr v-if="!companies.data || companies.data.length === 0">
                        <td colspan="6" class="px-4 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-full mb-4">
                                    <Building2 class="w-12 h-12 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-400 uppercase tracking-widest italic">No Companies Found</h3>
                                <p class="text-sm text-gray-400 mt-1">Start by adding your first company profile.</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <Pagination :links="companies.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/custom/Pagination.vue'
import PerPageSelect from '@/components/custom/PerPageSelect.vue'
import DeleteDialog from '@/components/custom/DeleteDialog.vue'
import { Link } from '@inertiajs/vue3'
import { Plus, Edit2, Eye, Building2 } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'
import HelpModal from '@/components/custom/HelpModal.vue'
import {HELP_DOCS} from '@/constants/helpDocs'
// Props from Inertia
defineProps<{ companies: any }>()

const { perPage, update: updatePerPage } = usePagination()

const handleDelete = () => {
    // Reload data after delete
    window.location.reload()
}
</script>
