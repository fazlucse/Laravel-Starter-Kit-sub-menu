<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="flex h-screen flex-col bg-background">
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    </div>

</template>
