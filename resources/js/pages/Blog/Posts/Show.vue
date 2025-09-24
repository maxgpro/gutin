<script setup lang="ts">
import BlogPostCard from '@/components/BlogPostCard.vue';
import Icon from '@/components/Icon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type BlogPostsShowProps } from '@/types/blog';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<BlogPostsShowProps>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Blog Posts',
        href: blog.posts.index().url,
    },
    {
        title: props.post.category.name,
        href: blog.categories.show(props.post.category).url,
    },
];

const canEdit = computed(() => {
    return props.canEdit;
});

function formatDate(dateString: string | null): string {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="post.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Article Header -->
            <header class="mb-8">
                <div class="mb-4">
                    <Badge variant="secondary" :style="{ backgroundColor: post.category.color + '20', color: post.category.color }">
                        {{ post.category.name }}
                    </Badge>
                </div>

                <h1 class="mb-4 text-4xl font-bold">{{ post.title }}</h1>

                <div class="flex items-center justify-between border-b pb-6 text-sm text-muted-foreground">
                    <div class="flex items-center gap-4">
                        <span>By {{ post.user.name }}</span>
                        <span>{{ formatDate(post.published_at) }}</span>
                        <span class="flex items-center gap-1">
                            <Icon name="clock" class="h-4 w-4" />
                            {{ post.reading_time }} min read
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1">
                            <Icon name="eye" class="h-4 w-4" />
                            {{ post.views_count }} views
                        </span>

                        <!-- Edit button for author -->
                        <Button v-if="canEdit" variant="outline" as-child size="sm">
                            <Link :href="blog.posts.edit(post).url">
                                <Icon name="edit" class="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div v-if="post.featured_image" class="mb-8">
                <img :src="post.featured_image" :alt="post.title" class="h-64 w-full rounded-lg object-cover sm:h-96" />
            </div>

            <!-- Article Content -->
            <article class="prose prose-lg mb-12 max-w-none">
                <div v-html="post.content"></div>
            </article>

            <!-- Related Posts -->
            <div v-if="relatedPosts.length > 0" class="border-t pt-8">
                <h3 class="mb-6 text-2xl font-semibold">Related Posts</h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <BlogPostCard v-for="relatedPost in relatedPosts" :key="relatedPost.id" :post="relatedPost" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Custom styles for the article content */
.prose {
    color: var(--color-foreground);
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    color: var(--color-foreground);
}

.prose a {
    color: var(--color-primary);
    &:hover {
        opacity: 0.8;
    }
}

.prose blockquote {
    border-left-color: var(--color-primary);
    color: var(--color-muted-foreground);
}

.prose code {
    background-color: var(--color-muted);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.prose pre {
    background-color: var(--color-muted);
}
</style>
