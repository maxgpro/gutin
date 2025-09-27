<script setup lang="ts">
import Icon from '@/components/Icon.vue';
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

const props = defineProps<BlogCategoriesEditProps>();
const { getLocalized } = useLocalizedField();
const localizedName = computed(() => getLocalized(props.category.name, undefined, ''));
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
    name: getLocalized(props.category.name, undefined, ''),
    slug: getLocalized(props.category.slug, undefined, ''),
    description: getLocalized(props.category.description, undefined, ''),
    color: props.category.color || '#3b82f6',
    is_active: props.category.is_active ?? true,
});

function submit() {
    form.put(blog.categories.update(props.category).url);
}

// When app locale changes, rehydrate form fields with that locale values
watch(locale, (newLocale) => {
    form.name = getLocalized(props.category.name, newLocale, '');
    form.slug = getLocalized(props.category.slug, newLocale, '');
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
                        <Icon name="edit-3" class="h-5 w-5" />
                        {{ t('blog.categories.edit') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <Label for="name">{{ t('blog.categories.name') }} *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                :placeholder="t('blog.categories.name_placeholder')"
                                :class="['mt-1', { 'border-destructive': form.errors.name }]"
                                required
                            />
                            <InputError :message="form.errors.name" />
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
                                {{ form.processing ? t('common.updating') : t('blog.common.update') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
