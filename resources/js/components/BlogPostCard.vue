<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import TextLink from '@/components/TextLink.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import blog from '@/routes/blog';


interface BlogPost {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    featured_image: string | null;
    published_at: string | null;
    views_count: number;
    reading_time: number;
    user: {
        id: number;
        name: string;
    };
    category: {
        id: number;
        name: string;
        slug: string;
        color: string;
    };
}

interface Props {
    post: BlogPost;
}

defineProps<Props>();

function formatDate(dateString: string | null): string {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<template>
    <Card class="flex h-full flex-col overflow-hidden">
        <div v-if="post.featured_image" class="relative h-48 overflow-hidden">
            <img :src="post.featured_image" :alt="post.title" class="h-full w-full object-cover transition-transform hover:scale-105" />
            <div
                class="absolute top-3 left-3 rounded-full px-2 py-1 text-xs font-medium text-white"
                :style="{ backgroundColor: post.category.color }"
            >
                {{ post.category.name }}
            </div>
        </div>

        <CardContent class="flex-1 p-6">
            <div v-if="!post.featured_image" class="mb-3">
                <Badge variant="secondary" :style="{ backgroundColor: post.category.color + '20', color: post.category.color }">
                    {{ post.category.name }}
                </Badge>
            </div>

            <h3 class="mb-2 line-clamp-2 text-xl font-semibold">
                <TextLink :href="blog.posts.show(post)" class="hover:text-primary">
                    {{ post.title }}
                </TextLink>
            </h3>

            <p v-if="post.excerpt" class="mb-4 line-clamp-3 text-muted-foreground">
                {{ post.excerpt }}
            </p>
        </CardContent>

        <CardFooter>
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <div class="flex items-center gap-4">
                    <span>{{ post.user.name }}</span>
                    <span>{{ formatDate(post.published_at) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <Icon name="clock" class="h-3 w-3" />
                        {{ post.reading_time }}min
                    </span>
                    <span class="flex items-center gap-1">
                        <Icon name="eye" class="h-3 w-3" />
                        {{ post.views_count }}
                    </span>
                </div>
            </div>
        </CardFooter>
    </Card>
</template>
