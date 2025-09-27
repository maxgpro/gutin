<script setup lang="ts">
import BlogPostCard from '@/components/BlogPostCard.vue';
import LaravelPaginationAdapter from '@/components/LaravelPaginationAdapter.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogPostsIndexProps } from '@/types/blog';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ArrowDownAZ, ArrowDownUp, ArrowUpZA, Calendar, ListFilter, Search, Tag, FileText, X, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { t } = useI18n();

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
    category: string | null;
    status: string | null;
    sort_by: string;
    sort_order: string;
}>({
    search: props.filters.search || '',
    category: props.filters.category || null,
    status: props.filters.status || null,
    sort_by: props.filters.sort_by || 'published_at',
    sort_order: props.filters.sort_order || 'desc',
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

watch(
    () => form.value.status,
    () => {
        updateFilters();
    },
);

watch(
    () => form.value.sort_by,
    () => {
        updateFilters();
    },
);

watch(
    () => form.value.sort_order,
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

// Фильтры Статусов
const statusModel = computed({
    get: () => form.value.status ?? '_all',
    set: (v: string) => (form.value.status = v === '_all' ? null : v),
});

// Сортировка
const sortByModel = computed({
    get: () => form.value.sort_by,
    set: (v: string) => (form.value.sort_by = v),
});

const sortOrderModel = computed({
    get: () => form.value.sort_order,
    set: (v: string) => (form.value.sort_order = v),
});

function updateFilters() {
    const params = new URLSearchParams();

    if (form.value.search) {
        params.set('search', form.value.search);
    }

    // Добавляем параметр category только если выбрана конкретная категория
    if (form.value.category && form.value.category !== '_all') {
        params.set('category', form.value.category);
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
    router.get(url, {}, { preserveState: true, replace: true });
}

// Проверка наличия активных фильтров
const hasActiveFilters = computed(() => {
    return (
        form.value.search ||
        (form.value.category && form.value.category !== '_all') ||
        (canFilterByStatus && form.value.status && form.value.status !== '_all') ||
        form.value.sort_by !== 'published_at' ||
        form.value.sort_order !== 'desc'
    );
});

// Очистка всех фильтров
function clearFilters() {
    form.value.search = '';
    form.value.category = null;
    form.value.status = null; // Можно безопасно очищать всегда
    form.value.sort_by = 'published_at';
    form.value.sort_order = 'desc';
    updateFilters();
}
</script>

<template>
    <Head title="Blog Posts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Filters and Search -->
            <div class="mb-6 space-y-4">
                <!-- Second row: Filters and Sorting -->
                <div class="flex gap-2 flex-col sm:flex-row sm:flex-wrap sm:flex-start">
                    <!-- Top row: Create button and Search -->
                    <div class="relative w-full">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="form.search" placeholder="Search posts..." class="min-w-md pl-10" @input="debouncedSearch" />
                    </div>
                    <Button v-if="canCreate" as-child>
                        <Link :href="blog.posts.create().url">
                            <Plus :size="20" class="h-4 w-4" />
                            Post
                        </Link>
                    </Button>

                    <!-- Filters -->
                    <Select v-model="categoryModel">
                        <SelectTrigger class="w-full sm:w-48">
                            <div class="flex items-center gap-2">
                                <Tag class="h-4 w-4 text-muted-foreground" />
                                <SelectValue placeholder="All Categories" />
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all">
                                <div class="flex items-center gap-2">All Categories</div>
                            </SelectItem>
                            <SelectItem v-for="category in props.categories" :key="category.id" :value="category.slug">
                                <div class="flex items-center gap-2">
                                    {{ category.name }}
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-if="canFilterByStatus" v-model="statusModel">
                        <SelectTrigger class="w-full sm:w-40">
                            <div class="flex items-center gap-2">
                                <ListFilter class="h-4 w-4 text-muted-foreground" />
                                <SelectValue placeholder="All Statuses" />
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all">
                                <div class="flex items-center gap-2">All Statuses</div>
                            </SelectItem>
                            <SelectItem v-for="status in props.statuses" :key="status" :value="status">
                                <div class="flex items-center gap-2">
                                    {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Sorting -->
                    <Select v-model="sortByModel">
                        <SelectTrigger class="w-full sm:w-40">
                            <div class="flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <SelectValue />
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="published_at">
                                <div class="flex items-center gap-2">Published Date</div>
                            </SelectItem>
                            <SelectItem value="created_at">
                                <div class="flex items-center gap-2">Created Date</div>
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="sortOrderModel">
                        <SelectTrigger class="w-full sm:w-32">
                            <div class="flex items-center gap-2">
                                <ArrowDownUp />
                                <SelectValue />
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="desc">
                                <div class="flex items-center gap-2">
                                    <ArrowDownAZ class="h-4 w-4" />
                                    Newest
                                </div>
                            </SelectItem>
                            <SelectItem value="asc">
                                <div class="flex items-center gap-2">
                                    <ArrowUpZA class="h-4 w-4" />
                                    Oldest
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Clear Filters Button -->
                    <Button
                        variant="outline"
                        @click="clearFilters"
                        :disabled="!hasActiveFilters"
                        :class="{
                            'w-full sm:w-auto': true,
                        }"
                    >
                        <X :size="20" class="h-4 w-4" />
                        Clear Filters
                    </Button>
                </div>
            </div>

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
