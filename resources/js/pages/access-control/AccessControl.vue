<template>
    <AppLayout title="Access Matrix">
        <div class="bg-gray-50 dark:bg-gray-950 min-h-screen py-8 px-4">
            <div class="max-w-7xl mx-auto">

                <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-600 rounded-2xl shadow-lg shadow-blue-500/20">
                            <ShieldCheck class="w-8 h-8 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter uppercase">Access Matrix</h1>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest opacity-70">Security & Permissions Control</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="savePermissions" :disabled="form.processing"
                                class="px-8 py-3 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-2 disabled:opacity-50">
                            <Save class="w-4 h-4" />
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>

                        <div class="h-10 w-[1px] bg-gray-200 dark:bg-gray-800 hidden md:block mx-2"></div>

                        <div class="relative w-full md:w-64">
                            <select v-model="selectedRole" class="w-full pl-4 pr-10 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                                <option v-for="role in props.roles" :key="role.id" :value="role">{{ role.name }}</option>
                            </select>
                            <ChevronDown class="w-3 h-3 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"/>
                        </div>
                    </div>
                </div>

                <div class="mb-4 flex justify-between items-center px-2">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                        Configure <span class="text-blue-600">{{ selectedRole.name }}</span> Permissions
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="setAllGlobal(true)" class="text-[9px] font-black uppercase tracking-tighter text-emerald-600 hover:underline">Grant Everything</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" @click="setAllGlobal(false)" class="text-[9px] font-black uppercase tracking-tighter text-red-600 hover:underline">Revoke Everything</button>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden transition-all">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-gray-800/50 border-b dark:border-gray-800">
                        <tr>
                            <th class="p-6 text-[10px] font-black uppercase text-gray-400 tracking-widest">Module</th>
                            <th v-for="action in actions" :key="action" class="p-6 text-center text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                {{ action }}
                            </th>
                            <th class="p-6 text-center text-[10px] font-black uppercase text-gray-400 tracking-widest">All</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                        <tr v-for="mod in modules" :key="mod.slug" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-gray-900 dark:text-white text-sm">{{ mod.name }}</div>
                                <div class="text-[9px] text-gray-400 font-mono italic opacity-60">{{ mod.slug }}.*</div>
                            </td>

                            <td v-for="action in actions" :key="action" class="p-6 text-center">
                                <button type="button" @click="togglePermission(mod.slug, action)"
                                        class="w-10 h-10 rounded-xl inline-flex items-center justify-center transition-all border-2"
                                        :class="hasPermission(mod.slug, action) ? 'bg-blue-600 border-blue-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600'">
                                    <Check v-if="hasPermission(mod.slug, action)" class="w-5 h-5 stroke-[3]"/>
                                    <Plus v-else class="w-4 h-4 opacity-10"/>
                                </button>
                            </td>

                            <td class="p-6 text-center">
                                <button type="button" @click="toggleRow(mod.slug)"
                                        class="w-8 h-8 rounded-lg border flex items-center justify-center mx-auto transition-all"
                                        :class="isRowFull(mod.slug) ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-gray-50 border-gray-100 text-gray-300'">
                                    <Layers class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="p-6 bg-white dark:bg-gray-900 border-t dark:border-gray-800 flex justify-end gap-4">
                        <Link href="/dashboard" class="px-6 py-3 text-xs font-bold text-gray-400 hover:text-gray-600">Discard</Link>
                        <button @click="savePermissions" :disabled="form.processing"
                                class="px-12 py-3 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all flex items-center gap-2">
                            <Save class="w-4 h-4" />
                            Update {{ selectedRole.name }} Permissions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Check, Plus, ChevronDown, Save, ShieldCheck, Layers } from 'lucide-vue-next';

const props = defineProps({
    roles: Array,
    modules: Array,
    actions: Array
});

const selectedRole = ref(props.roles[0]);

const form = useForm({
    permissions: selectedRole.value.permissions.map(p => p.name)
});

watch(selectedRole, (newRole) => {
    form.permissions = newRole.permissions.map(p => p.name);
});

const hasPermission = (slug, action) => {
    return form.permissions.includes(`${slug}.${action}`);
};

const togglePermission = (slug, action) => {
    const perm = `${slug}.${action}`;
    const index = form.permissions.indexOf(perm);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(perm);
    }
};

const isRowFull = (slug) => {
    return props.actions.every(action => hasPermission(slug, action));
};

const toggleRow = (slug) => {
    const shouldAdd = !isRowFull(slug);
    props.actions.forEach(action => {
        const perm = `${slug}.${action}`;
        const index = form.permissions.indexOf(perm);
        if (shouldAdd && index === -1) form.permissions.push(perm);
        else if (!shouldAdd && index > -1) form.permissions.splice(index, 1);
    });
};

const setAllGlobal = (grant) => {
    if (!grant) { form.permissions = []; return; }
    const all = [];
    props.modules.forEach(mod => {
        props.actions.forEach(action => all.push(`${mod.slug}.${action}`));
    });
    form.permissions = all;
};

const savePermissions = () => {
    form.put(`/access-control/${selectedRole.value.id}`, {
        preserveScroll: true
    });
};
</script>
