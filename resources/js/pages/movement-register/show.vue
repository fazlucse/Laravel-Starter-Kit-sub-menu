<template>
    <AppLayout
        title="Movement Details"
        :breadcrumbs="[
            { title: 'Dashboard', href: '/' },
            { title: 'Movement Register', href: '/movement-registers' },
            { title: 'View Details', href: '#' }
        ]"
    >
        <div class="bg-gray-50 dark:bg-gray-950 py-4 px-4">
            <div class="max-w-7xl mx-auto p-1 sm:p-3">

                <div class="flex justify-between items-center mb-2">
                    <Link
                        href="/movement-registers"
                        class="text-gray-600 hover:text-gray-900 flex items-center gap-2"
                    >
                        <ArrowLeft class="w-4 h-4"/>
                        Back to List
                    </Link>

                    <div class="flex gap-3">
                        <button
                            v-if="movement.status === 2"
                            @click="printOne"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <Printer class="w-4 h-4"/>
                            Print Bill
                        </button>

                        <Link
                            v-if="movement.status === 0"
                            :href="`/movement-registers/${movement.id}/edit`"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <Edit class="w-4 h-4"/>
                            Edit Record
                        </Link>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">

                    <div :class="statusClasses" class="px-6 py-3 flex justify-between items-center border-b">
                    <span class="font-bold uppercase tracking-wider text-sm">
                        Movement #{{ movement.id }}
                    </span>
                        <span class="font-black uppercase text-sm">
                        {{ statusLabel }}
                    </span>
                    </div>

                    <div class="p-6 space-y-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-bold tracking-widest">Purpose of
                                    Visit</label>

                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                    {{
                                        Array.isArray(movement.purpose)
                                            ? movement.purpose.map(p => p.name).join(', ')
                                            : movement.purpose
                                    }}
                                </p>

                                <p class="text-sm text-gray-600 dark:text-gray-400 italic">
                                    {{
                                        Array.isArray(movement.transport_mode)
                                            ? movement.transport_mode.map(t => t.name).join(', ')
                                            : (movement.transport_mode || 'N/A')
                                    }}
                                </p>
                            </div>
                            <div class="md:text-right">
                                <label class="text-xs text-gray-500 uppercase font-bold tracking-widest">Conveyance
                                    Bill</label>
                                <p class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-1">
                                    {{ movement.conveyance_amount || 0 }} <span
                                    class="text-sm font-normal text-gray-500">BDT</span>
                                </p>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800"/>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 relative">
                            <div class="space-y-4">
                                <div class="flex items-center gap-3 text-blue-600">
                                    <MapPin class="w-5 h-5"/>
                                    <h3 class="font-bold uppercase text-sm">Origin (From)</h3>
                                </div>
                                <div class="pl-8 border-l-2 border-dashed border-blue-200 dark:border-blue-900">
                                    <p class="text-base font-bold">{{ movement.origin_location_name }}</p>
                                    <p class="text-sm text-gray-500 mt-2 italic font-mono">
                                        Started: {{ formatDateTime(movement.movement_started_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center gap-3 text-emerald-600">
                                    <Navigation class="w-5 h-5"/>
                                    <h3 class="font-bold uppercase text-sm">Destination (To)</h3>
                                </div>
                                <div class="pl-8 border-l-2 border-dashed border-emerald-200 dark:border-emerald-900">
                                    <p class="text-base font-bold">
                                        {{ movement.destination_location_name || 'Not Reached' }}</p>
                                    <p v-if="movement.movement_ended_at"
                                       class="text-sm text-gray-500 mt-2 italic font-mono">
                                        Reached: {{ formatDateTime(movement.movement_ended_at) }}
                                    </p>
                                    <p v-else class="text-amber-500 text-sm mt-2 font-bold animate-pulse">
                                        In Progress...
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="movement.status !== 0"
                             class="mt-8 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] text-gray-500 uppercase font-bold">Action Taken By</label>
                                    <p class="text-sm font-medium">{{ movement.approved_by_name || 'System' }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-500 uppercase font-bold">Action Date</label>
                                    <p class="text-sm font-medium">{{ formatDateTime(movement.approved_at) }}</p>
                                </div>
                                <div v-if="movement.status === 22" class="sm:col-span-2">
                                    <label class="text-[10px] text-red-500 uppercase font-bold">Rejection Reason</label>
                                    <p class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 p-2 rounded mt-1">
                                        {{ movement.rejection_reason }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import {Link} from '@inertiajs/vue3'
import {ArrowLeft, MapPin, Navigation, Printer, Edit, Lock} from 'lucide-vue-next'
import {computed} from 'vue'

const props = defineProps<{
    movement: any
}>()

const statusLabel = computed(() => {
    if (!props.movement.movement_ended_at) return 'In Progress'
    if (props.movement.status === 0) return 'Pending Approval'
    if (props.movement.status === 2) return 'Approved'
    return 'Rejected'
})

const statusClasses = computed(() => {
    if (!props.movement.movement_ended_at) return 'bg-amber-50 border-amber-200 text-amber-700'
    if (props.movement.status === 0) return 'bg-yellow-50 border-yellow-200 text-yellow-700'
    if (props.movement.status === 2) return 'bg-emerald-50 border-emerald-200 text-emerald-700'
    return 'bg-red-50 border-red-200 text-red-700'
})

const formatDateTime = (dateStr: string) => {
    if (!dateStr) return 'N/A'
    return new Date(dateStr).toLocaleString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: true
    })
}

const printOne = () => {
    window.open(`/movement-registers-print-report?ids=${props.movement.id}`, '_blank')
}
</script>
