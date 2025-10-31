<script setup>
import PersonProfile from '@/Components/PersonProfile.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
defineProps({ person: Object })

const breadcrumbs = [
  { title: 'People', href: '/people' },
  { title: 'Profile' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-8">
      <PersonProfile :person="person" :editable="true" />
    </div>
  </AppLayout>
</template>