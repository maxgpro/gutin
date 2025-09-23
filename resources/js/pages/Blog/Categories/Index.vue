<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

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

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    color: string;
    posts_count: number;
}

interface Props {
    categories: BlogCategory[];
}

defineProps<Props>();

function deleteCategory(category: BlogCategory) {
    if (category.posts_count > 0) {
        alert('Cannot delete category with existing posts.');
        return;
    }

    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        router.delete(blog.categories.destroy(category));
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <Button v-if="$page.props.auth?.user" :href="blog.categories.create()">
                    <Icon name="plus" class="mr-2 h-4 w-4" />
                    New Category
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
                            <TextLink :href="blog.categories.show(category)" class="hover:text-primary">
                                {{ category.name }}
                            </TextLink>
                        </h3>

                        <p v-if="category.description" class="mb-4 text-muted-foreground">
                            {{ category.description }}
                        </p>

                        <div v-if="$page.props.auth?.user" class="flex gap-2">
                            <Button :href="blog.categories.edit(category)" variant="outline" size="sm">
                                <Icon name="edit" class="mr-1 h-3 w-3" />
                                Edit
                            </Button>
                            <Button @click="deleteCategory(category)" variant="outline" size="sm" :disabled="category.posts_count > 0">
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
                <Button v-if="$page.props.auth?.user" :href="blog.categories.create()" class="mt-4"> Create Your First Category </Button>
            </div>
        </div>
    </AppLayout>
</template>
