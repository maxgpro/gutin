<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogCategoriesCreateProps } from '@/types/blog';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<BlogCategoriesCreateProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Blog Categories',
        href: blog.categories.index().url,
    },
    {
        title: 'Create Category',
        href: blog.posts.create().url,
    },
];

const form = useForm({
    name: '',
    slug: '',
    description: '',
    color: '#3b82f6',
    is_active: true,
});

function submit() {
    form.post(blog.categories.store().url);
}
</script>

<template>
    <Head title="Create Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="folder-plus" class="h-5 w-5" />
                        Create New Category
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <Label for="name">Category Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Enter category name..."
                                :class="['mt-1', { 'border-destructive': form.errors.name }]"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <Label for="slug">Slug</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                placeholder="Auto-generated from name"
                                :class="['mt-1', { 'border-destructive': form.errors.slug }]"
                            />
                            <p class="mt-1 text-sm text-muted-foreground">Leave empty to auto-generate from name</p>
                            <InputError :message="form.errors.slug" />
                        </div>

                        <!-- Description -->
                        <div>
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Brief description of the category..."
                                rows="3"
                                :class="['mt-1', { 'border-destructive': form.errors.description }]"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <!-- Color -->
                        <div>
                            <Label for="color">Category Color</Label>
                            <div class="mt-1 flex items-center gap-3">
                                <Input
                                    id="color"
                                    v-model="form.color"
                                    type="color"
                                    :class="['h-10 w-20 rounded border p-1', { 'border-destructive': form.errors.color }]"
                                />
                                <Input
                                    v-model="form.color"
                                    type="text"
                                    placeholder="#3b82f6"
                                    :class="['flex-1', { 'border-destructive': form.errors.color }]"
                                />
                            </div>
                            <InputError :message="form.errors.color" />
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model="form.is_active" />
                            <Label for="is_active">Active Category</Label>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between border-t pt-6">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="blog.categories.index().url">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Icon v-if="form.processing" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                                <Icon v-else name="save" class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Creating...' : 'Create Category' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
