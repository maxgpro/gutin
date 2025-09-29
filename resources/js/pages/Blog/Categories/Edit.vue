<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useLocalizedField } from '@/composables/useTranslation';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import blog from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import type { BlogCategoriesEditProps } from '@/types/blog';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Edit, Loader2, Save as IconSave } from 'lucide-vue-next';

const props = defineProps<BlogCategoriesEditProps>();
const { getLocalized } = useLocalizedField();
const localizedName = computed(() => getLocalized(props.category.title, undefined, ''));
const { t, locale } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('navigation.dashboard'),
        href: dashboard().url,
    },
    {
        title: t('navigation.categories'),
        href: blog.categories.index().url,
    },
    {
        title: localizedName.value,
        href: blog.categories.show(props.category).url,
    },
]);

const form = useForm({
    title: getLocalized(props.category.title, undefined, ''),
    slug: getLocalized(props.category.base_slug, undefined, ''),
    description: getLocalized(props.category.description, undefined, ''),
    color: props.category.color || '#3b82f6',
    is_active: props.category.is_active ?? true,
});

function submit() {
    form.put(blog.categories.update(props.category).url);
}

function saveOnly() {
    form.transform((data) => ({ ...data, stay: true }))
        .put(blog.categories.update(props.category).url, {
            onFinish: () => form.transform((data) => data),
            preserveScroll: true,
        });
}

// When app locale changes, rehydrate form fields with that locale values
watch(locale, (newLocale) => {
    form.title = getLocalized(props.category.title, newLocale, '');
    form.slug = getLocalized(props.category.base_slug, newLocale, '');
    form.description = getLocalized(props.category.description, newLocale, '');
});
</script>

<template>
    <Head :title="`${t('blog.categories.edit')}: ${localizedName}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Edit :size="16" class="h-5 w-5" />
                        {{ t('blog.categories.edit') }}
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
                            <div class="flex items-center gap-2">
                                <Button type="button" variant="secondary" :disabled="form.processing" @click="saveOnly">
                                    <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                    <IconSave v-else :size="16" class="mr-2 h-4 w-4" />
                                    {{ t('common.save') }}
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                    <IconSave v-else :size="16" class="mr-2 h-4 w-4" />
                                    {{ t('common.save_and_close') }}
                                </Button>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
