<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, ClipboardList, FileText, LayoutGrid, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

// Admin navigation items based on permissions
const adminNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
    const user = page.props.auth?.user as any;

    if (!user) return items;

    // Check if user can view users
    if (user.permissions?.includes('view users') || user.roles?.includes('Super Admin')) {
        items.push({
            title: 'Users',
            href: '/admin/users',
            icon: Users,
        });
    }

    // Check if user can view roles
    if (user.permissions?.includes('view roles') || user.roles?.includes('Super Admin')) {
        items.push({
            title: 'Roles',
            href: '/admin/roles',
            icon: Shield,
        });
    }

    // Check if user can view insights
    if (user.permissions?.includes('view insights') || user.roles?.includes('Super Admin')) {
        items.push({
            title: 'Insights',
            href: '/admin/insights',
            icon: BookOpen,
        });
    }

    // Check if user can view diagnostics
    if (user.permissions?.includes('view diagnostics') || user.roles?.includes('Super Admin')) {
        items.push({
            title: 'Diagnostics',
            href: '/admin/diagnostics',
            icon: ClipboardList,
        });
    }

    // Check if user can view submissions
    if (user.permissions?.includes('view submissions') || user.roles?.includes('Super Admin')) {
        items.push({
            title: 'Submissions',
            href: '/admin/submissions',
            icon: FileText,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
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
            <NavMain v-if="adminNavItems.length > 0" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
