<script setup lang="ts">
import { availableLocales, switchLocale } from '@/i18n';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { locale } = useI18n();

// Computed
const currentLocale = computed(() => locale.value);
</script>

<template>
    <!-- Single list: row in expanded, column in collapsed sidebar -->
    <div class="flex items-center gap-1 group-data-[collapsible=icon]:flex-col">
        <button
            v-for="localeOption in availableLocales"
            :key="localeOption.code"
            @click="switchLocale(localeOption.code)"
            :class="[
                'flex h-8 w-8 items-center justify-center rounded-md text-base transition-all duration-200',
                // Active -> outline variant look; Inactive -> ghost/hover look
                currentLocale === localeOption.code
                    ? 'bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50'
                    : 'hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50',
            ]"
            :title="localeOption.name"
        >
            {{ localeOption.flag }}
        </button>
    </div>
</template>
