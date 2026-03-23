<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// 1. Props Definition
const props = defineProps<{
    auth: { user: { id: number, name: string, role: string } },
    stats: Array<{ label: string, value: string | number, color: string }>,
    // stats_details: { 'Total Present': [{name, id, photo, dept, desig, info, status_color}, ...] }
    stats_details?: Record<string, Array<any>>,
    table_data: Array<any>,
    upcoming_holidays?: Array<{ date: string, title: string }>
}>();

// 2. Real-Time Clock & Date Logic
const now = ref(new Date());
let timer: any;
onMounted(() => { timer = setInterval(() => { now.value = new Date(); }, 1000); });
onUnmounted(() => { clearInterval(timer); });

const timeStr = computed(() => now.value.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }));
const dateStr = computed(() => now.value.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));

// 3. Role Helpers
const userRole = computed(() => props.auth.user.role);
const isAdmin = computed(() => ['Developer', 'Super Admin', 'Admin'].includes(userRole.value));
const isHR = computed(() => userRole.value === 'HR Manager');
const isEmployee = computed(() => userRole.value === 'Employee');
const holidays = computed(() => props.upcoming_holidays ?? []);

// 4. Full Window Modal Logic
const isModalOpen = ref(false);
const modalTitle = ref('');
const modalData = ref<Array<any>>([]);
const searchQuery = ref('');

const openModal = (label: string) => {
    if (!isAdmin.value || !props.stats_details?.[label]) return;
    modalTitle.value = label;
    modalData.value = props.stats_details[label];
    isModalOpen.value = true;
    searchQuery.value = ''; // Reset search
};

const closeModal = () => { isModalOpen.value = false; };

// Search filter for the modal list
const filteredPeople = computed(() => {
    if (!searchQuery.value) return modalData.value;
    return modalData.value.filter(person =>
        person.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        person.id.toString().includes(searchQuery.value)
    );
});

const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }];
</script>

