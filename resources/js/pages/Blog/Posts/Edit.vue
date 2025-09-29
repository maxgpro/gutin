<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import TiptapEditor from '@/components/ui/tiptap-editor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type BlogPostsEditProps } from '@/types/blog';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLocalizedField } from '@/composables/useTranslation';

const props = defineProps<BlogPostsEditProps>();
const { getLocalized } = useLocalizedField();
const { t, locale } = useI18n();
const localizedTitle = computed(() => getLocalized(props.post.title, undefined, ''));

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
        title: localizedTitle.value,
        href: blog.posts.show(props.post).url,
    },
]);

const form = useForm({
    title: getLocalized(props.post.title, undefined, ''),
    slug: getLocalized(props.post.base_slug, undefined, ''),
    excerpt: getLocalized(props.post.excerpt, undefined, ''),
    content: getLocalized(props.post.content, undefined, ''),
    blog_category_id: props.post.blog_category_id?.toString() || '',
    featured_image: props.post.featured_image || '',
    status: props.post.status || 'draft',
    published_at: props.post.published_at || '',
});

function submit() {
    form.put(blog.posts.update(props.post).url);
}

// When locale changes, rehydrate form fields with that locale values
watch(locale, (newLocale) => {
    form.title = getLocalized(props.post.title, newLocale, '');
    form.slug = getLocalized(props.post.base_slug, newLocale, '');
    form.excerpt = getLocalized(props.post.excerpt, newLocale, '');
    form.content = getLocalized(props.post.content, newLocale, '');
});
</script>

<template>
    <Head :title="`${t('blog.posts.edit')}: ${localizedTitle}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 md:col-span-2">
                        <h2 class="flex items-center gap-2">
                            <Icon name="edit" class="h-5 w-5" />
                            {{ t('blog.posts.edit') }}
                        </h2>
                        <!-- Title -->
                        <div>
                            <Label for="title">{{ t('blog.posts.title_field') }}</Label>
                            <Input id="title" v-model="form.title" type="text" class="mt-1" required />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <Label for="slug">{{ t('blog.posts.slug') }}</Label>
                            <Input id="slug" v-model="form.slug" type="text" class="mt-1" />
                            <p class="mt-1 text-sm text-muted-foreground">{{ t('blog.posts.leave_empty_to_autogenerate_from_title') }}</p>
                            <InputError class="mt-2" :message="form.errors.slug" />
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <Label for="excerpt">{{ t('blog.posts.excerpt') }}</Label>
                            <Textarea id="excerpt" v-model="form.excerpt" rows="3" class="mt-1" :placeholder="t('blog.posts.excerpt_placeholder')" />
                            <InputError class="mt-2" :message="form.errors.excerpt" />
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
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </div>

                                <!-- Published At (only show if published) -->
                                <div v-if="form.status === 'published'">
                                    <Label for="published_at">{{ t('blog.posts.published_at') }}</Label>
                                    <Input
                                        id="published_at"
                                        v-model="form.published_at"
                                        type="datetime-local"
                                        class="mt-1"
                                    />
                                    <InputError class="mt-2" :message="form.errors.published_at" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label for="category">{{ t('blog.posts.category') }}</Label>
                                    <Select v-model="form.blog_category_id">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue :placeholder="t('blog.posts.select_category')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in categories" :key="category.id" :value="String(category.id)">
                                                {{ getLocalized(category.title) }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="form.errors.blog_category_id" />
                                </div>

                                <!-- Featured Image -->
                                <div>
                                    <Label for="featured_image">{{ t('blog.posts.featured_image_url') }}</Label>
                                    <Input
                                        id="featured_image"
                                        v-model="form.featured_image"
                                        type="url"
                                        class="mt-1"
                                        placeholder="https://example.com/image.jpg"
                                    />
                                    <InputError class="mt-2" :message="form.errors.featured_image" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Content -->
                <div>
                    <Label for="content">{{ t('blog.posts.content') }}</Label>
                    <div class="mt-1">
                        <TiptapEditor
                            v-model="form.content"
                            :placeholder="t('blog.posts.content_placeholder')"
                            :class="{ 'border-destructive': form.errors.content }"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.content" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Button type="button" variant="outline" as-child>
                        <Link :href="blog.posts.show(post).url">{{ t('common.cancel') }}</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Icon v-if="form.processing" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                        <Icon v-else name="save" class="mr-2 h-4 w-4" />
                        {{ form.processing ? t('common.updating') : t('common.save') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
