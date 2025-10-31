<script setup lang="ts">
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
} from '@/components/ui/dropdown-menu';
import { LogOut, Folder, Settings } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
  userPhoto?: string | null;
  userName?: string;
}

const { breadcrumbs, userPhoto, userName } = withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
  userPhoto: null,
  userName: '',
});

const emit = defineEmits<{
  (e: 'update:userPhoto', value: null): void;
}>();

// Compute two-letter initials (optional)
const initials = computed(() => {
  if (!userName || userName.trim() === '') return 'JD';
  const nameParts = userName.trim().split(' ');
  const firstLetter = nameParts[0]?.[0] || 'J';
  const secondLetter = nameParts[1]?.[0] || nameParts[0]?.[1] || 'D';
  return `${firstLetter}${secondLetter}`.toUpperCase();
});

// Handle image error
const handleImageError = () => {
  emit('update:userPhoto', null);
};
</script>

<template>
  <header
    class="sticky top-0 z-50 flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 bg-background px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
  >
    <div class="flex items-center gap-2">
      <SidebarTrigger class="-ml-1" />
      <template v-if="breadcrumbs && breadcrumbs.length > 0">
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </template>
    </div>
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <button class="focus:outline-none" aria-label="User menu">
          <div
            class="h-10 w-10 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center"
          >
            <img
              v-if="userPhoto"
              :src="userPhoto"
              :alt="userName || 'User'"
              class="h-full w-full object-cover"
              @error="handleImageError"
            />
            <Folder
              v-else
              class="h-6 w-6 text-gray-500"
            />
          </div>
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent class="w-48 p-2" align="end" >
        <div class="flex flex-col gap-2">
          <Link
            :href="'/settings'"
            as="button"
            class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground rounded-md"
          >
            <Settings class="h-4 w-4" />
            <span>Settings</span>
          </Link>
          <Link
            :href="'/logout'"
            method="post"
            as="button"
            class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground rounded-md"
          >
            <LogOut class="h-4 w-4" />
            <span>Logout</span>
          </Link>
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  </header>
</template>

<style scoped>
.bg-primary {
  background-color: #3b82f6;
}
.text-primary-foreground {
  color: #ffffff;
}
</style>