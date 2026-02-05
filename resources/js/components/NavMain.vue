<!-- NavMain.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
} from '@/components/ui/dropdown-menu'
import { Link } from '@inertiajs/vue3'
import { ChevronDown } from 'lucide-vue-next'
import { type NavItem } from '@/types'
import { useSidebar } from '@/components/ui/sidebar'

defineProps<{
  items: NavItem[]
}>()

const { isMobile, state } = useSidebar()
const openSubMenus = ref<string[]>([])

const toggleSubMenu = (title: string) => {
  openSubMenus.value = openSubMenus.value.includes(title)
    ? openSubMenus.value.filter(t => t !== title)
    : [...openSubMenus.value, title]
}
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem
      v-for="(item, index) in items"
      :key="item.title"
      class="border-b border-border last:border-b-0"
    >
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            :class="[
              'flex items-center gap-2 w-full transition-colors cursor-pointer',
              item.subItems
                ? 'data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground'
                : '',
              state === 'collapsed' && !isMobile ? 'justify-center' : '',
            ]"
            @click="item.subItems ? toggleSubMenu(item.title) : null"
          >
            <!-- Single menu item (no subitems) -->
            <Link
              v-if="!item.subItems && item.href"
              :href="item.href"
              class="flex items-center gap-2 w-full"
              :class="state === 'collapsed' && !isMobile ? 'justify-center' : ''"
            >
              <component
                :is="item.icon"
                v-if="item.icon"
                class="h-5 w-5 shrink-0"
                :class="state === 'collapsed' && !isMobile ? 'mr-0' : 'mr-2'"
              />
              <span
                v-if="isMobile || state !== 'collapsed'"
                class="truncate"
              >
                {{ item.title }}
              </span>
            </Link>

            <!-- Parent item with subitems -->
            <div
              v-else
              class="flex items-center gap-2 w-full"
              :class="state === 'collapsed' && !isMobile ? 'justify-center' : ''"
            >
              <component
                :is="item.icon"
                v-if="item.icon"
                class="h-5 w-5 shrink-0"
                :class="state === 'collapsed' && !isMobile ? 'mr-0' : 'mr-2'"
              />
              <span
                v-if="isMobile || state !== 'collapsed'"
                class="truncate"
              >
                {{ item.title }}
              </span>
              <ChevronDown
                :class="[
                  'h-4 w-4 ml-auto transition-transform duration-200',
                  openSubMenus.includes(item.title) ? 'rotate-180' : '',
                  state === 'collapsed' && !isMobile ? 'hidden' : '',
                ]"
              />
            </div>
          </SidebarMenuButton>
        </DropdownMenuTrigger>

        <!-- Submenu Dropdown -->
        <DropdownMenuContent
          v-if="item.subItems"
          class="min-w-60 rounded-lg shadow-lg border border-border/50"
          :side="isMobile ? 'bottom' : state === 'collapsed' ? 'right' : 'bottom'"
          :align="state === 'collapsed' ? 'start' : 'end'"
          :side-offset="8"
        >
          <div
            v-for="(subItem, subIndex) in item.subItems"
            :key="subItem.title"
            :class="[
              'px-2 py-1',
              // Add bottom border to all subitems except the last one
              subIndex < item.subItems!.length - 1 ? 'border-b border-border/60' : ''
            ]"
          >
            <Link
              :href="subItem.href"
              class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors cursor-pointer w-full"
            >
              <span>{{ subItem.title }}</span>
            </Link>
          </div>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>