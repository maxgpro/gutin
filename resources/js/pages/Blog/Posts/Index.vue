<script setup lang="ts">
import BlogPostCard from '@/components/BlogPostCard.vue';
import BlogPostFilters from '@/components/BlogPostFilters.vue';
import LaravelPaginationAdapter from '@/components/LaravelPaginationAdapter.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogPostsIndexProps } from '@/types/blog';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { FileText, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { t, locale } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('navigation.dashboard'),
        href: dashboard().url,
    },
    {
        title: t('blog.posts.title'),
        href: blog.posts.index().url,
    },
];

const props = defineProps<BlogPostsIndexProps>();
const { canCreate, canFilterByStatus } = props;

const form = ref<{
    search: string;
    category: number | null;
    status: string | null;
    sort_by: string;
    sort_order: string;
}>({
    search: props.filters.search || '',
    category: (props.filters as any).category_id ?? null,
    status: props.filters.status || null,
    sort_by: props.filters.sort_by || 'published_at',
    sort_order: props.filters.sort_order || 'desc',
});

// Normalize categories for filter component (ensure name uses current locale)
const categoriesForFilters = computed(() =>
    props.categories.map((c) => ({
        id: c.id,
        name: typeof c.name === 'string' ? c.name : (c.name as Record<string, string>)[locale.value] ?? '',
    })),
);

// Debounced single watcher to batch all filter changes (search/category/status/sort)
const scheduleUpdate = useDebounceFn(() => {
    updateFilters();
}, 250);

watch(
    form,
    () => {
        scheduleUpdate();
    },
    { deep: true },
);

// Computed proxies moved into BlogPostFilters component

function updateFilters() {
    const params = new URLSearchParams();

    if (form.value.search) {
        params.set('search', form.value.search);
    }

    // Добавляем параметр category_id только если выбрана конкретная категория
    if (form.value.category !== null) {
        params.set('category_id', String(form.value.category));
    }

    // Добавляем параметр status только если выбран конкретный статус
    if (form.value.status && form.value.status !== '_all') {
        params.set('status', form.value.status);
    }

    // Добавляем сортировку только если она отличается от дефолтной
    if (form.value.sort_by && form.value.sort_by !== 'published_at') {
        params.set('sort_by', form.value.sort_by);
    }

    if (form.value.sort_order && form.value.sort_order !== 'desc') {
        params.set('sort_order', form.value.sort_order);
    }

    // Сохраняем текущее значение per_page из URL
    const currentUrl = new URL(window.location.href);
    const currentPerPage = currentUrl.searchParams.get('per_page');
    if (currentPerPage) {
        params.set('per_page', currentPerPage);
    }

    const queryString = params.toString();
    const url = blog.posts.index().url + (queryString ? `?${queryString}` : '');

    // Skip navigation if URL hasn't changed to avoid redundant requests
    const target = new URL(url, window.location.origin);
    const current = new URL(window.location.href);
    if (target.pathname === current.pathname && target.search === current.search) {
        return;
    }

    router.get(url, {}, { preserveState: true, replace: true });
}

// Clear handled inside child component via v-model changes
</script>

<template>
    <Head :title="t('blog.posts.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <BlogPostFilters
                :key="locale"
                v-model="form"
                :categories="categoriesForFilters"
                :statuses="props.statuses"
                :canFilterByStatus="canFilterByStatus"
            >
                <template #actions>
                    <Button v-if="canCreate" as-child>
                        <Link :href="blog.posts.create().url">
                            <Plus :size="20" class="h-4 w-4" />
                            {{ t('blog.posts.create') }}
                        </Link>
                    </Button>
                </template>
            </BlogPostFilters>

            <!-- Posts Grid -->
            <div v-if="posts.data.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <BlogPostCard v-for="post in posts.data" :key="post.id" :post="post" />
            </div>

            <div v-else class="py-12 text-center">
                <FileText :size="20" class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <p class="text-lg text-muted-foreground">{{ t('blog.posts.no_posts') }}</p>
                <Button v-if="canCreate" as-child class="mt-4">
                    <Link :href="blog.posts.create().url">{{ t('blog.posts.create_first') }}</Link>
                </Button>
            </div>

            <!-- Pagination with Results Count -->
            <div v-if="props.posts.total > 6" class="mt-8">
                <LaravelPaginationAdapter
                    :total="props.posts.total"
                    :perPage="props.posts.per_page"
                    :currentPage="props.posts.current_page"
                    :from="props.posts.from"
                    :to="props.posts.to"
                    :showPerPageSelector="true"
                    :showResultsCount="true"
                />
            </div>
        </div>
    </AppLayout>
</template>