<template>
    <Head title="HRMS Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 max-w-[1600px] mx-auto w-full relative">

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b pb-8 border-gray-100 dark:border-gray-800">
                <div class="space-y-1">
                    <h1 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white">
                        Welcome, {{ props.auth.user.name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest rounded border border-indigo-100 dark:border-indigo-800">
                            {{ userRole }}
                        </span>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <p class="text-sm font-bold text-gray-500 dark:text-gray-400">
                            {{ dateStr }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 bg-gray-900 dark:bg-black px-6 py-4 rounded-[2rem] border border-gray-800 shadow-2xl">
                    <div class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </div>
                    <span class="text-3xl font-black font-mono text-white tabular-nums tracking-tighter">
                        {{ timeStr }}
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div v-if="isAdmin" class="flex items-center gap-2 px-1">
                    <div class="h-1 w-4 rounded-full bg-indigo-500"></div>
                    <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400">Daily Attendance Overview</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="stat in props.stats" :key="stat.label"
                         @click="openModal(stat.label)"
                         :class="isAdmin ? 'cursor-pointer hover:border-indigo-500 hover:shadow-2xl hover:-translate-y-1 active:scale-95' : ''"
                         class="bg-white dark:bg-gray-900 p-8 rounded-[1rem] border-2 border-transparent shadow-sm transition-all group relative overflow-hidden">

                        <div class="flex justify-between items-start mb-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ stat.label }}</p>
                            <span v-if="isAdmin" class="text-[9px] font-black text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">VIEW PERSONNEL &rarr;</span>
                        </div>
                        <div class="text-6xl font-black tabular-nums tracking-tighter" :class="stat.color">
                            {{ stat.value }}
                        </div>

                        <div v-if="stat.label.includes('Late') || stat.label.includes('Absent')"
                             class="absolute top-0 right-0 w-1.5 h-full"
                             :class="stat.label.includes('Late') ? 'bg-orange-500' : 'bg-rose-500'">
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="isModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/95 backdrop-blur-xl p-0 md:p-10">
                <div class="bg-white dark:bg-gray-950 w-full h-full rounded-[1rem] shadow-2xl flex flex-col overflow-hidden border dark:border-gray-800">

                    <div class="p-8 border-b dark:border-gray-800 flex flex-col md:flex-row justify-between items-center gap-6 bg-gray-50/50 dark:bg-gray-900/50">
                        <div>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">{{ modalTitle }}</h3>
                            <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">Live Employee Registry</p>
                        </div>

                        <div class="relative w-full md:w-96">
                            <input v-model="searchQuery" type="text" placeholder="Search by name or ID..."
                                   class="w-full bg-white dark:bg-gray-900 border-dark dark:border-gray-700 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <button @click="closeModal" class="bg-rose-500 hover:bg-rose-600 text-white px-2 py-3 rounded cursor-pointer font-black text-xs uppercase tracking-widest transition-all">
                            Close
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 content-start">
                        <div v-for="(person, i) in filteredPeople" :key="i"
                             class="flex items-center gap-4 p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-800 hover:border-indigo-400 hover:shadow-md transition-all group">

                            <div class="relative shrink-0">
                                <img v-if="person.photo" :src="person.photo" class="w-16 h-16 rounded-xl object-cover shadow-sm" />
                                <div v-else class="w-16 h-16 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-xl uppercase">
                                    {{ person.name.charAt(0) }}
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 border-2 border-white dark:border-gray-800 rounded-full" :class="person.status_color"></div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col">
                                    <h4 class="text-base font-black text-gray-900 dark:text-white leading-tight break-words">
                                        {{ person.name }}
                                    </h4>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">ID: {{ person.id }}</span>
                                </div>

                                <div class="flex flex-wrap gap-x-3 mt-2">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-500">
                                        <div class="w-1 h-1 rounded-full bg-indigo-500"></div> {{ person.desig }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-500">
                                        <div class="w-1 h-1 rounded-full bg-gray-300"></div> {{ person.dept }}
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0">
                                <div class="px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-center min-w-[90px]">
                                    <p class="text-[11px] font-black uppercase text-indigo-600 dark:text-indigo-400 tabular-nums">
                                        {{ person.info }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-if="filteredPeople.length === 0" class="col-span-full text-center py-32">
                            <p class="text-xl font-black text-gray-300 uppercase tracking-[0.5em]">No Personnel Found</p>
                        </div>
                    </div>

                    <div class="p-8 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-800 flex justify-between items-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Showing {{ filteredPeople.length }} Records</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Confidential HR Data</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
                <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-[1rem] border border-gray-100 dark:border-gray-800 overflow-hidden shadow-sm">
                    <div class="p-8 border-b flex justify-between items-center bg-gray-50/30 dark:bg-gray-800/30">
                        <h2 class="font-black text-gray-800 dark:text-white uppercase tracking-tighter">System Activity Logs</h2>
                        <Link href="#" class="text-[10px] font-black text-indigo-600 hover:underline">VIEW ALL LOGS &rarr;</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-400 text-[10px] font-black uppercase tracking-widest border-b">
                            <tr><th class="p-8">Staff Member</th><th class="p-8">Action</th><th class="p-8 text-right">Details</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-bold">
                            <tr v-for="item in props.table_data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-8 text-gray-900 dark:text-gray-200">{{ item.employee?.name || item.user?.name || 'Staff' }}</td>
                                <td class="p-8 text-gray-500 uppercase text-[10px] tracking-widest">{{ item.action || 'System Log' }}</td>
                                <td class="p-8 text-right font-black text-indigo-600">VIEW</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-8 rounded-[1rem] border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-black uppercase text-[10px] tracking-[0.3em] text-gray-400 mb-8 border-b pb-4">Upcoming Holidays</h3>
                    <div v-if="holidays.length" class="space-y-8">
                        <div v-for="h in holidays" :key="h.date" class="flex items-center gap-6 group">
                            <div class="bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 p-4 rounded-3xl text-center min-w-[80px] border border-rose-100 dark:border-rose-900 shadow-sm transition-transform group-hover:scale-110">
                                <div class="text-[10px] font-black uppercase mb-1">{{ new Date(h.date).toLocaleString('default', { month: 'short' }) }}</div>
                                <div class="text-3xl font-black leading-none">{{ h.date.split('-')[2] }}</div>
                            </div>
                            <div class="font-black text-gray-800 dark:text-gray-200 tracking-tight leading-tight">
                                {{ h.title }}
                                <p class="text-[10px] text-gray-400 uppercase mt-1 font-bold">Public Holiday</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 text-gray-400 italic text-xs">No holidays found</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
