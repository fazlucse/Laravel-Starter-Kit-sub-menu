<template>
    <AppLayout :title="isEdit ? 'Edit User' : 'Create User'">
        <div class="max-w-4xl mx-auto py-2 px-2">

            <div class="mb-3 flex justify-between items-center">
                <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight uppercase">
                    {{ isEdit ? 'Update User' : 'Create User' }}
                </h1>
                <Link href="/users" class="text-xs font-bold text-blue-600 hover:underline uppercase tracking-widest transition-colors">
                    Back to List
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden transition-colors">
                <form @submit.prevent="submit" class="p-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

                        <div class="md:col-span-2 pb-4 border-b dark:border-gray-800">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Employee Link</label>
                            <div class="relative">
                                <select v-model="form.employee_id" @change="handleEmployeeChange"
                                        class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-900 dark:text-white appearance-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                                    <option value="">Independent User (No Link)</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                </select>
                                <ChevronDown class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Full Name</label>
                            <input type="text" v-model="form.name" placeholder="John Doe"
                                   class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all" required />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Email Address</label>
                            <input type="email" v-model="form.email" placeholder="john@company.com"
                                   class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all" required />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Password</label>
                            <div class="relative">
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    :placeholder="isEdit ? '••••••••' : 'Set password'"
                                    class="w-full pl-4 pr-12 py-3.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                                    :required="!isEdit"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none"
                                >
                                    <Eye v-if="!showPassword" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Confirm Password</label>
                            <div class="relative">
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password_confirmation"
                                    placeholder="••••••••"
                                    class="w-full pl-4 pr-12 py-3.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                                    :required="!isEdit"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">User Role</label>
                            <select v-model="form.role" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-bold text-gray-700 dark:text-white outline-none focus:border-blue-500 transition-all">
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Account Status</label>
                            <select v-model="form.status" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-bold text-gray-700 dark:text-white outline-none focus:border-blue-500 transition-all">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t dark:border-gray-800">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 ml-1">Mobile Access</label>
                                <div class="flex p-1.5 bg-gray-100 dark:bg-gray-800 rounded-2xl">
                                    <button type="button" @click="form.mobile_access = 1"
                                            class="flex-1 py-2 text-[10px] font-black rounded-xl transition-all"
                                            :class="form.mobile_access ? 'bg-white dark:bg-gray-700 shadow-sm text-blue-600' : 'text-gray-400'">
                                        ALLOWED
                                    </button>
                                    <button type="button" @click="form.mobile_access = 0"
                                            class="flex-1 py-2 text-[10px] font-black rounded-xl transition-all"
                                            :class="!form.mobile_access ? 'bg-white dark:bg-gray-700 shadow-sm text-red-600' : 'text-gray-400'">
                                        RESTRICTED
                                    </button>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Access Days</label>
                                    <button type="button" @click="toggleAllDays" class="text-[9px] font-black text-blue-600 uppercase tracking-tighter hover:underline">Toggle All</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="day in days" :key="day"
                                           class="px-3 py-1.5 rounded-lg border text-[9px] font-black cursor-pointer transition-all"
                                           :class="form.access_days.includes(day)
                                            ? 'bg-blue-600 border-blue-600 text-white'
                                            : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-400 hover:border-gray-300'">
                                        <input type="checkbox" v-model="form.access_days" :value="day" class="hidden" />
                                        {{ day.toUpperCase() }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 pt-8 border-t dark:border-gray-800 flex justify-end gap-4">
                        <Link href="/users" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                                class="px-10 py-3 bg-blue-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 active:scale-95 transition-all disabled:opacity-50">
                            {{ isEdit ? 'Save Changes' : 'Create Account' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronDown, EyeOff, Eye } from 'lucide-vue-next';

// Reactive state for password visibility
const showPassword = ref(false);

const props = defineProps({
    user: Object,
    employees: Array,
    departments: Array,
    divisions: Array,
    roles: Array
});

const isEdit = computed(() => !!props.user);
const days = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

const form = useForm({
    employee_id: props.user?.employee_id || '',
    name: props.user?.name || '',
    department_id: props.user?.department_id || '',
    division_id: props.user?.division_id || '',
    role: props.user?.role || 'User',
    email: props.user?.email || '',
    status: props.user?.status || 'Active',
    password: '',
    password_confirmation: '',
    mobile_access: props.user?.mobile_access ?? 1,
    access_days: props.user?.access_days || days,
});

const handleEmployeeChange = () => {
    const emp = props.employees.find(e => e.id === form.employee_id);
    if (emp) {
        form.name = emp.name;
        form.email = emp.email || '';
        form.department_id = emp.department_id;
        form.division_id = emp.division_id;
    }
};

const toggleAllDays = () => {
    form.access_days = form.access_days.length === days.length ? [] : [...days];
};

const submit = () => {
    if (isEdit.value) {
        form.put(`/users/update/${props.user.id}`);
    } else {
        form.post('/users/store');
    }
};
</script>
