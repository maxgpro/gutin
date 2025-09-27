<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowDownAZ, ArrowDownUp, ArrowUpZA, Calendar, ListFilter, Search, Tag, X } from 'lucide-vue-next';
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

type Filters = {
    search: string;
    category: number | null;
    status: string | null;
    sort_by: string; // 'published_at' | 'created_at'
    sort_order: string; // 'asc' | 'desc'
};

type CategoryOption = { id: number | string; name: string };

const props = defineProps<{
    /** v-model binding for filters */
    modelValue: Filters;
    categories: CategoryOption[];
    statuses: string[];
    canFilterByStatus: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Filters): void;
}>();

const { t, locale } = useI18n();

// Using t() directly in the template ensures labels react to locale changes without extra dependencies

// Local copy to avoid mutating props directly
const local = reactive<Filters>({ ...props.modelValue });

// Sync-down when parent changes
watch(
    () => props.modelValue,
    (val) => {
        Object.assign(local, val);
    },
    { deep: true },
);

// Emit up when local changes
watch(
    () => local,
    () => {
        emit('update:modelValue', { ...local });
    },
    { deep: true },
);

// Category proxy: null <-> '_all' and id <-> string
const categoryModel = computed<string>({
    get: () => (local.category == null ? '_all' : String(local.category)),
    set: (v: string) => (local.category = v === '_all' ? null : Number(v)),
});

// Status proxy: null <-> '_all'
const statusModel = computed<string>({
    get: () => local.status ?? '_all',
    set: (v: string) => (local.status = v === '_all' ? null : v),
});

// Sort proxies
const sortByModel = computed<string>({
    get: () => local.sort_by,
    set: (v: string) => (local.sort_by = v),
});

const sortOrderModel = computed<string>({
    get: () => local.sort_order,
    set: (v: string) => (local.sort_order = v),
});

// Active filters flag (for Clear button disabled state)
const hasActiveFilters = computed<boolean>(() => {
    return (
        !!local.search ||
        local.category !== null ||
        (props.canFilterByStatus && !!local.status) ||
        local.sort_by !== 'published_at' ||
        local.sort_order !== 'desc'
    );
});

function clearFilters() {
    Object.assign(local, {
        search: '',
        category: null,
        status: null,
        sort_by: 'published_at',
        sort_order: 'desc',
    } satisfies Filters);
}
</script>

<template>
    <div class="mb-6 space-y-4">
        <div class="sm:flex-start flex flex-col gap-2 sm:flex-row sm:flex-wrap" :key="locale">
            <!-- External actions (e.g., Create button) -->
            <slot name="actions" />
            <!-- Search -->
            <div class="relative w-full">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="local.search" :placeholder="t('blog.posts.filters.search_placeholder')" class="min-w-md pl-10" />
            </div>

            <!-- Category Filter -->
            <Select v-model="categoryModel">
                <SelectTrigger class="w-full whitespace-nowrap sm:w-min">
                    <div class="flex items-center gap-2">
                        <Tag class="h-4 w-4 text-muted-foreground" />
                        <SelectValue :placeholder="t('blog.posts.filters.all_categories')" />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="_all">
                        <div class="flex items-center gap-2">{{ t('blog.posts.filters.all_categories') }}</div>
                    </SelectItem>
                    <SelectItem v-for="category in categories" :key="category.id" :value="String(category.id)">
                        <div class="flex items-center gap-2">{{ category.name }}</div>
                    </SelectItem>
                </SelectContent>
            </Select>

            <!-- Status Filter (admins only) -->
            <Select v-if="canFilterByStatus" v-model="statusModel">
                <SelectTrigger class="w-full whitespace-nowrap sm:w-min">
                    <div class="flex items-center gap-2">
                        <ListFilter class="h-4 w-4 text-muted-foreground" />
                        <SelectValue :placeholder="t('blog.posts.filters.all_statuses')" />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="_all">
                        <div class="flex items-center gap-2">{{ t('blog.posts.filters.all_statuses') }}</div>
                    </SelectItem>
                    <SelectItem v-for="status in statuses" :key="status" :value="status">
                        <div class="flex items-center gap-2">
                            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                        </div>
                    </SelectItem>
                </SelectContent>
            </Select>

            <!-- Sort By -->
            <Select v-model="sortByModel">
                <SelectTrigger class="w-full whitespace-nowrap sm:w-min">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <SelectValue />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="published_at">
                        <div class="flex items-center gap-2">{{ t('blog.posts.filters.sort.published_date') }}</div>
                    </SelectItem>
                    <SelectItem value="created_at">
                        <div class="flex items-center gap-2">{{ t('blog.posts.filters.sort.created_date') }}</div>
                    </SelectItem>
                </SelectContent>
            </Select>

            <!-- Sort Order -->
            <Select v-model="sortOrderModel">
                <SelectTrigger class="w-full whitespace-nowrap sm:w-min">
                    <div class="flex items-center gap-2">
                        <ArrowDownUp />
                        <SelectValue />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="desc">
                        <div class="flex items-center gap-2">
                            <ArrowDownAZ class="h-4 w-4" />
                            {{ t('blog.posts.filters.sort_order.newest') }}
                        </div>
                    </SelectItem>
                    <SelectItem value="asc">
                        <div class="flex items-center gap-2">
                            <ArrowUpZA class="h-4 w-4" />
                            {{ t('blog.posts.filters.sort_order.oldest') }}
                        </div>
                    </SelectItem>
                </SelectContent>
            </Select>

            <!-- Clear Filters Button -->
            <Button variant="outline" @click="clearFilters" :disabled="!hasActiveFilters" class="w-full whitespace-nowrap sm:w-min">
                <X :size="20" class="h-4 w-4" />
                {{ t('blog.posts.filters.clear') }}
            </Button>
        </div>
    </div>
</template>

<style scoped></style>
