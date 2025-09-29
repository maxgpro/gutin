<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import blog from '@/routes/blog';
import { Clock, Eye } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useLocalizedField, useLocalizedDate } from '@/composables/useTranslation';
import type { BlogPost } from '@/types/blog';

// Composables
const { t } = useI18n();
const { getLocalized } = useLocalizedField();
const { formatDate } = useLocalizedDate();

interface Props {
    post: BlogPost;
}

defineProps<Props>();

function getStatusVariant(status: string): "default" | "secondary" | "destructive" | "outline" {
    switch (status) {
        case 'published':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'archived':
            return 'outline';
        default:
            return 'secondary';
    }
}

function getStatusLabel(status: string): string {
    return t(`blog.posts.status_${status}`);
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<template>
    <Card class="flex h-full flex-col overflow-hidden">
        <div v-if="post.featured_image" class="relative h-48 overflow-hidden">
            <img :src="post.featured_image" :alt="getLocalized(post.title)" class="h-full w-full object-cover transition-transform hover:scale-105" />
            <div class="absolute top-3 left-3 flex gap-2">
                <div
                    class="rounded-full px-2 py-1 text-xs font-medium text-white"
                    :style="{ backgroundColor: post.category.color }"
                >
                    {{ getLocalized(post.category.title) }}
                </div>
                <Badge 
                    v-if="post.status !== 'published'"
                    :variant="getStatusVariant(post.status)"
                    class="text-xs"
                >
                    {{ getStatusLabel(post.status) }}
                </Badge>
            </div>
        </div>

        <CardContent class="flex-1 p-6">
            <div v-if="!post.featured_image" class="mb-3 flex gap-2">
                <Badge variant="secondary" :style="{ backgroundColor: post.category.color + '20', color: post.category.color }">
                    {{ getLocalized(post.category.title) }}
                </Badge>
                <Badge 
                    v-if="post.status !== 'published'"
                    :variant="getStatusVariant(post.status)"
                >
                    {{ getStatusLabel(post.status) }}
                </Badge>
            </div>

            <h3 class="mb-2 line-clamp-2 text-xl font-semibold">
                <TextLink :href="blog.posts.show(post)" class="hover:text-primary">
                    {{ getLocalized(post.title) }}
                </TextLink>
            </h3>

            <p v-if="getLocalized(post.excerpt)" class="mb-4 line-clamp-3 text-muted-foreground">
                {{ getLocalized(post.excerpt) }}
            </p>
        </CardContent>

        <CardFooter>
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <div class="flex items-center gap-4">
                    <span>{{ post.user.name }}</span>
                    <span>{{ formatDate((post.published_at ?? ''), { month: 'short' }) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <Clock class="h-3 w-3 ml-2" />
                        {{ post.reading_time }} {{ t('blog.posts.minutes') }}
                    </span>
                    <span class="flex items-center gap-1">
                        <Eye class="h-3 w-3" />
                        {{ post.views_count }}
                    </span>
                </div>
            </div>
        </CardFooter>
    </Card>
</template>
