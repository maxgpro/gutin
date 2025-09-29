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
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<BlogCategoriesCreateProps>();

const { t } = useI18n();
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('navigation.dashboard'),
        href: dashboard().url,
    },
    {
        title: t('blog.categories.title'),
        href: blog.categories.index().url,
    },
    {
        title: t('blog.categories.create'),
        href: blog.posts.create().url,
    },
]);

const form = useForm({
    title: '',
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
    <Head :title="t('blog.categories.create')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="folder-plus" class="h-5 w-5" />
                        {{ t('blog.categories.create') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Title -->
                        <div>
                            <Label for="title">{{ t('blog.categories.field_title') }} *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                :placeholder="t('blog.categories.title_placeholder')"
                                :class="['mt-1', { 'border-destructive': form.errors.title }]"
                                required
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <Label for="slug">{{ t('blog.categories.slug') }}</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                :placeholder="t('blog.categories.auto_from_name')"
                                :class="['mt-1', { 'border-destructive': form.errors.slug }]"
                            />
                            <p class="mt-1 text-sm text-muted-foreground">{{ t('blog.categories.leave_empty_to_autogenerate_from_name') }}</p>
                            <InputError :message="form.errors.slug" />
                        </div>

                        <!-- Description -->
                        <div>
                            <Label for="description">{{ t('blog.categories.description') }}</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :placeholder="t('blog.categories.description_placeholder')"
                                rows="3"
                                :class="['mt-1', { 'border-destructive': form.errors.description }]"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <!-- Color -->
                        <div>
                            <Label for="color">{{ t('blog.categories.color') }}</Label>
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
                            <Label for="is_active">{{ t('blog.categories.active') }}</Label>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between border-t pt-6">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="blog.categories.index().url">{{ t('common.cancel') }}</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Icon v-if="form.processing" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                                <Icon v-else name="save" class="mr-2 h-4 w-4" />
                                {{ form.processing ? t('common.creating') : t('blog.categories.create') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
