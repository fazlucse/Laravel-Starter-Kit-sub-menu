<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue';
import { useForm,Link } from '@inertiajs/vue3';

const props = defineProps<{
  person: any,
  editable?: boolean
}>();

const editable = props.editable ?? false;

const form = useForm({
  name: props.person.name || '',
  designation: props.person.designation || '',
  phone: props.person.phone || '',
  email: props.person.email || '',
  country: props.person.country || '',
  city: props.person.city || '',
  address: props.person.address || '',
  present_address: props.person.present_address || '',
  education: props.person.education || '',
  section: props.person.section || '',
  material_status: props.person.material_status || '',
  father_name: props.person.father_name || '',
  mother_name: props.person.mother_name || '',
  company: props.person.company || '',
  nationality: props.person.nationality || '',
  national_id: props.person.national_id || '',
  tin: props.person.tin || '',
  dob: props.person.dob || '',
  avatar: props.person.avatar || null,
  status_photo: props.person.status_photo || null,
});

// Breadcrumbs array
const breadcrumbs = [
  { title: 'Dashboard', href: '/' },
  { title: 'People', href: '/people' },
  { title: 'Profile' }
];
</script>

<template>
  <AppLayout
    title="People Profile"
    :breadcrumbs="breadcrumbs"
  >
    <!-- Back Button -->
    <div class="mb-2 mt-1">
      <Link
        href="/people"
        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-md hover:bg-gray-400 dark:hover:bg-gray-700 transition"
      >
        &larr; Back
      </Link>
    </div>

    <!-- Profile Card -->
    <div class="w-full mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 border border-gray-200 dark:border-gray-700">
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Avatar -->
        <div class="flex-shrink-0">
          <img
            v-if="form.photo"
            :src="`/${form.photo}`"
            class="w-32 h-32 rounded-full object-cover border border-gray-300 dark:border-gray-600"
            alt="Avatar"
          />
          <div v-else class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500">
            No Photo
          </div>
        </div>

        <!-- User Info -->
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Name:</span>
            <span class="text-gray-900 dark:text-white">{{ form.name }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Designation:</span>
            <span class="text-gray-900 dark:text-white">{{ form.designation || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Email:</span>
            <span class="text-gray-900 dark:text-white">{{ form.email || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Phone:</span>
            <span class="text-gray-900 dark:text-white">{{ form.phone || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Date of Birth:</span>
            <span class="text-gray-900 dark:text-white">{{ form.dob || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Marital Status:</span>
            <span class="text-gray-900 dark:text-white">{{ form.material_status || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Nationality:</span>
            <span class="text-gray-900 dark:text-white">{{ form.nationality || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Company:</span>
            <span class="text-gray-900 dark:text-white">{{ form.company || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-40">Section/Department:</span>
            <span class="text-gray-900 dark:text-white">{{ form.section || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Education:</span>
            <span class="text-gray-900 dark:text-white">{{ form.education || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">National ID:</span>
            <span class="text-gray-900 dark:text-white">{{ form.national_id || '-' }}</span>
          </div>
          <div class="flex gap-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">TIN:</span>
            <span class="text-gray-900 dark:text-white">{{ form.tin || '-' }}</span>
          </div>
          <div class="flex gap-2 md:col-span-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Permanent Address:</span>
            <span class="text-gray-900 dark:text-white">{{ form.address || '-' }}</span>
          </div>
          <div class="flex gap-2 md:col-span-2">
            <span class="font-semibold text-gray-700 dark:text-gray-300 w-36">Present Address:</span>
            <span class="text-gray-900 dark:text-white">{{ form.present_address || '-' }}</span>
          </div>
        </div>
      </div>

      <!-- Edit Button -->
      <div v-if="editable" class="mt-6 text-right">
        <Link
          :href="`/people/${props.person.id}/edit`"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md"
        >
          Edit Profile
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
