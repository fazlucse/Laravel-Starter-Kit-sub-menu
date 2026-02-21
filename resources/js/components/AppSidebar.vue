<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3'; // Added usePage
import { computed } from 'vue'; // Added computed
import {
    LayoutGrid,
    Users,
    CalendarCheck,
    FileText,
    Briefcase,
    ClipboardList,
    Receipt,
    Calendar,
    MapPin,
    Settings,
    Building2,
    Database,
    ShieldCheck, // Added for Access Matrix icon
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

/**
 * Robust Permission Helper
 * Checks if the user has ANY permission for the given module slug.
 */
const canAccess = (slug: string | undefined) => {
    if (!slug) return true; // Always show items without a slug (like Dashboard)

    // We check page.props.authUser because that is where your Middleware puts the data
    const authUser = page.props.authUser as any;
    const permissions = authUser?.permissions || [];

    // Check if any permission starts with the module slug (e.g. "movement.")
    return permissions.some((p: string) => p.startsWith(`${slug}.`));
};

const rawNavItems: any[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'People',
        href: '/people.index',
        icon: Users,
        slug: 'person', // Matches person.view, etc.
    },
    {
        title: 'Employee Management',
        href: '/employees',
        icon: Briefcase,
        slug: 'employee',
    },
    {
        title: 'Attendance',
        href: '/attendance',
        icon: CalendarCheck,
        slug: 'attendance',
    },
    {
        title: 'Leave Request',
        href: '/leave-request',
        icon: FileText,
        slug: 'leave-request',
    },
    {
        title: 'Leave Allotment',
        href: '/leave-allotments',
        icon: ClipboardList,
        slug: 'leave-allotment',
    },
    {
        title: 'Payroll',
        href: '/payroll',
        icon: Receipt,
        slug: 'payroll',
    },
    {
        title: 'Holiday Management',
        href: '/holidays',
        icon: Calendar,
        slug: 'holiday',
    },
    {
        title: 'Movement Register',
        href: '/movement-registers',
        icon: MapPin,
        slug: 'movement',
    },
    {
        title: 'Reports',
        href: '#',
        icon: FileText,
        slug: 'reports',
        subItems: [
            { title: 'Employee Reports', href: '/reports/add', icon: FileText, slug: 'reports' },
            { title: 'Attendance Report', href: '/reports/list', icon: ClipboardList, slug: 'reports' },
            { title: 'Movement Report', href: '/reports/list', icon: ClipboardList, slug: 'reports' },
            { title: 'Leave Report', href: '/reports/list', icon: ClipboardList, slug: 'reports' },
        ],
    },
    {
        title: 'Initial Setup',
        href: '#',
        icon: Settings,
        slug: 'reports', // Gate Settings to users with admin/reports access
        subItems: [
            {
                title: 'User Management',
                href: '/users',
                icon: Users,
                slug: 'employee',
            },
            {
                title: 'Company Setup',
                href: '/companies',
                icon: Building2,
                slug: 'employee',
            },
            {
                title: 'Access Matrix',
                href: '/access-control',
                icon: ShieldCheck,
                slug: 'reports'
            },
            {
                title: 'Master Data',
                href: '/info-masters',
                icon: Database,
                slug: 'reports',
            },
        ],
    },
];

// This is the fixed list that respects permissions
const mainNavItems = computed(() => {
    return rawNavItems
        .filter(item => canAccess(item.slug))
        .map(item => {
            if (item.subItems) {
                return {
                    ...item,
                    subItems: item.subItems.filter((sub: any) => canAccess(sub.slug))
                };
            }
            return item;
        });
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo/>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems"/>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems"/>
            <NavUser/>
        </SidebarFooter>
    </Sidebar>
    <slot/>
</template>
