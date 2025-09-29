<template>
    <div class="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <!-- Header with Language Switcher -->
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ t('blog.categories.title') }}
                </h1>
                <LanguageSwitcher />
            </div>

            <!-- Demo Content -->
            <div class="space-y-6">
                <!-- Basic Translations -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ t('common.navigation') }}
                    </h2>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="text-center">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ t('common.save') }}</div>
                            <div class="text-lg font-medium">{{ t('common.edit') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ t('common.delete') }}</div>
                            <div class="text-lg font-medium">{{ t('common.cancel') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ t('common.create') }}</div>
                            <div class="text-lg font-medium">{{ t('common.search') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ t('common.loading') }}</div>
                            <div class="text-lg font-medium">{{ t('common.submit') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Sample Multilingual Content -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ t('blog.posts.title') }}
                    </h2>

                    <!-- Mock Category Card -->
                    <div class="mb-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="mr-3 h-4 w-4 rounded-full bg-blue-500"></div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ getLocalized(sampleCategory.title) }}
                                </h3>
                            </div>
                            <span class="text-sm text-gray-500">
                                {{ t('blog.categories.posts_count', { count: sampleCategory.posts_count }, sampleCategory.posts_count) }}
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ getLocalized(sampleCategory.description) }}
                        </p>
                    </div>

                    <!-- Mock Post Card -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">
                            {{ getLocalized(samplePost.title) }}
                        </h3>
                        <p class="mb-3 text-gray-600 dark:text-gray-400">
                            {{ getLocalized(samplePost.excerpt) }}
                        </p>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ formatDate(samplePost.published_at) }}</span>
                            <span>{{ samplePost.views_count }} {{ t('blog.posts.views') }}</span>
                            <span>{{ samplePost.reading_time }} {{ t('blog.posts.reading_time') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Date Formatting Demo -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-white">Date Formatting ({{ currentLocale }})</h2>
                    <div class="space-y-2">
                        <div><strong>Today:</strong> {{ formatDate(new Date()) }}</div>
                        <div><strong>DateTime:</strong> {{ formatDateTime(new Date()) }}</div>
                        <div><strong>Yesterday:</strong> {{ formatDate(yesterday) }}</div>
                    </div>
                </div>

                <!-- Language Info -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ t('language.current') }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">Available locales: {{ availableLocales.map((l) => l.name).join(', ') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { useLocalizedDate, useLocalizedField } from '@/composables/useTranslation';
import { availableLocales } from '@/i18n';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { t, locale } = useI18n();
const { getLocalized } = useLocalizedField();
const { formatDate, formatDateTime } = useLocalizedDate();

// Sample data
const sampleCategory = {
    title: { ru: 'Веб-разработка', en: 'Web Development', fr: 'Développement Web' },
    description: {
        ru: 'Всё о современной веб-разработке и технологиях',
        en: 'Everything about modern web development and technologies',
        fr: 'Tout sur le développement web moderne et les technologies',
    },
    posts_count: 5,
};

const samplePost = {
    title: {
        ru: 'Руководство по Vue.js и Laravel',
        en: 'Vue.js and Laravel Guide',
        fr: 'Guide Vue.js et Laravel',
    },
    excerpt: {
        ru: 'Изучаем создание современных веб-приложений с Vue.js и Laravel',
        en: 'Learn to build modern web applications with Vue.js and Laravel',
        fr: 'Apprenez à créer des applications web modernes avec Vue.js et Laravel',
    },
    published_at: '2025-09-20',
    views_count: 142,
    reading_time: 8,
};

// Computed
const currentLocale = computed(() => locale.value);
const yesterday = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() - 1);
    return date;
});
</script>
