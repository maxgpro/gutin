<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogCategoriesIndexProps, BlogCategory } from '@/types/blog';
import { Head, Link, router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Blog Categories',
        href: blog.categories.index().url,
    },
];

const props = defineProps<BlogCategoriesIndexProps>();
const { canCreate } = props;

function deleteCategory(category: BlogCategory) {
    if ((category.posts_count || 0) > 0) {
        alert('Cannot delete category with existing posts.');
        return;
    }

    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        router.delete(blog.categories.destroy(category).url);
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <Button v-if="canCreate" as-child>
                    <Link :href="blog.categories.create().url">
                        <Icon name="plus" class="h-4 w-4" />
                        Category
                    </Link>
                </Button>
            </div>

            <div v-if="categories.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="category in categories" :key="category.id" class="transition-all hover:shadow-md">
                    <CardContent class="p-6">
                        <div class="mb-3 flex items-start justify-between">
                            <div class="mt-1 h-4 w-4 shrink-0 rounded-full" :style="{ backgroundColor: category.color }"></div>
                            <div class="text-sm text-muted-foreground">
                                {{ category.posts_count }} {{ category.posts_count === 1 ? 'post' : 'posts' }}
                            </div>
                        </div>

                        <h3 class="mb-2 text-xl font-semibold">
                            <!-- <TextLink :href="blog.categories.show(category)" class="hover:text-primary"> -->
                                {{ category.name }}
                            <!-- </TextLink> -->
                        </h3>

                        <p v-if="category.description" class="mb-4 text-muted-foreground">
                            {{ category.description }}
                        </p>

                        <div v-if="$page.props.auth?.user" class="flex gap-2">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="blog.categories.edit(category).url">
                                    <Icon name="edit" class="mr-1 h-3 w-3" />
                                    Edit
                                </Link>
                            </Button>
                            <Button @click="deleteCategory(category)" variant="outline" size="sm" :disabled="(category.posts_count || 0) > 0">
                                <Icon name="trash-2" class="mr-1 h-3 w-3" />
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="py-12 text-center">
                <Icon name="folder" class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <p class="text-lg text-muted-foreground">No categories found.</p>
                <Button v-if="$page.props.auth?.user" as-child class="mt-4">
                    <Link :href="blog.categories.create().url">Create Your First Category</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
