<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { availableLocales, switchLocale } from '@/i18n';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { locale } = useI18n();

// Computed
const currentLocale = computed(() => locale.value);
const currentLanguage = computed(() => availableLocales.find((l) => l.code === currentLocale.value));

// Language model for Select component
const languageModel = computed({
    get: () => currentLocale.value,
    set: (value: string) => switchLocale(value),
});
</script>

<template>
    <!-- Collapsed sidebar: simple flag buttons -->
    <div class="hidden group-data-[collapsible=icon]:block">
        <div class="flex flex-col gap-1">
            <button
                v-for="localeOption in availableLocales"
                :key="localeOption.code"
                @click="switchLocale(localeOption.code)"
                :class="[
                    'flex h-8 w-8 items-center justify-center rounded-md text-base transition-all duration-200',
                    currentLocale === localeOption.code ? 'bg-indigo-100 dark:bg-indigo-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                ]"
                :title="localeOption.name"
            >
                {{ localeOption.flag }}
            </button>
        </div>
    </div>

    <!-- Expanded sidebar: Select dropdown -->
    <div class="block group-data-[collapsible=icon]:hidden">
        <Select v-model="languageModel">
            <SelectTrigger class="h-8 w-20 px-2">
                <div class="flex items-center gap-1">
                    <span class="text-base">{{ currentLanguage?.flag }}</span>
                    <!-- <span class="text-xs font-medium">{{ currentLanguage?.code.toUpperCase() }}</span> -->
                </div>
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="localeOption in availableLocales" :key="localeOption.code" :value="localeOption.code">
                    <div class="flex items-center gap-2">
                        <span class="text-base">{{ localeOption.flag }}</span>
                        <span class="text-sm">{{ localeOption.name }}</span>
                    </div>
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
