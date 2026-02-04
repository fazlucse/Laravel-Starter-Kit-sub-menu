<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    userPhoto?: string | null;
  userName?: string;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    userPhoto: null,
  userName: 'Fazlu Niloy',
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
           <AppSidebarHeader
        :breadcrumbs="breadcrumbs"
        :user-photo="userPhoto"
        :user-name="userName"
      />
            <slot />
        </AppContent>
    </AppShell>
</template>
