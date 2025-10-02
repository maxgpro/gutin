<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FileText, Folder, LayoutGrid, Tag } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

const mainNavItems = computed(() => {
    const items: NavItem[] = [];
    if (page.props.auth?.isAdmin) {
        items.push(
        {
            title: 'Blog Categories',
            href: blog.categories.index(),
            icon: Tag,
        });
    }

    items.push(
        {
            title: 'Blog Posts',
            href: blog.posts.index(),
            icon: FileText,
        });
    if (page.props.auth.user) {
        items.push(
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },);
    }

    return items;
});

const footerNavItems = computed(() => {
    const items: NavItem[] = [];

    if (page.props.auth?.isAdmin) {
        items.push(
        {
            title: 'i18n Demo',
            href: '/i18n-demo',
            icon: BookOpen,
        });
    }
    return items;
});
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
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
