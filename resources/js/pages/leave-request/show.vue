<template>
    <AppLayout
        title="Leave Request Details"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Leave Requests', href: '/leave-request' },
            { title: 'Details', href: '#' },
        ]"
    >
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto space-y-6">

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Leave Request Details</h1>
                        <p class="text-sm text-gray-500 flex items-center gap-2 mt-1">
                            <Clock class="w-4 h-4" />
                            Submitted on {{ formatDateTime(leaveRequest.created_at) }}
                        </p>
                    </div>
                    <div :class="statusBadgeClass(leaveRequest.status)">
                        {{ getStatusLabel(leaveRequest.status) }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Requester Info</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] text-gray-400 uppercase font-bold">Employee Name</label>
                                    <p class="text-sm font-semibold dark:text-gray-200">{{ leaveRequest.person_name }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-400 uppercase font-bold">Department</label>
                                    <p class="text-sm font-semibold dark:text-gray-200">{{ leaveRequest.department_name }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-400 uppercase font-bold">Emergency Contact</label>
                                    <p class="text-sm font-semibold dark:text-gray-200">{{ leaveRequest.emergency_contact_name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-400 uppercase font-bold">Reliever</label>
                                    <p class="text-sm font-semibold dark:text-gray-200">{{ leaveRequest.reliver_employee || 'None assigned' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-2">
                                <CalendarDays class="w-4 h-4 text-blue-500" />
                                <h3 class="font-bold text-gray-700 dark:text-gray-300 text-sm uppercase">Requested Dates</h3>
                            </div>

                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div v-for="(item, index) in leaveRequest.details" :key="index" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span :class="reasonBadgeClass(item.leave_reason)">
                                                    {{ formatReason(item.leave_reason) }}
                                                </span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-bold uppercase">
                                                    {{ item.leave_duration }}
                                                </span>
                                            </div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ item.from_date }} <span class="mx-1 text-gray-400">→</span> {{ item.to_date }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xl font-black text-gray-900 dark:text-white">{{ item.total_days }}</p>
                                            <p class="text-[10px] text-gray-500 font-bold uppercase">Days</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-blue-50/50 dark:bg-blue-900/20 flex justify-between items-center border-t border-blue-100 dark:border-blue-900/30">
                                <span class="font-bold text-blue-800 dark:text-blue-300 text-xs uppercase">Total Leave Duration</span>
                                <span class="text-xl font-black text-blue-800 dark:text-blue-300">{{ leaveRequest.total_leave }} Days</span>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Submission Remarks</h3>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-l-4 border-blue-500">
                                <p class="text-sm text-gray-700 dark:text-gray-300 italic leading-relaxed">
                                    "{{ leaveRequest.remarks || 'No remarks provided for this request.' }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class=" flex justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <button
                        @click="$inertia.visit('/leave-request')"
                        class=" cursor-pointer text-sm font-bold text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-2"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Back to List
                    </button>

                    <div class="flex gap-3" v-if="leaveRequest.status === 0 && can_approve">
                        <button
                            @click="updateStatus(22)"
                            class="cursor-pointer px-5 py-2 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/50 rounded-lg text-xs font-black uppercase hover:bg-red-600 hover:text-white transition-all"
                        >
                            Reject
                        </button>
                        <button
                            @click="updateStatus(2)"
                            class="cursor-pointer px-5 py-2 bg-emerald-600 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-700 shadow-lg shadow-emerald-200 dark:shadow-none transition-all"
                        >
                            Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { Clock, CalendarDays, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    leaveRequest: { type: Object, required: true },
    can_approve: { type: Boolean, default: false }
})

/**
 * Formats the ISO Date to "Feb 13, 2026, 04:46 PM"
 */
const formatDateTime = (dateString: string) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).format(date)
}

/**
 * Logic for Leave Reason Labels
 */
const formatReason = (reason: string) => {
    const reasons: Record<string, string> = {
        'sick': 'Sick Leave',
        'casual': 'Casual Leave',
        'annual': 'Annual Leave',
        'emergency': 'Emergency Leave',
        'maternity': 'Maternity Leave',
        'paternity': 'Paternity Leave'
    }
    return reasons[reason.toLowerCase()] || reason
}

/**
 * Dynamic Colors per Leave Reason
 */
const reasonBadgeClass = (reason: string) => {
    const base = "text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-tighter border "
    const type = reason.toLowerCase()
    if (type === 'sick') return base + "bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400"
    if (type === 'casual') return base + "bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400"
    if (type === 'annual') return base + "bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400"
    return base + "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
}

const getStatusLabel = (status: number) => {
    const statuses: Record<number, string> = { 0: 'Pending Review', 2: 'Approved', 22: 'Rejected' }
    return statuses[status] || 'Unknown'
}

const statusBadgeClass = (status: number) => {
    return {
        'px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm': true,
        'bg-yellow-50 text-yellow-700 border-yellow-200': status === 0,
        'bg-emerald-50 text-emerald-700 border-emerald-200': status === 2,
        'bg-red-50 text-red-700 border-red-200': status === 22,
    }
}

const updateStatus = (newStatus: number) => {
    const action = newStatus === 2 ? 'APPROVE' : 'REJECT'
    if (confirm(`ACTION REQUIRED: Are you sure you want to ${action} this leave request?`)) {
        router.post(`/leave-request/${props.leaveRequest.id}/status`, {
            status: newStatus
        })
    }
}
</script>
