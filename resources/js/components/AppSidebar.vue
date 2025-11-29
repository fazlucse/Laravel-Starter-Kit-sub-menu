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
import { type NavItem } from '@/types'; // Import NavItem
import { Link } from '@inertiajs/vue3';
import {   LayoutGrid,
  Menu,
  Users,
  CalendarCheck,
  FileText,
  Folder,
  Briefcase,
    ClipboardList,
    Receipt,
    Calendar,
  MapPin,
  FileSignature, } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid, // Grid icon for Dashboard
  },
//   {
//     title: 'Menu',
//     href: '/menus.index',
//     icon: Menu, // Menu icon
//   },
  {
    title: 'People',
    href: '/people.index',
    icon: Users, // Users icon
  },
  {
    title: 'Employee Management',
    href: '/employees',
    icon: Briefcase, // Briefcase for employees
  },
  {
    title: 'Attendance',
    href: '/attendance',
    icon: CalendarCheck, // Calendar icon for attendance
  },
  {
    title: 'Leave Request',
    href: '/leave-request',
    icon: FileText, // Document icon for leave request
  },
  {
    title: 'Leave Allotment',
    href: '/leave-allotments',
    icon: ClipboardList, // List icon for leave allotment
  },
  {
    title: 'Payroll',
    href: '/payroll',
    icon: Receipt, // Dollar file icon for payroll
    },
  {
    title: 'Holiday Management',
    href: '/holidays',
    icon: Calendar, // Users icon
    },
    {
    title: 'Movement Regsiter',
    href: '/people.index',
    icon: MapPin, // Users icon
  },
  {
    title: 'Recruitment & ATS',
    href: '#',
    icon: Folder,
    subItems: [
      { title: 'Job Openings', href: '/projects/active', icon: Briefcase },
      { title: 'Applicants', href: '/projects/archived', icon: Users },
      { title: 'Interviews', href: '/projects/interviews', icon: ClipboardList },
      { title: 'Offers & Onboarding', href: '/projects/offers', icon: FileSignature },
    ],
  },
  {
    title: 'Reports',
    href: '#',
    icon: FileText,
    subItems: [
      { title: 'Employee Reports', href: '/reports/add', icon: FileText },
        { title: 'Attendance Report', href: '/reports/list', icon: ClipboardList },
        { title: 'Movement Report', href: '/reports/list', icon: ClipboardList },
        { title: 'Leave Report', href: '/reports/list', icon: ClipboardList },
        { title: 'Attendance Report', href: '/reports/list', icon: ClipboardList },
    ],
  },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
