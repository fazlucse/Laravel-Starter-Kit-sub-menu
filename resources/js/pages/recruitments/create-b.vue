<template>
    <AppLayout
        title="Recruitment & ATS"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Recruitment & ATS', href: '/recruitment' },
    ]"
    >
        <div class="flex flex-col h-[calc(100vh-5rem)] bg-gray-50 dark:bg-gray-950">

            <!-- Header -->
            <div class="p-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Recruitment & ATS</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manage your entire hiring pipeline — from job posting to onboarding
                        </p>
                    </div>
                    <button
                        @click="showJobModal = true"
                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition"
                    >
                        <Plus class="w-4 h-4" />
                        Create Job Opening
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex overflow-x-auto scrollbar-hide">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-2 px-6 py-4 font-medium text-sm whitespace-nowrap border-b-2 transition-colors"
                        :class="activeTab === tab.id
              ? 'border-blue-600 text-blue-600 dark:text-blue-400'
              : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'"
                    >
                        <component :is="tab.icon" class="w-5 h-5" />
                        {{ tab.label }}
                        <span
                            v-if="tab.count !== undefined"
                            class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full"
                            :class="activeTab === tab.id
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                        >
              {{ tab.count }}
            </span>
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="max-w-7xl mx-auto">

                    <!-- JOBS TAB -->
                    <div v-if="activeTab === 'jobs'">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <Briefcase class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                                Active Job Openings
                            </h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Browse and manage all open positions across the company. Click on any role to view applicants.
                            </p>
                        </div>

                        <div class="grid gap-6">
                            <div
                                v-for="job in jobsList"
                                :key="job.id"
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 hover:shadow-xl transition-all duration-300"
                            >
                                <div class="flex flex-col lg:flex-row justify-between gap-6">
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-4">
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ job.title }}</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ job.department }} • {{ job.location }}
                                                </p>
                                            </div>
                                            <span
                                                :class="job.status === 'Active'
                          ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                          : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                                class="px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider"
                                            >
                        {{ job.status }}
                      </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-4 mb-5 text-sm">
                                            <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                                <span class="font-semibold">{{ job.salary }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                <Briefcase class="w-4 h-4" />
                                                {{ job.type }}
                                            </div>
                                        </div>

                                        <p class="text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed mb-5">
                                            {{ job.description }}
                                        </p>

                                        <div class="flex flex-wrap gap-2">
                      <span
                          v-for="skill in job.skills"
                          :key="skill"
                          class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-medium"
                      >
                        {{ skill }}
                      </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-5">
                                        <div class="text-right">
                                            <div class="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-white">
                                                <Users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                                {{ job.applicants }}
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Applicants</p>
                                        </div>

                                        <div class="flex flex-col gap-3 w-full lg:w-auto">
                                            <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-md">
                                                View Applications
                                            </button>
                                            <div class="flex gap-2">
                                                <button class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition">
                                                    Edit Job
                                                </button>
                                                <button class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-medium transition">
                                                    Close Position
                                                </button>
                                            </div>
                                        </div>

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Posted {{ formatDate(job.posted) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- APPLICANTS TAB -->
                    <div v-if="activeTab === 'applicants'">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <Users class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                                Candidate Pipeline
                            </h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Track every applicant through your hiring stages. Use Board View for visual tracking or List View for detailed analysis.
                            </p>
                        </div>

                        <div class="flex gap-3 mb-6">
                            <button
                                @click="applicantView = 'board'"
                                :class="applicantView === 'board' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="px-4 py-2 rounded-lg font-medium text-sm transition"
                            >Board View</button>
                            <button
                                @click="applicantView = 'list'"
                                :class="applicantView === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="px-4 py-2 rounded-lg font-medium text-sm transition"
                            >List View</button>
                        </div>

                        <!-- Board View -->
                        <div v-if="applicantView === 'board'" class="flex gap-5 overflow-x-auto pb-4">
                            <div v-for="stage in stages" :key="stage.id" class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ stage.name }}</h3>
                                    <span :class="stage.color" class="px-3 py-1 rounded-full text-xs font-medium">{{ stage.count }}</span>
                                </div>
                                <div class="space-y-3">
                                    <div
                                        v-for="applicant in applicants.filter(a => a.stage === stage.id)"
                                        :key="applicant.id"
                                        class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition cursor-pointer"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center flex-shrink-0">
                                                <User class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-semibold text-gray-900 dark:text-white truncate">{{ applicant.name }}</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ applicant.position }}</p>
                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                    <Mail class="w-3.5 h-3.5" />
                                                    <span class="truncate">{{ applicant.email }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div v-if="applicantView === 'list'">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 dark:text-gray-300">Candidate</th>
                                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 dark:text-gray-300">Position</th>
                                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 dark:text-gray-300">Stage</th>
                                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 dark:text-gray-300">Applied</th>
                                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="applicant in applicants" :key="applicant.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                    <User class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ applicant.name }}</div>
                                                    <div class="text-sm text-
```gray-500 dark:text-gray-400">{{ applicant.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">{{ applicant.position }}</td>
                                        <td class="py-4 px-6">
                        <span :class="stages.find(s => s.id === applicant.stage)?.color" class="px-3 py-1 rounded-full text-xs font-medium">
                          {{ stages.find(s => s.id === applicant.stage)?.name }}
                        </span>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">{{ applicant.applied }}</td>
                                        <td class="py-4 px-6">
                                            <div class="flex gap-2">
                                                <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition"><Eye class="w-4 h-4" /></button>
                                                <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition"><Calendar class="w-4 h-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- INTERVIEWS TAB -->
                    <div v-if="activeTab === 'interviews'">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <Calendar class="w-8 h-8 text-orange-600 dark:text-orange-400" />
                                Upcoming Interviews
                            </h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Stay on top of all scheduled candidate interviews. Join meetings directly from here.
                            </p>
                        </div>
                        <!-- Interviews content (same as before) -->
                        <div class="grid gap-5">
                            <div v-for="interview in interviews" :key="interview.id" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 hover:shadow-lg transition">
                                <div class="flex justify-between items-start gap-6">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ interview.candidate }}</h3>
                                            <span class="px-3 py-1 bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 rounded-full text-xs font-medium">
                        {{ interview.type }}
                      </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ interview.position }}</p>
                                        <div class="flex flex-wrap gap-6 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="flex items-center gap-2"><Calendar class="w-4 h-4" />{{ interview.date }}</span>
                                            <span class="flex items-center gap-2"><Clock class="w-4 h-4" />{{ interview.time }}</span>
                                            <span class="flex items-center gap-2"><User class="w-4 h-4" />{{ interview.interviewer }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">Join Meeting</button>
                                        <button class="px-4 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition">Reschedule</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OFFERS & ONBOARDING TAB -->
                    <div v-if="activeTab === 'offers'">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <FileText class="w-8 h-8 text-green-600 dark:text-green-400" />
                                Offers & Onboarding
                            </h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Manage candidates who have received offers and track new hires through their onboarding journey.
                            </p>
                        </div>
                        <!-- Offers & Onboarding content (same as before) -->
                        <div class="grid lg:grid-cols-2 gap-8">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                                    <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    Pending Offers
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">Candidates awaiting response</p>
                                <div class="space-y-4">
                                    <div v-for="applicant in applicants.filter(a => a.stage === 'offer')" :key="applicant.id" class="border border-gray-200 dark:border-gray-600 rounded-lg p-5">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-white">{{ applicant.name }}</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ applicant.position }}</p>
                                            </div>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 text-xs rounded-full">Awaiting Response</span>
                                        </div>
                                        <div class="flex gap-3">
                                            <button class="flex-1 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg text-sm font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">View Offer</button>
                                            <button class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition">Send Reminder</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                                    <CheckSquare class="w-5 h-5 text-green-600 dark:text-green-400" />
                                    Onboarding Progress
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">New hires completing setup</p>
                                <div class="space-y-5">
                                    <div v-for="person in onboarding" :key="person.id" class="border border-gray-200 dark:border-gray-600 rounded-lg p-5">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-white">{{ person.name }}</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ person.position }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Starts: {{ person.startDate }}</p>
                                            </div>
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs rounded-full">{{ person.status }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ person.progress }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" :style="{ width: person.progress + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Job Modal -->
        <teleport to="body">
            <div v-if="showJobModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Job Opening</h2>
                        <button @click="showJobModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-3xl">&times;</button>
                    </div>
                    <div class="p-6 space-y-8">
                        <!-- Form fields -->
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job Title *</label>
                                <input v-model="newJob.title" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500" placeholder="e.g. Marketing Manager" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department *</label>
                                <select v-model="newJob.department" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Department</option>
                                    <option>Engineering</option><option>Product</option><option>Design</option><option>Marketing</option><option>Sales</option><option>HR</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location *</label>
                                <input v-model="newJob.location" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500" placeholder="e.g. Remote, London" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salary Range</label>
                                <input v-model="newJob.salary" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500" placeholder="$100k - $140k" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job Description *</label>
                            <textarea v-model="newJob.description" rows="5" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500" placeholder="Describe the role and responsibilities..."></textarea>
                        </div>
                    </div>
                    <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 p-6 flex justify-end gap-4">
                        <button @click="showJobModal = false" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition">
                            Cancel
                        </button>
                        <button @click="createJob" :disabled="!isFormValid" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg font-medium shadow-sm transition disabled:cursor-not-allowed">
                            Create Job Opening
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    Briefcase, Users, FileText, Calendar, Plus, Eye, Edit, Clock, User, Mail, MapPin, CheckSquare
} from 'lucide-vue-next'

const activeTab = ref('jobs')
const applicantView = ref('board')
const showJobModal = ref(false)

// REALISTIC DUMMY DATA — No more "Senior Software Engineer" spam!
const jobsList = ref([
    {
        id: 1,
        title: 'Product Marketing Manager',
        department: 'Marketing',
        location: 'San Francisco, CA',
        status: 'Active',
        applicants: 68,
        posted: '2024-11-28',
        salary: '$130k – $170k',
        type: 'Full-time',
        description: 'Lead go-to-market strategy for our flagship product. Own messaging, positioning, and launch campaigns that drive adoption and revenue growth.',
        skills: ['Product Marketing', 'Go-to-Market', 'Positioning', 'Content Strategy', 'Analytics']
    },
    {
        id: 2,
        title: 'Full Stack Engineer',
        department: 'Engineering',
        location: 'Remote (US)',
        status: 'Active',
        applicants: 94,
        posted: '2024-11-25',
        salary: '$140k – $190k',
        type: 'Full-time',
        description: 'Build end-to-end features for our core product using React, Node.js, and modern cloud infrastructure. Work directly with design and product teams.',
        skills: ['React', 'Node.js', 'TypeScript', 'PostgreSQL', 'AWS', 'GraphQL']
    },
    {
        id: 3,
        title: 'UX Researcher',
        department: 'Design',
        location: 'New York, NY',
        status: 'Active',
        applicants: 37,
        posted: '2024-11-22',
        salary: '$110k – $150k',
        type: 'Full-time',
        description: 'Conduct user research to inform product decisions. Run interviews, usability tests, and synthesize insights into actionable recommendations.',
        skills: ['User Research', 'Usability Testing', 'Figma', 'Survey Design', 'Data Analysis']
    },
    {
        id: 4,
        title: 'Sales Director',
        department: 'Sales',
        location: 'London, UK',
        status: 'Draft',
        applicants: 0,
        posted: '2024-12-01',
        salary: '£120k – £180k + Commission',
        type: 'Full-time',
        description: 'Build and lead our EMEA sales team. Drive enterprise deals and establish partnerships with Fortune 500 companies.',
        skills: ['Enterprise Sales', 'SaaS', 'Negotiation', 'Team Leadership', 'CRM']
    },
])

const applicants = ref([
    { id: 1, name: 'Sarah Johnson', position: 'Product Marketing Manager', stage: 'applied', email: 'sarah.j@email.com', applied: '2024-11-29', experience: '6 years' },
    { id: 2, name: 'Michael Chen', position: 'Full Stack Engineer', stage: 'screening', email: 'michael.c@email.com', applied: '2024-11-27', experience: '8 years' },
    { id: 3, name: 'Emily Rodriguez', position: 'UX Researcher', stage: 'interview', email: 'emily.r@email.com', applied: '2024-11-26', experience: '5 years' },
    { id: 4, name: 'David Kim', position: 'Full Stack Engineer', stage: 'offer', email: 'david.k@email.com', applied: '2024-11-24', experience: '7 years' },
    { id: 5, name: 'Lisa Wang', position: 'Product Marketing Manager', stage: 'applied', email: 'lisa.w@email.com', applied: '2024-11-30', experience: '4 years' },
])

const interviews = ref([
    { id: 1, candidate: 'Emily Rodriguez', position: 'UX Researcher', date: '2024-12-04', time: '10:00 AM', interviewer: 'John Smith', type: 'Research Presentation' },
    { id: 2, candidate: 'Michael Chen', position: 'Full Stack Engineer', date: '2024-12-04', time: '2:00 PM', interviewer: 'Sarah Lee', type: 'Technical Interview' },
    { id: 3, candidate: 'David Kim', position: 'Full Stack Engineer', date: '2024-12-05', time: '11:00 AM', interviewer: 'Mark Johnson', type: 'Final Round' },
])

const onboarding = ref([
    { id: 1, name: 'Jessica Brown', position: 'Content Writer', startDate: '2024-12-09', progress: 75, status: 'In Progress' },
    { id: 2, name: 'Robert Taylor', position: 'Data Analyst', startDate: '2024-12-12', progress: 40, status: 'In Progress' },
    { id: 3, name: 'Anna Martinez', position: 'Customer Success Manager', startDate: '2024-12-16', progress: 90, status: 'Almost Complete' },
])

const stages = ref([
    { id: 'applied', name: 'Applied', count: 23, color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
    { id: 'screening', name: 'Screening', count: 15, color: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' },
    { id: 'interview', name: 'Interview', count: 9, color: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' },
    { id: 'offer', name: 'Offer', count: 4, color: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
    { id: 'hired', name: 'Hired', count: 7, color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' },
])

// Dynamic counts
const tabCounts = computed(() => ({
    applicants: applicants.value.length,
    interviews: interviews.value.length,
    offers: applicants.value.filter(a => a.stage === 'offer').length + onboarding.value.length
}))

const tabs = [
    { id: 'jobs', label: 'Job Openings', icon: Briefcase },
    { id: 'applicants', label: 'Applicants', icon: Users, get count() { return tabCounts.value.applicants } },
    { id: 'interviews', label: 'Interviews', icon: Calendar, get count() { return tabCounts.value.interviews } },
    { id: 'offers', label: 'Offers & Onboarding', icon: FileText, get count() { return tabCounts.value.offers } },
]

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffDays = Math.floor((now - date) / 86400000)
    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return 'Yesterday'
    if (diffDays < 7) return `${diffDays} days ago`
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const newJob = reactive({
    title: '', department: '', location: '', salary: '', description: ''
})

const isFormValid = computed(() => newJob.title && newJob.department && newJob.location && newJob.description)

const createJob = () => {
    jobsList.value.unshift({
        id: Date.now(),
        title: newJob.title,
        department: newJob.department,
        location: newJob.location,
        salary: newJob.salary || 'Competitive',
        type: 'Full-time',
        description: newJob.description,
        skills: ['Team Player', 'Communication', 'Growth Mindset'],
        applicants: 0,
        posted: new Date().toISOString().split('T')[0],
        status: 'Active'
    })
    showJobModal.value = false
    Object.assign(newJob, { title: '', department: '', location: '', salary: '', description: '' })
}
</script>

<style scoped>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.line-clamp-3 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}
</style>
