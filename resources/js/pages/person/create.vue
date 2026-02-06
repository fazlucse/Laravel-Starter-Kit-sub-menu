<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, watch } from 'vue'
import flatpickr from 'flatpickr'
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue'

const props = defineProps<{ person?: any, countries: any[], cities: any[] }>()
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
     dob: props.person?.dob || '',
     gender: props.person?.gender || '',
     religion: props.person?.religion || '',
    avatar: null as File | null,
})

const avatarPreview = ref(
    props.person?.photo ? `/${props.person.photo}` : null
)
const dateInput = ref<HTMLInputElement | null>(null)
let fp: any = null

onMounted(() => {
    if (dateInput.value) {
        fp = flatpickr(dateInput.value, {
            dateFormat: 'd-m-Y',
            defaultDate: form.dob,
            maxDate: 'today',
            onChange: (dates: Date[]) => form.dob = dates[0]?.toISOString().split('T')[0] || ''
        })
    }
})

onUnmounted(() => fp?.destroy())

watch(() => props.person, (newPerson) => {
    if (newPerson) {
        Object.keys(form.data()).forEach(key => {
            if (key !== 'avatar') {
                form[key] = newPerson[key] || ''
            }
        })
        // avatarPreview.value = newPerson.photo ? `/${newPerson.photo}` : null
    } else {
        form.reset()
        // avatarPreview.value = null
    }
}, { immediate: true })

function handleAvatar(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file) {
        form.avatar = file
        avatarPreview.value = URL.createObjectURL(file)
    }
}

function handleStatusPhoto(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file) {
        form.status_photo = file
        statusPreview.value = URL.createObjectURL(file)
    }
}

function submit() {
    const formData = new FormData()
    Object.entries(form.data()).forEach(([key, value]) => {
        if (value !== null) formData.append(key, value as any)
    })

  if (isEdit) {
  formData.append('_method', 'PUT') // 👈 Tells Laravel it's an update
    form.post(`/people/${props.person.id}`, {
      data: formData,
      forceFormData: true,
    });
    } else {

        form.post('/people', { data: formData, forceFormData: true })
    }
}
</script>

<template>

  <AppLayout :breadcrumbs="[
    { title: 'People', href: '/people.index' },
    { title: isEdit ? 'Edit Person' : 'Create Person' }
  ]">
    <div class="max-w-4xl mx-auto p-2 sm:p-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-8 border border-sidebar-border/70">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- PERSONAL INFO -->
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                <input v-model="form.name"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Designation</label>
                <input v-model="form.designation"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.designation ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />

                </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input v-model="form.email" type="email"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.email ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />
                  <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>

                </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone <span class="text-red-500">*</span></label>
                <input v-model="form.phone"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.phone ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'" />
                 <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
              </div>
               <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">National ID</label>
                <input v-model="form.national_id"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                   <p v-if="form.errors.national_id" class="text-red-500 text-xs mt-1">{{ form.errors.national_id }}</p>
                </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TIN</label>
                <input v-model="form.tin"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                 <p v-if="form.errors.tin" class="text-red-500 text-xs mt-1">{{ form.errors.tin }}</p>

                </div>
              <div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Gender
  </label>
  <select v-model="form.gender"
          class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
    <option value="">Select</option>
    <option value="Male">Male</option>
    <option value="Female">Female</option>
    <option value="Other">Other</option>
  </select>
</div>

<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Religion
  </label>
  <select v-model="form.religion"
          class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
    <option value="">Select</option>
    <option value="Islam">Islam</option>
    <option value="Hinduism">Hinduism</option>
    <option value="Christianity">Christianity</option>
    <option value="Buddhism">Buddhism</option>
    <option value="Other">Other</option>
  </select>
</div>

            </div>
          </div>
          <!-- ADDRESS INFO -->
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4">Address Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
              <select v-model="form.country"
                      class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                      :class="form.errors.country ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                <option value="">Select Country</option>
                <option v-for="country in props.countries" :key="country.id" :value="country.name">
                  {{ country.name }}
                </option>
              </select>
              <p v-if="form.errors.country" class="text-red-500 text-xs mt-1">{{ form.errors.country }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
              <select v-model="form.city"
                      class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                      :class="form.errors.city ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                <option value="">Select City</option>
                <option v-for="city in props.cities" :key="city.id" :value="city.name">
                  {{ city.name }}
                </option>
              </select>
              <p v-if="form.errors.country" class="text-red-500 text-xs mt-1">{{ form.errors.country }}</p>
            </div>

<!--
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                <select v-model="form.city"
                        class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        :disabled="!form.country"
                        :class="form.errors.city ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                  <option value="">Select City</option>
                  <option v-for="city in filteredCities" :key="city.id" :value="city.id">
                    {{ city.name }}
                  </option>
                </select>
                <p v-if="form.errors.city" class="text-red-500 text-xs mt-1">{{ form.errors.city }}</p>
              </div> -->

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permanent Address</label>
                <textarea v-model="form.address" rows="2"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.address ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Present Address</label>
                <textarea v-model="form.present_address" rows="2"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="form.errors.present_address ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"></textarea>
              </div>
            </div>
          </div>

          <!-- FAMILY & PERSONAL -->
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4">Family & Personal</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Father's Name</label>
                <input v-model="form.father_name"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mother's Name</label>
                <input v-model="form.mother_name"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marital Status</label>
                <select v-model="form.material_status"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                  <option value="">Select</option>
                  <option>Single</option>
                  <option>Married</option>
                  <option>Divorced</option>
                  <option>Widowed</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality</label>
                <input v-model="form.nationality"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
              </div>
            </div>
          </div>

          <!-- PROFESSIONAL -->
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4">Professional</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                <input v-model="form.company"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section/Department</label>
                <input v-model="form.section"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Education</label>
                <textarea v-model="form.education" rows="3"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
              </div>
            </div>
          </div>
          <!-- DATE & PHOTOS -->
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4">Date & Photos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                <input ref="dateInput" type="text" placeholder="YYYY-MM-DD"
                  class="w-full rounded-md border px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile Photo</label>
                <input ref="avatar" type="file" accept="image/*" @change="handleAvatar"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-blue-400" />
                <div v-if="avatarPreview" class="mt-2">
                  <img :src="avatarPreview" class="w-24 h-24 rounded-full object-cover border" />
                </div>
              </div>
            </div>
          </div>

          <!-- SUBMIT BUTTONS -->
          <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <Link href="/people.index"
              class="px-5 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-md hover:bg-gray-400 dark:hover:bg-gray-700 text-center">
              Cancel
            </Link>
            <button type="submit" :disabled="form.processing"
              class=" cursor-pointer px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition flex items-center justify-center gap-2">
              <LoadingSpinner v-if="form.processing" />
              {{ isEdit ? 'Update' : 'Submit' }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>
