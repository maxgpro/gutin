<script setup lang="ts">

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useLocalizedField } from '@/composables/useTranslation';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogCategoriesIndexProps, BlogCategory } from '@/types/blog';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Edit, Folder, Plus, Trash2 } from 'lucide-vue-next';


// Composables
const { t } = useI18n();
const { getLocalized } = useLocalizedField();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('navigation.dashboard'),
        href: dashboard().url,
    },
    {
        title: t('blog.categories.title'),
        href: blog.categories.index().url,
    },
];

const props = defineProps<BlogCategoriesIndexProps>();
const { canCreate } = props;

function deleteCategory(category: BlogCategory) {
    if ((category.posts_count || 0) > 0) {
        alert(t('blog.categories.cannot_delete_with_posts'));
        return;
    }

    const categoryName = getLocalized(category.name);
    if (confirm(t('messages.confirm_delete') + ` "${categoryName}"?`)) {
        router.delete(blog.categories.destroy(category).url);
    }
}
</script>

<template>
    <Head :title="t('blog.categories.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold tracking-tight">{{ t('blog.categories.title') }}</h1>
                <Button v-if="canCreate" as-child>
                    <Link :href="blog.categories.create().url">
                        <Plus class="h-4 w-4" />
                        {{ t('blog.categories.create') }}
                    </Link>
                </Button>
            </div>

            <div v-if="categories.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="category in categories" :key="category.id" class="transition-all hover:shadow-md">
                    <CardContent class="p-6">
                        <div class="mb-3 flex items-start justify-between">
                            <div class="mt-1 h-4 w-4 shrink-0 rounded-full" :style="{ backgroundColor: category.color }"></div>
                            <div class="text-sm text-muted-foreground">
                                {{ t('blog.categories.posts_count', { count: category.posts_count || 0 }, category.posts_count || 0) }}
                            </div>
                        </div>

                        <h3 class="mb-2 text-xl font-semibold">
                            <!-- <TextLink :href="blog.categories.show(category)" class="hover:text-primary"> -->
                            {{ getLocalized(category.name) }}
                            <!-- </TextLink> -->
                        </h3>

                        <p v-if="getLocalized(category.description)" class="mb-4 text-muted-foreground">
                            {{ getLocalized(category.description) }}
                        </p>

                        <div v-if="$page.props.auth?.user" class="flex gap-2">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="blog.categories.edit(category).url">
                                    <Edit :size="16" />
                                    {{ t('common.edit') }}
                                </Link>
                            </Button>
                            <Button @click="deleteCategory(category)" variant="outline" size="sm" :disabled="(category.posts_count || 0) > 0">
                                <Trash2 :size="16" />
                                {{ t('common.delete') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="py-12 text-center">
                <Folder :size="60" class="mx-auto mb-4 text-muted-foreground" />
                <p class="text-lg text-muted-foreground">{{ t('blog.categories.no_categories') }}</p>
                <Button v-if="$page.props.auth?.user" as-child class="mt-4">
                    <Link :href="blog.categories.create().url">{{ t('blog.categories.create') }}</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
