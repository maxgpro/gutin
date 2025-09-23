<script setup lang="ts">
import BlogPostCard from '@/components/BlogPostCard.vue';
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import LaravelPaginationAdapter from '@/components/LaravelPaginationAdapter.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogPostsIndexProps } from '@/types/blog';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Blog Posts',
        href: blog.posts.index().url,
    },
];

const props = defineProps<BlogPostsIndexProps>();

const form = ref<{
    search: string;
    category: string | null;
}>({
    search: props.filters.search || '',
    category: props.filters.category || null,
});

const debouncedSearch = useDebounceFn(() => {
    updateFilters();
}, 300);

watch(
    () => form.value.category,
    () => {
        updateFilters();
    },
);

// Фильтры Категорий
const categoryModel = computed({
    // GET: что отдаём Select-у
    get: () => form.value.category ?? '_all', // null → '_all' (Select покажет "All Categories")
    // SET: что записываем обратно в форму
    set: (v: string) => (form.value.category = v === '_all' ? null : v), // '_all' → null, иначе slug
});

function updateFilters() {
    const params = new URLSearchParams();

    if (form.value.search) {
        params.set('search', form.value.search);
    }

    // Добавляем параметр category только если выбрана конкретная категория
    // Если выбрано "All Categories" (_all) или null, параметр не добавляем
    if (form.value.category && form.value.category !== '_all') {
        params.set('category', form.value.category);
    }

    const queryString = params.toString();
    const url = blog.posts.index().url + (queryString ? `?${queryString}` : '');
    router.get(url, {}, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Blog Posts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Filters and Search -->
            <div class="mb-6 flex flex-col gap-4 md:flex-row">
                <div class="flex-1">
                    <Input v-model="form.search" placeholder="Search posts..." class="max-w-md" @input="debouncedSearch" />
                </div>

                <div class="flex gap-2">
                    <Select v-model="categoryModel">
                        <SelectTrigger class="w-48">
                            <SelectValue placeholder="All Categories" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all">All Categories</SelectItem>
                            <SelectItem v-for="category in props.categories" :key="category.id" :value="category.slug">
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Button v-if="$page.props.auth?.user" :href="blog.posts.create()">
                        <Icon name="plus" class="mr-2 h-4 w-4" />
                        New Post
                    </Button>
                </div>
            </div>

            <!-- Posts Grid -->
            <div v-if="posts.data.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <BlogPostCard v-for="post in posts.data" :key="post.id" :post="post" />
            </div>

            <div v-else class="py-12 text-center">
                <Icon name="file-text" class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <p class="text-lg text-muted-foreground">No blog posts found.</p>
                <Button v-if="$page.props.auth?.user" :href="blog.posts.create()" class="mt-4"> Create Your First Post </Button>
            </div>

            <!-- Pagination -->
            <div v-if="props.posts.total > 1" class="mt-8">
                <LaravelPaginationAdapter :total="props.posts.total" :perPage="props.posts.per_page" :currentPage="props.posts.current_page" />
            </div>
        </div>
    </AppLayout>
</template>
