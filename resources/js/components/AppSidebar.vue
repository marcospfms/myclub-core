<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Award,
    // BookOpen,
    // FolderGit2,
    LayoutGrid,
    Map,
    Shield,
    Shapes,
    Tags,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
// import NavFooter from '@/components/NavFooter.vue';
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
import { dashboard } from '@/routes';
import { index as badgeTypesIndex } from '@/routes/admin/catalog/badge-types';
import { index as categoriesIndex } from '@/routes/admin/catalog/categories';
import { index as formationsIndex } from '@/routes/admin/catalog/formations';
import { index as positionsIndex } from '@/routes/admin/catalog/positions';
import { index as sportModesIndex } from '@/routes/admin/catalog/sport-modes';
import { index as staffRolesIndex } from '@/routes/admin/catalog/staff-roles';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const catalogNavItems: NavItem[] = [
    {
        title: 'Sport Modes',
        href: sportModesIndex.url(),
        icon: Map,
    },
    {
        title: 'Categories',
        href: categoriesIndex.url(),
        icon: Tags,
    },
    {
        title: 'Positions',
        href: positionsIndex.url(),
        icon: Shapes,
    },
    {
        title: 'Formations',
        href: formationsIndex.url(),
        icon: Shield,
    },
    {
        title: 'Staff Roles',
        href: staffRolesIndex.url(),
        icon: Users,
    },
    {
        title: 'Badge Types',
        href: badgeTypesIndex.url(),
        icon: Award,
    },
];

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Repository',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: FolderGit2,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="catalogNavItems" label="Catalog" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
