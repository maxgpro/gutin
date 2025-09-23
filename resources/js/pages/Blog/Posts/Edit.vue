<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type BlogPostsEditProps } from '@/types/blog';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<BlogPostsEditProps>();

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
        title: props.post.title,
        href: blog.posts.show(props.post).url,
    },
];

const form = useForm({
    title: props.post.title || '',
    slug: props.post.slug || '',
    excerpt: props.post.excerpt || '',
    content: props.post.content || '',
    blog_category_id: props.post.blog_category_id?.toString() || '',
    featured_image: props.post.featured_image || '',
    status: props.post.status || 'draft',
    published_at: props.post.published_at || '',
});

function submit() {
    form.put(blog.posts.update(props.post).url);
}
</script>

<template>
    <Head :title="`Edit: ${post.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 md:col-span-2">
                        <h2 class="flex items-center gap-2">
                            <Icon name="edit" class="h-5 w-5" />
                            Edit Blog Post
                        </h2>
                        <!-- Title -->
                        <div>
                            <Label for="title">Title</Label>
                            <Input id="title" v-model="form.title" type="text" class="mt-1" required />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" type="text" class="mt-1" />
                            <p class="mt-1 text-sm text-muted-foreground">Leave empty to auto-generate from title</p>
                            <InputError class="mt-2" :message="form.errors.slug" />
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea id="excerpt" v-model="form.excerpt" rows="3" class="mt-1" placeholder="Brief description of the post..." />
                            <InputError class="mt-2" :message="form.errors.excerpt" />
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Publish Settings</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Status -->
                                <div>
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">Draft</SelectItem>
                                            <SelectItem value="published">Published</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </div>

                                <!-- Published At (only show if published) -->
                                <div v-if="form.status === 'published'">
                                    <Label for="published_at">Published At</Label>
                                    <Input id="published_at" v-model="form.published_at" type="datetime-local" class="mt-1" />
                                    <InputError class="mt-2" :message="form.errors.published_at" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label for="category">Category</Label>
                                    <Select v-model="form.blog_category_id">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in categories" :key="category.id" :value="String(category.id)">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-2" :message="form.errors.blog_category_id" />
                                </div>

                                <!-- Featured Image -->
                                <div>
                                    <Label for="featured_image">Featured Image URL</Label>
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
                    <Label for="content">Content</Label>
                    <Textarea
                        id="content"
                        v-model="form.content"
                        rows="12"
                        class="mt-1"
                        placeholder="Write your blog post content here..."
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.content" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Button type="button" variant="outline" as-child>
                        <Link :href="blog.posts.show(post).url">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Icon v-if="form.processing" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                        <Icon v-else name="save" class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Updating...' : 'Update Post' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
