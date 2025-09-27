<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import TiptapEditor from '@/components/ui/tiptap-editor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type BlogPostsCreateProps } from '@/types/blog';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('navigation.dashboard'),
        href: dashboard().url,
    },
    {
        title: t('blog.posts.title'),
        href: blog.posts.index().url,
    },
    {
        title: t('blog.posts.create'),
        href: blog.posts.create().url,
    },
]);

const props = defineProps<BlogPostsCreateProps>();

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    blog_category_id: '',
    featured_image: '',
    status: 'draft',
    published_at: '',
});

function submit() {
    form.post(blog.posts.store().url);
}
</script>

<template>
    <Head :title="t('blog.posts.create')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 md:col-span-2">
                        <h2 class="flex items-center gap-2">
                            <Icon name="edit" class="h-5 w-5" />
                            {{ t('blog.posts.create') }}
                        </h2>
                        <!-- Title -->
                        <div>
                            <Label for="title">{{ t('blog.posts.title_field') }} *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                :placeholder="t('blog.posts.title_field')"
                                :class="['mt-1', { 'border-destructive': form.errors.title }]"
                                required
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <Label for="slug">{{ t('blog.posts.slug') }}</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                :placeholder="t('blog.posts.leave_empty_to_autogenerate_from_title')"
                                :class="['mt-1', { 'border-destructive': form.errors.slug }]"
                            />
                            <p class="mt-1 text-sm text-muted-foreground">{{ t('blog.posts.leave_empty_to_autogenerate_from_title') }}</p>
                            <InputError :message="form.errors.slug" />
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <Label for="excerpt">{{ t('blog.posts.excerpt') }}</Label>
                            <Textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                :placeholder="t('blog.posts.excerpt_placeholder')"
                                rows="3"
                                :class="['mt-1', { 'border-destructive': form.errors.excerpt }]"
                            />
                            <InputError :message="form.errors.excerpt" />
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('blog.posts.publish_settings') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Status -->
                                <div>
                                    <Label for="status">{{ t('blog.posts.status') }}</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">{{ t('blog.posts.status_draft') }}</SelectItem>
                                            <SelectItem value="published">{{ t('blog.posts.status_published') }}</SelectItem>
                                            <SelectItem value="archived">{{ t('blog.posts.status_archived') }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.status" />
                                </div>

                                <!-- Published At (only show if published) -->
                                <div v-if="form.status === 'published'">
                                    <Label for="published_at">{{ t('blog.posts.published_at') }}</Label>
                                    <Input
                                        id="published_at"
                                        v-model="form.published_at"
                                        type="datetime-local"
                                        :class="['mt-1', { 'border-destructive': form.errors.published_at }]"
                                    />
                                    <InputError :message="form.errors.published_at" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label for="blog_category_id">{{ t('blog.posts.category') }} *</Label>
                                    <Select v-model="form.blog_category_id">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue :placeholder="t('blog.posts.select_category')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in props.categories" :key="category.id" :value="category.id.toString()">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.blog_category_id" />
                                </div>

                                <div>
                                    <Label for="featured_image">{{ t('blog.posts.featured_image_url') }}</Label>
                                    <Input
                                        id="featured_image"
                                        v-model="form.featured_image"
                                        placeholder="https://example.com/image.jpg"
                                        :class="['mt-1', { 'border-destructive': form.errors.featured_image }]"
                                    />
                                    <InputError :message="form.errors.featured_image" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div>
                    <Label for="content">{{ t('blog.posts.content') }} *</Label>
                    <div class="mt-1">
                        <TiptapEditor
                            v-model="form.content"
                            :placeholder="t('blog.posts.content_placeholder')"
                            :class="{ 'border-destructive': form.errors.content }"
                        />
                    </div>
                    <InputError :message="form.errors.content" />
                </div>

                <!-- Actions -->
                <div class="flex justify-between">
                    <Button type="button" variant="outline" as-child>
                        <Link :href="blog.posts.index().url">{{ t('common.cancel') }}</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Icon v-if="form.processing" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                        <Icon v-else name="save" class="mr-2 h-4 w-4" />
                        {{ form.processing ? t('common.creating') : t('common.create') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
