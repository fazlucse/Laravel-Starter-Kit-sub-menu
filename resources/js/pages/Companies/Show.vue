<template>
    <AppLayout
        title="Corporate Profile"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Companies', href: '/companies' },
            { title: company.name, href: '#' }
        ]"
    >
        <div class="py-4 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

            <div class="flex flex-col md:flex-row justify-between items-end mb-4 gap-2 print:hidden">
                <div class="flex items-center gap-4 leading-tight">
                    <Link
                        href="/companies"
                        class="p-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 text-gray-400 hover:text-indigo-600 transition-colors shadow-sm"
                        title="Back to List"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.5em]">Registry Management</h2>
                        <div class="flex items-center gap-2 text-[10px] text-gray-400 italic font-medium">
                            <Clock class="w-3 h-3" />
                            Updated: {{ new Date(company.updated_at).toLocaleDateString() }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-900 p-1 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <button @click="generateCSV" class="flex items-center gap-1 px-3 py-1.5 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white">
                        <Download class="w-3 h-3" /> CSV
                    </button>
                    <button @click="printReport" class="flex items-center gap-1 px-3 py-1.5 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all bg-gray-50 text-gray-700 hover:bg-gray-900 hover:text-white">
                        <Printer class="w-3 h-3" /> Print
                    </button>
                    <div class="w-px h-4 bg-gray-200 dark:bg-gray-800 mx-1"></div>
                    <Link :href="`/companies/edit/${company.id}`" class="flex items-center gap-1 px-4 py-1.5 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all bg-indigo-600 text-white hover:bg-indigo-700">
                        <Edit2 class="w-3 h-3" /> Edit Profile
                    </Link>
                </div>
            </div>

            <div id="report-card" class="bg-white dark:bg-gray-950 shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 dark:border-gray-800">

                <div class="relative p-6 md:p-10 flex flex-col md:flex-row items-center gap-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-950 border-b dark:border-gray-800">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <Building2 class="w-32 h-32 rotate-12" />
                    </div>

                    <div class="relative w-24 h-24 bg-white dark:bg-gray-950 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800 flex items-center justify-center overflow-hidden p-2">
                        <img v-if="company.logo_path" :src="'/' + company.logo_path" class="max-w-full max-h-full object-contain" />
                        <Building2 v-else class="w-10 h-10 text-gray-200" />
                    </div>

                    <div class="relative flex-1 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-[8px] font-black uppercase tracking-[0.2em] mb-1">
                            Official Registry Verified
                        </div>
                        <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white uppercase tracking-tighter leading-none mb-2">
                            {{ company.name }}
                        </h1>
                        <div class="flex flex-wrap justify-center md:justify-start items-center gap-4">
                            <span :class="company.status === 'Active' ? 'text-green-600' : 'text-red-600'" class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest">
                                <span class="w-1.5 h-1.5 rounded-full animate-pulse bg-current"></span>
                                Status: {{ company.status }}
                            </span>
                            <span class="w-px h-3 bg-gray-300 dark:bg-gray-700"></span>
                            <p class="text-gray-400 font-bold uppercase text-[10px] tracking-[0.2em]">
                                {{ company.industry_type || 'Industrial' }} Sector
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">

                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b-2 border-indigo-500 w-fit pb-1">
                            <h4 class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-[0.3em]">Legal Identity</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-4">
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Entity Code</p>
                                <p class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter text-sm">{{ company.company_code || '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Ownership</p>
                                <p class="font-bold text-gray-700 dark:text-gray-300 uppercase text-[11px]">{{ company.ownership_type || 'Private' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Registration No</p>
                                <p class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter font-mono text-[11px]">{{ company.registration_no || '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Tax ID (TIN)</p>
                                <p class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter font-mono text-[11px]">{{ company.tax_identification_no || '---' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b-2 border-indigo-500 w-fit pb-1">
                            <h4 class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-[0.3em]">Communication</h4>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg text-indigo-500 p-2 shadow-inner"><Mail class="w-3.5 h-3.5" /></div>
                                <div>
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Official Email</p>
                                    <p class="font-black text-indigo-600 lowercase tracking-tighter text-xs">{{ company.email || '---' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg text-indigo-500 p-2 shadow-inner"><Phone class="w-3.5 h-3.5" /></div>
                                <div>
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Direct Line</p>
                                    <p class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter text-xs">{{ company.phone || '---' }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="md:col-span-2 space-y-4">
                        <div class="flex items-center gap-2 border-b-2 border-indigo-500 w-fit pb-1">
                            <h4 class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-[0.3em]">Headquarters</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div class="md:col-span-2">
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Primary Street Address</p>
                                <p class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter text-xl leading-tight">{{ company.address_line1 || company.address || 'N/A' }}</p>
                                <p v-if="company.address_line2" class="text-gray-400 mt-1 italic text-[11px] font-medium">{{ company.address_line2 }}</p>
                            </div>
                            <div class="flex flex-col justify-center border-l dark:border-gray-700 pl-6 md:pl-8">
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Location Details</p>
                                <p class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter text-sm">{{ company.city }}, {{ company.state }}</p>
                                <p class="text-[9px] font-black text-indigo-500 uppercase mt-1 tracking-wider">{{ company.country }} • {{ company.postal_code }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="bg-gray-900 p-6 text-center">
                    <p class="text-[8px] font-black text-gray-500 uppercase tracking-[0.5em] mb-2">Authorized Corporate Profile</p>
                    <div class="flex justify-between items-center px-4 border-t border-gray-800 pt-4">
                        <p class="text-[7px] text-gray-600 uppercase font-bold tracking-widest italic">Registry: #{{ company.id }}</p>
                        <p class="text-[7px] text-gray-600 uppercase font-bold tracking-widest italic">{{ new Date().toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { Building2, Printer, Download, Mail, Phone, Edit2, Clock, ArrowLeft } from 'lucide-vue-next'

const props = defineProps<{ company: any }>()

const printReport = () => {
    const printContent = document.getElementById("report-card")?.innerHTML;
    const originalContent = document.body.innerHTML;
    if (printContent) {
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    }
}

const generateCSV = () => {
    const rows = [
        ["Attribute", "Value"],
        ["Name", props.company.name],
        ["Industry", props.company.industry_type],
        ["Status", props.company.status],
        ["Reg No", props.company.registration_no],
        ["Email", props.company.email],
        ["Address", props.company.address_line1 || props.company.address],
    ];
    let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");
    const link = document.createElement("a");
    link.setAttribute("href", encodeURI(csvContent));
    link.setAttribute("download", `profile_${props.company.id}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<style scoped>
@media print {
    body { background: white !important; }
    #report-card {
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }
    p, h1, h4 { color: black !important; }
}
</style>
