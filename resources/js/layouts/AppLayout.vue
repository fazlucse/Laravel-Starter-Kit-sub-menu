<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { useToast } from '@/composables/useToast'
import GlobalPageLoader from '@/components/custom/PageLoader.vue'
import type { BreadcrumbItemType } from '@/types'
import { usePage } from '@inertiajs/vue3'
import SmartAlert from '@/components/custom/SmartAlert.vue'
import { useAlert } from '@/composables/useAlert'
import { computed } from 'vue'

interface Props {
  breadcrumbs?: BreadcrumbItemType[]
}
useToast()

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})

const page = usePage()
const { alertState } = useAlert()
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
        <SmartAlert
            :show="alertState.show"
            :message="alertState.message"
            :type="alertState.type"
            @close="alertState.show = false"
        />
    </AppLayout>
  </div>
</template>
