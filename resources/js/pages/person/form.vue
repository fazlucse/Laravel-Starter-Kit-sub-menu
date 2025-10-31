<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { 
  Mail, Phone, MapPin, Home, GraduationCap, Building2, 
  Users, Heart, User, IdCard, FileText, Edit2, Globe 
} from 'lucide-vue-next'
import { computed } from 'vue'

interface Props {
  person: {
    id: number
    name: string
    designation?: string
    phone?: string
    email?: string
    country?: string
    city?: string
    address?: string
    present_address?: string
    education?: string
    section?: string
    material_status?: string
    father_name?: string
    mother_name?: string
    company?: string
    nationality?: string
    national_id?: string
    tin?: string
    status_photo?: string | null
    avatar?: string | null
  }
  editable?: boolean
}

const props = withDefaults(defineProps<Props>(), { editable: false })

const initials = computed(() => {
  const names = props.person.name.trim().split(' ')
  const first = names[0]?.[0] || ''
  const last = names[names.length - 1]?.[0] || ''
  return (first + last).toUpperCase() || 'NA'
})
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden max-w-6xl mx-auto">

    <!-- Header: Avatar + Name + Designation -->
    <div class="relative h-40 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600">
      <div class="absolute -bottom-16 left-8 flex items-end gap-6">
        <!-- Avatar -->
        <div class="relative">
          <div
            class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 overflow-hidden bg-gray-200 flex items-center justify-center shadow-xl"
          >
            <img
              v-if="person.avatar"
              :src="person.avatar"
              :alt="person.name"
              class="w-full h-full object-cover"
            />
            <span v-else class="text-4xl font-bold text-gray-600 dark:text-gray-400">
              {{ initials }}
            </span>
          </div>
          <Link
            v-if="editable"
            :href="`/people/${person.id}/edit`"
            class="absolute bottom-1 right-1 bg-blue-600 text-white p-2.5 rounded-full shadow-lg hover:bg-blue-700 transition"
          >
            <Edit2 class="w-5 h-5" />
          </Link>
        </div>

        <!-- Name + Designation -->
        <div class="text-white pb-4">
          <h1 class="text-3xl font-bold">{{ person.name }}</h1>
          <p v-if="person.designation" class="text-xl opacity-90">
            {{ person.designation }}
          </p>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="pt-20 px-8 pb-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

        <!-- Column 1: Contact -->
        <div class="space-y-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <Phone class="w-5 h-5 text-green-600" /> Contact
          </h3>
          <div class="space-y-3 text-gray-700 dark:text-gray-300">
            <div v-if="person.email" class="flex items-center gap-3">
              <Mail class="w-4 h-4 text-blue-600" />
              <a :href="`mailto:${person.email}`" class="hover:underline">{{ person.email }}</a>
            </div>
            <div v-if="person.phone" class="flex items-center gap-3">
              <Phone class="w-4 h-4 text-green-600" />
              <span>{{ person.phone }}</span>
            </div>
          </div>
        </div>

        <!-- Column 2: Location -->
        <div class="space-y-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <MapPin class="w-5 h-5 text-purple-600" /> Location
          </h3>
          <div class="space-y-3 text-gray-700 dark:text-gray-300">
            <div v-if="person.address" class="flex items-start gap-3">
              <Home class="w-4 h-4 text-orange-600 mt-0.5" />
              <div>
                <p class="font-medium">Permanent Address</p>
                <p>{{ person.address }}</p>
              </div>
            </div>
            <div v-if="person.present_address" class="flex items-start gap-3">
              <Home class="w-4 h-4 text-teal-600 mt-0.5" />
              <div>
                <p class="font-medium">Present Address</p>
                <p>{{ person.present_address }}</p>
              </div>
            </div>
            <div v-if="person.city || person.country" class="flex items-center gap-3">
              <Globe class="w-4 h-4 text-indigo-600" />
              <span>{{ person.city }}{{ person.city && person.country ? ', ' : '' }}{{ person.country }}</span>
            </div>
          </div>
        </div>

        <!-- Column 3: Personal -->
        <div class="space-y-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <Users class="w-5 h-5 text-pink-600" /> Personal
          </h3>
          <div class="space-y-3 text-gray-700 dark:text-gray-300">
            <div v-if="person.father_name" class="flex items-center gap-3">
              <User class="w-4 h-4 text-blue-600" />
              <span>Father: {{ person.father_name }}</span>
            </div>
            <div v-if="person.mother_name" class="flex items-center gap-3">
              <User class="w-4 h-4 text-pink-600" />
              <span>Mother: {{ person.mother_name }}</span>
            </div>
            <div v-if="person.material_status" class="flex items-center gap-3">
              <Heart class="w-4 h-4 text-red-600" />
              <span>Status: {{ person.material_status }}</span>
            </div>
            <div v-if="person.nationality" class="flex items-center gap-3">
              <Globe class="w-4 h-4 text-teal-600" />
              <span>Nationality: {{ person.nationality }}</span>
            </div>
          </div>
        </div>

        <!-- Column 4: Professional -->
        <div class="space-y-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <Building2 class="w-5 h-5 text-cyan-600" /> Professional
          </h3>
          <div class="space-y-3 text-gray-700 dark:text-gray-300">
            <div v-if="person.company" class="flex items-center gap-3">
              <Building2 class="w-4 h-4 text-cyan-600" />
              <span>{{ person.company }}</span>
            </div>
            <div v-if="person.section" class="flex items-center gap-3">
              <FileText class="w-4 h-4 text-amber-600" />
              <span>Section: {{ person.section }}</span>
            </div>
            <div v-if="person.education" class="flex items-start gap-3">
              <GraduationCap class="w-4 h-4 text-indigo-600 mt-0.5" />
              <div>
                <p class="font-medium">Education</p>
                <p>{{ person.education }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Column 5: IDs -->
        <div class="space-y-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <IdCard class="w-5 h-5 text-emerald-600" /> Identification
          </h3>
          <div class="space-y-3 text-gray-700 dark:text-gray-300">
            <div v-if="person.national_id" class="flex items-center gap-3">
              <IdCard class="w-4 h-4 text-emerald-600" />
              <span>ID: {{ person.national_id }}</span>
            </div>
            <div v-if="person.tin" class="flex items-center gap-3">
              <FileText class="w-4 h-4 text-purple-600" />
              <span>TIN: {{ person.tin }}</span>
            </div>
          </div>
        </div>

        <!-- Column 6: Status Photo -->
        <div class="space-y-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <FileText class="w-5 h-5 text-rose-600" /> Status Photo
          </h3>
          <div v-if="person.status_photo" class="mt-3">
            <img
              :src="person.status_photo"
              :alt="`${person.name}'s status photo`"
              class="w-full max-w-xs rounded-lg shadow-md border border-gray-300 dark:border-gray-600"
            />
          </div>
          <p v-else class="text-gray-500 italic">No status photo uploaded</p>
        </div>

      </div>
    </div>
  </div>
</template>