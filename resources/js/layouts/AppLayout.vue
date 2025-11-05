<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useToast } from '@/composables/useToast'
import GlobalPageLoader from '@/components/custom/PageLoader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
useToast();
withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="flex h-screen flex-col bg-background">
    <AppLayout :breadcrumbs="breadcrumbs">
        <PageLoader />
        <slot />
    </AppLayout>
    </div>

</template>
