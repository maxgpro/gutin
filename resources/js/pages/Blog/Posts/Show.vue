<script setup lang="ts">
import BlogPostCard from '@/components/BlogPostCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type BlogPostsShowProps } from '@/types/blog';
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, Eye, SquarePen, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLocalizedField, useLocalizedDate } from '@/composables/useTranslation';

// Composables  
const { t } = useI18n();
const { getLocalized } = useLocalizedField();
const { formatDate } = useLocalizedDate();

const props = defineProps<BlogPostsShowProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('navigation.dashboard'),
        href: dashboard().url,
    },
    {
        title: t('blog.posts.title'),
        href: blog.posts.index().url,
    },
    {
        title: getLocalized(props.post.category.title),
        href: blog.categories.show(props.post.category).url,
    },
];

const canEdit = computed(() => {
    return props.canEdit;
});

const canDelete = computed(() => {
    return props.canDelete;
});

function deletePost() {
    const postTitle = getLocalized(props.post.title);
    if (confirm(t('messages.confirm_delete') + ` "${postTitle}"?`)) {
        router.delete(blog.posts.destroy(props.post).url);
    }
}
</script>

<template>
    <Head :title="getLocalized(post.title)" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Article Header -->
            <header class="mb-8">
                <div class="mb-4">
                    <Badge variant="secondary" :style="{ backgroundColor: post.category.color + '20', color: post.category.color }">
                        {{ getLocalized(post.category.title) }}
                    </Badge>
                </div>

                <h1 class="mb-4 text-4xl font-bold">{{ getLocalized(post.title) }}</h1>

                <div class="flex items-center justify-between border-b pb-6 text-sm text-muted-foreground">
                    <div class="flex items-center gap-4">
                        <span>By {{ post.user.name }}</span>
                        <span>{{ formatDate(post.published_at ?? '') }}</span>
                        <span class="flex items-center gap-1">
                            <Clock class="h-4 w-4" />
                            {{ post.reading_time }} {{ t('blog.posts.reading_time') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1">
                            <Eye class="h-4 w-4" />
                            {{ post.views_count }} {{ t('blog.posts.views') }}
                        </span>

                        <!-- Edit button for author/admin -->
                        <Button v-if="canEdit" variant="outline" as-child size="sm">
                            <Link :href="blog.posts.edit(post).url">
                                <SquarePen :size="20" stroke-width="1.5" class="mr-2" />
                                {{ t('common.edit') }}
                            </Link>
                        </Button>

                        <!-- Delete button for author/admin -->
                        <Button v-if="canDelete" variant="destructive" size="sm" @click="deletePost">
                            <Trash2 :size="20" stroke-width="1.5" class="mr-2" />
                            {{ t('common.delete') }}
                        </Button>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div v-if="post.featured_image" class="mb-8">
                <img :src="post.featured_image" :alt="getLocalized(post.title)" class="h-64 w-full rounded-lg object-cover sm:h-96" />
            </div>

            <!-- Article Content -->
            <article
                class="prose prose-lg prose-headings:text-foreground prose-p:text-foreground prose-li:text-foreground prose-strong:text-foreground mb-12 max-w-none"
            >
                <div v-html="getLocalized(post.content)" class="tiptap-content"></div>
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

/* 
 * Стили для отображения контента Tiptap вынесены в /resources/css/tiptap.css
 * Все стили теперь централизованы и применяются через класс .tiptap-content
 */
</style>
