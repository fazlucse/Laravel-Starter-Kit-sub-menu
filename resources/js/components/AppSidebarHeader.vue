<!-- AppHeader.vue – FIXED VERSION (instant & close dropdown) -->
<script setup lang="ts">
import { computed } from 'vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import { LogOut, Settings, User } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user || {})

const userName = computed(() => user.value?.name || '')
const userEmail = computed(() => user.value?.email || '')
const userPhoto = computed(() => user.value?.photo || null)
const props = defineProps<{
  breadcrumbs?: Array<{ title: string; href?: string }>
}>()

const breadcrumbs = computed(() => props.breadcrumbs || [])


const initials = computed(() => {
  if (!userName.value) return '??'
  const parts = userName.value.trim().split(' ')
  const first = parts[0]?.[0] || ''
  const last = parts.length > 1 ? parts[parts.length - 1]?.[0] : ''
  return (first + last).toUpperCase() || '??'
})

const handleImageError = () => {
  // optionally force fallback
}

// Instant navigation (no portal delay)
const goto = (url: string) => {
  router.visit(url, { preserveState: false })
}
const logout = () => {
  router.post('/logout')
}
</script>

<template>
  <header
    class="sticky top-0 z-50 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-border/70 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-4 md:px-6"
  >
    <div class="flex items-center gap-3">
      <SidebarTrigger class="-ml-1" />
      <Breadcrumbs v-if="breadcrumbs?.length" :breadcrumbs="breadcrumbs" />
    </div>
 <!-- <div class="flex items-center gap-3">
      <SidebarTrigger class="-ml-1" />
      <Breadcrumbs v-if="breadcrumbs?.length" :breadcrumbs="breadcrumbs" />
    </div> -->
    <!-- User Dropdown -->
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <button
          class="flex items-center gap-3 rounded-lg p-2 transition-all hover:bg-accent/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring cursor-pointer"
          aria-label="User menu"
        >
          <div class="relative h-9 w-9 shrink-0 overflow-hidden rounded-full">
            <img
              v-if="userPhoto"
              :src="userPhoto"
              :alt="userName"
              class="h-full w-full object-cover"
              @error="handleImageError"
            />
            <div
              v-else
              class="flex h-full w-full items-center justify-center bg-primary/10 text-sm font-medium text-primary"
            >
              {{ initials }}
            </div>
          </div>
          <div class="hidden text-left sm:block">
            <p class="text-sm font-medium leading-none">{{ userName || 'User' }}</p>
            <p v-if="userEmail" class="text-xs text-muted-foreground mt-0.5">{{ userEmail }}</p>
          </div>
        </button>
      </DropdownMenuTrigger>

      <!-- This is the key fix: force dropdown to stay close + instant -->
      <DropdownMenuContent
        class="w-56 p-3"
        align="end"
        side="bottom"
        :side-offset="8"
        :avoid-collisions="false"
        :hide-when-detached="false"
      >
        <!-- User Info -->
        <div class="flex items-center gap-3 px-2 pb-3">
          <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-sm font-medium text-primary">
            {{ initials }}
          </div>
          <div class="overflow-hidden">
            <p class="text-sm font-medium truncate">{{ userName || 'User' }}</p>
            <p v-if="userEmail" class="text-xs text-muted-foreground truncate">{{ userEmail }}</p>
          </div>
        </div>

        <DropdownMenuSeparator />

        <!-- Instant navigation items -->
        <DropdownMenuItem as-child>
          <button
            @click="goto('/profile')"
            class="flex w-full cursor-pointer items-center gap-2.5 px-2 py-1.5 text-sm"
          >
            <User class="h-4 w-4" />
            Profile
          </button>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <button
            @click="goto('/settings')"
            class="flex w-full cursor-pointer items-center gap-2.5 px-2 py-1.5 text-sm"
          >
            <Settings class="h-4 w-4" />
            Settings
          </button>
        </DropdownMenuItem>

        <DropdownMenuSeparator />

        <DropdownMenuItem as-child>
          <button
            @click="logout"
            class="flex w-full cursor-pointer items-center gap-2.5 px-2 py-1.5 text-sm text-destructive hover:text-destructive"
          >
            <LogOut class="h-4 w-4" />
            Log out
          </button>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </header>
</template>