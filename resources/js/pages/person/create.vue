<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, watch,onMounted, onUnmounted } from 'vue'
import LoadingSpinner from '@/Components/custom/LoadingSpinner.vue'
import flatpickr from 'flatpickr'

const props = defineProps<{
  person?: any
}>()

const { props: pageProps } = usePage()
const isEdit = !!props.person

const form = useForm({
  name: props.person?.name || '',
  designation: props.person?.designation || '',
  phone: props.person?.phone || '',
  email: props.person?.email || '',
  country: props.person?.country || '',
  city: props.person?.city || '',
  address: props.person?.address || '',
  present_address: props.person?.present_address || '',
  education: props.person?.education || '',
  section: props.person?.section || '',
  material_status: props.person?.material_status || '',
  father_name: props.person?.father_name || '',
  mother_name: props.person?.mother_name || '',
  company: props.person?.company || '',
  nationality: props.person?.nationality || '',
  national_id: props.person?.national_id || '',
  tin: props.person?.tin || '',
  dob: props.person?.dob || '', // ← NEW: Date of Birth
  avatar: null as File | null,
  status_photo: null as File | null,
})

watch(
  () => props.person,
  (newPerson) => {
    if (newPerson) {
      Object.keys(form.data()).forEach(key => {
        if (key !== 'avatar' && key !== 'status_photo') {
          form[key] = newPerson[key] || ''
        }
      })
    } else {
      form.reset()
    }
  },
  { immediate: true }
)

const avatarInput = ref<HTMLInputElement | null>(null)
const statusPhotoInput = ref<HTMLInputElement | null>(null)
const dateInput = ref<HTMLInputElement | null>(null)
let fp: any = null
onMounted(() => {
  if (dateInput.value) {
    fp = flatpickr(dateInput.value, {
      dateFormat: 'd-m-Y',
      defaultDate: form.dob,
      maxDate: 'today',
      theme: 'dark', // auto-detects dark mode
      onChange: (selectedDates: Date[]) => {
        form.dob = selectedDates[0] ? selectedDates[0].toISOString().split('T')[0] : ''
      },
    })
  }
})

onUnmounted(() => {
  fp?.destroy()
})
function handleAvatar(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) form.avatar = file
}

function handleStatusPhoto(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) form.status_photo = file
}

function submit() {
  const formData = new FormData()
  Object.entries(form.data()).forEach(([key, value]) => {
    if (value !== null && value !== '') {
      formData.append(key, value as any)
    }
  })

  if (isEdit) {
    formData.append('_method', 'PUT')
    form.post(`/people/${props.person.id}`, {
      data: formData,
      forceFormData: true,
    })
  } else {
    form.post('/people', {
      data: formData,
      forceFormData: true,
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'People', href: '/people' },
    { title: isEdit ? 'Edit Person' : 'Create Person' }
  ]">
    <div class="min-w-4xl max-w-7xl  mx-auto p-2">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 border border-sidebar-border/70">
        <form @submit.prevent="submit" >

          <!-- PERSONAL INFO -->
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Personal Information</h2>
            <div class="grid md:grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  required
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Designation</label>
                <input
                  v-model="form.designation"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.designation ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.designation" class="text-red-500 text-xs mt-1">{{ form.errors.designation }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.email ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone  <span class="text-red-500">*</span></label>
                <input
                  v-model="form.phone"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.phone ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
              </div>
            </div>
          </div>

          <!-- ADDRESS INFO -->
          <div>
            <div class="grid md:grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                <input
                  v-model="form.country"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.country ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                <input
                  v-model="form.city"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.city ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permanent Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.address ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                ></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Present Address</label>
                <textarea
                  v-model="form.present_address"
                  rows="2"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.present_address ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- FAMILY & PERSONAL -->
          <div>
            <div class="grid md:grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Father's Name</label>
                <input
                  v-model="form.father_name"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.father_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mother's Name</label>
                <input
                  v-model="form.mother_name"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.mother_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marital Status</label>
                <select
                  v-model="form.material_status"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.material_status ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                >
                  <option value="">Select</option>
                  <option>Single</option>
                  <option>Married</option>
                  <option>Divorced</option>
                  <option>Widowed</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality</label>
                <input
                  v-model="form.nationality"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.nationality ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
            </div>
          </div>

          <!-- PROFESSIONAL -->
          <div>
            <div class="grid md:grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                <input
                  v-model="form.company"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.company ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section/Department</label>
                <input
                  v-model="form.section"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.section ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Education</label>
                <textarea
                  v-model="form.education"
                  rows="3"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.education ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- IDs -->
          <div >
            <div class="grid md:grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">National ID</label>
                <input
                  v-model="form.national_id"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.national_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.national_id" class="text-red-500 text-xs mt-1">{{ form.errors.national_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TIN</label>
                <input
                  v-model="form.tin"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.tin ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.tin" class="text-red-500 text-xs mt-1">{{ form.errors.tin }}</p>
              </div>
            </div>
          </div>

          <!-- PHOTOS -->
          <div>
            <div class="grid md:grid-cols-2 gap-2">
               <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Date of Birth
                </label>
                <input
                  ref="dateInput"
                  type="text"
                  placeholder="YYYY-MM-DD"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.dob ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.dob" class="text-red-500 text-xs mt-1">{{ form.errors.dob }}</p>
              </div>
               <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile Photo</label>
                <input
                  ref="avatarInput"
                  type="file"
                  accept="image/*"
                  @change="handleAvatar"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-blue-400"
                />
                <p v-if="form.avatar" class="text-sm text-green-600 mt-1">{{ form.avatar.name }}</p>
                <p v-if="form.errors.avatar" class="text-red-500 text-xs mt-1">{{ form.errors.avatar }}</p>
                <img
                  v-if="isEdit && props.person.avatar"
                  :src="`/storage/${props.person.avatar}`"
                  class="mt-2 w-24 h-24 rounded-full object-cover border"
                />
              </div>
            </div>
          </div>

          <!-- SUBMIT -->
          <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Link
              href="/people"
              class="cursor-pointer px-5 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-md hover:bg-gray-400 dark:hover:bg-gray-700 transition"
            >
              Cancel
            </Link>
           
           
        
            <button
              type="submit"
              :disabled="form.processing"
              class=" cursor-pointer px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition flex items-center gap-2"
            > <LoadingSpinner v-if="form.processing" />
              <!-- <span v-if="form.processing" class="animate-spin">Loading</span> -->
              {{ isEdit ? 'Update' : 'Submit' }}
               
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>