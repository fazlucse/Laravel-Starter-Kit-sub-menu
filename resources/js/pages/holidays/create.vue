<template>
    <AppLayout
        title="Add Holiday"
        :breadcrumbs="[
      { title: 'Dashboard', href: '/' },
      { title: 'Holidays', href: '/holidays' },
      { title: 'Add Holiday', href: '#' },
    ]"
    >
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-lg rounded-b-xl p-8 space-y-8">

            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import AppLayout from '@/Layouts/AppLayout.vue'



</script>
