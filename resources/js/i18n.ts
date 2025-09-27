import { router } from '@inertiajs/vue3';
import { createI18n } from 'vue-i18n';

// Import locale messages
import en from './locales/en.json';
import fr from './locales/fr.json';
import ru from './locales/ru.json';

// Get current locale from Laravel (passed from backend)
const currentLocale = document.documentElement.lang || 'en';

// Available locales
export const availableLocales = [
    { code: 'ru', name: 'Русский', flag: '🇷🇺' },
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
];

// Create i18n instance
export const i18n = createI18n({
    legacy: false, // Use Composition API mode
    locale: currentLocale,
    fallbackLocale: 'en',
    messages: {
        ru,
        en,
        fr,
    },
    globalInjection: true,
    missingWarn: false,
    fallbackWarn: false,
});

// Helper function to switch locale
export function switchLocale(locale: string) {
    if (availableLocales.some((l) => l.code === locale)) {
        // Update frontend locale immediately for better UX
        i18n.global.locale.value = locale as 'ru' | 'en' | 'fr';
        document.documentElement.lang = locale;

        // Save to localStorage for persistence
        localStorage.setItem('preferred-locale', locale);

        // Update session locale on backend and refresh the current Inertia page
        // Controller responds with redirect()->back(), so Inertia will re-visit the current route
        router.post(
            '/locale/switch',
            { locale },
            {
                preserveState: true,
                preserveScroll: true,
                onError: (errors) => {
                    console.error('Failed to switch locale on backend:', errors);
                },
            },
        );
    }
}

// Load preferred locale from localStorage on initialization
export function initializeLocale() {
    const savedLocale = localStorage.getItem('preferred-locale');
    if (savedLocale && availableLocales.some((l) => l.code === savedLocale)) {
        const htmlLocale = document.documentElement.lang || 'en';

        // If the saved locale already matches the current HTML/lang (session-driven),
        // just align the frontend i18n locale without hitting the backend.
        if (savedLocale === htmlLocale) {
            i18n.global.locale.value = savedLocale as 'ru' | 'en' | 'fr';
            return;
        }

        // Defer the backend switch until after the app is mounted to avoid
        // calling Inertia router before it's fully initialized.
        requestAnimationFrame(() => switchLocale(savedLocale));
    }
}

export default i18n;
