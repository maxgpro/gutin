<template>
    <div class="flex items-center space-x-1">
        <button
            v-for="locale in availableLocales"
            :key="locale.code"
            @click="switchLanguage(locale.code)"
            :class="[
                'flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium transition-colors',
                currentLocale === locale.code
                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100',
            ]"
            :title="`Switch to ${locale.name}`"
        >
            {{ locale.flag }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { availableLocales, switchLocale } from '@/i18n';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { locale } = useI18n();

// Computed
const currentLocale = computed(() => locale.value);

// Methods
function switchLanguage(localeCode: string) {
    switchLocale(localeCode);
}
</script>
