<template>
    <div class="relative inline-block text-left">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            :title="$t('language.switch')"
        >
            <span class="mr-2">{{ currentLanguage?.flag }}</span>
            <span class="hidden sm:inline">{{ currentLanguage?.name }}</span>
            <ChevronDown class="-mr-1 ml-2 h-5 w-5" />
        </button>

        <!-- Dropdown Menu -->
        <div
            v-show="isOpen"
            class="ring-opacity-5 absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black focus:outline-none dark:bg-gray-800"
            role="menu"
        >
            <div class="py-1" role="none">
                <button
                    v-for="locale in availableLocales"
                    :key="locale.code"
                    @click="switchLanguage(locale.code)"
                    :class="[
                        'flex w-full items-center px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700',
                        { 'bg-gray-100 dark:bg-gray-700': currentLocale === locale.code },
                    ]"
                    role="menuitem"
                >
                    <span class="mr-3">{{ locale.flag }}</span>
                    <span>{{ locale.name }}</span>
                    <Check v-if="currentLocale === locale.code" class="ml-auto h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                </button>
            </div>
        </div>

        <!-- Backdrop -->
        <div v-show="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
    </div>
</template>

<script setup lang="ts">
import { availableLocales, switchLocale } from '@/i18n';
import { Check, ChevronDown } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { locale } = useI18n();
const isOpen = ref(false);

// Computed
const currentLocale = computed(() => locale.value);
const currentLanguage = computed(() => availableLocales.find((l) => l.code === currentLocale.value));

// Methods
function switchLanguage(localeCode: string) {
    switchLocale(localeCode);
    isOpen.value = false;
}

// Close dropdown when clicking outside
function handleClickOutside(event: MouseEvent) {
    if (isOpen.value && !(event.target as Element)?.closest('.relative')) {
        isOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
