<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { useToast } from '@/composables/useToast'
import GlobalPageLoader from '@/components/custom/PageLoader.vue'
import type { BreadcrumbItemType } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
  breadcrumbs?: BreadcrumbItemType[]
}
useToast()

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})

const page = usePage()

/*  Safe navigation check  */
const isNavigating = computed(() => {
  const props = page.props.value
  return !!props && (props as any).isNavigating === true
})
</script>

<template>
  <div class="flex h-screen flex-col bg-background">
    <AppLayout :breadcrumbs="breadcrumbs">
      <GlobalPageLoader v-if="isNavigating" />
      <slot />
    </AppLayout>
  </div>
</template>