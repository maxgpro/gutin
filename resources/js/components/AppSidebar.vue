<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import hh from '@/routes/hh';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FileText, Folder, LayoutGrid, LogIn, Tag } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

const mainNavItems = computed(() => {
    const items: NavItem[] = [];

    // Категории показываем только тем, кто может их создавать (админы)
    if (page.props.auth?.canCreateBlogCategories) {
        items.push(
        {
            title: 'Blog Categories',
            href: blog.categories.index(),
            icon: Tag,
        });
    }

    // Блог посты доступны всем
    items.push(
        {
            title: 'Blog Posts',
            href: blog.posts.index(),
            icon: FileText,
        });

    // Добавляем пункт HH только для пользователей с доступом
    if (page.props.auth?.canAccessHh) {
        items.push(
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },{
            title: 'Войти через hh.ru',
            href: hh.redirect.url(),
            icon: LogIn,
            external: true,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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
