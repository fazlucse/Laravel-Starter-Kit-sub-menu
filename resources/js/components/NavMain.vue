<!-- NavMain.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
} from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { type NavItem } from '@/types';
import { useSidebar } from '@/components/ui/sidebar';

defineProps<{
    items: NavItem[];
}>();

const { isMobile, state } = useSidebar();
const openSubMenus = ref<string[]>([]);

const toggleSubMenu = (title: string) => {
    if (openSubMenus.value.includes(title)) {
        openSubMenus.value = openSubMenus.value.filter((t) => t !== title);
    } else {
        openSubMenus.value.push(title);
    }
};
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem v-for="item in items" :key="item.title">
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        :class="[
                            'flex items-center gap-2',
                            item.subItems
                                ? 'data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground'
                                : '',
                            state === 'collapsed' && !isMobile ? 'justify-center' : '',
                        ]"
                        @click="item.subItems ? toggleSubMenu(item.title) : null"
                    >
                        <Link
                            v-if="!item.subItems"
                            :href="item.href"
                            class="flex items-center gap-2 w-full"
                            :class="state === 'collapsed' && !isMobile ? 'justify-center' : ''"
                        >
                            <component
                                :is="item.icon"
                                v-if="item.icon"
                                class="h-5 w-5"
                                :class="state === 'collapsed' && !isMobile ? 'mr-0' : 'mr-2'"
                            />
                            <span
                                v-if="isMobile || state !== 'collapsed'"
                                :class="state === 'collapsed' && !isMobile ? 'hidden' : ''"
                            >
                                {{ item.title }}
                            </span>
                        </Link>
                        <div
                            v-else
                            class="flex items-center gap-2 w-full"
                            :class="state === 'collapsed' && !isMobile ? 'justify-center' : ''"
                        >
                            <component
                                :is="item.icon"
                                v-if="item.icon"
                                class="h-5 w-5"
                                :class="state === 'collapsed' && !isMobile ? 'mr-0' : 'mr-2'"
                            />
                            <span
                                v-if="isMobile || state !== 'collapsed'"
                                :class="state === 'collapsed' && !isMobile ? 'hidden' : ''"
                            >
                                {{ item.title }}
                            </span>
                            <ChevronDown
                                v-if="item.subItems"
                                :class="[
                                    'h-4 w-4 ml-auto transition-transform',
                                    openSubMenus.includes(item.title) ? 'rotate-180' : '',
                                    state === 'collapsed' && !isMobile ? 'hidden' : '',
                                ]"
                            />
                        </div>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    v-if="item.subItems"
                    class="min-w-60 rounded-lg"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'bottom'
                    "
                    align="end"
                    :side-offset="4"
                >
                    <div
                        v-for="subItem in item.subItems"
                        :key="subItem.title"
                        class="px-2 py-0"
                    >
                        <Link
                            :href="subItem.href"
                            class="flex items-center gap-2 rounded-md px-3 py-2 hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50"
                        >
                            <span>{{ subItem.title }}</span>
                        </Link>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>