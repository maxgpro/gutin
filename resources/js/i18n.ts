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

        // todo: refactor to inertia
        // Send request to backend to update session locale using Inertia
        // import('@inertiajs/vue3').then(({ router }) => {
        //   router.post('/locale/switch', { locale }, {
        //     preserveState: true,
        //     preserveScroll: true,
        //     onError: (errors) => {
        //       console.error('Failed to switch locale on backend:', errors)
        //     }
        //   })
        // }).catch(console.error)

        // Send request to backend to update session locale
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (csrfToken) {
            fetch('/locale/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ locale }),
                credentials: 'same-origin',
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    console.log('Locale switched successfully:', data);
                })
                .catch((error) => {
                    console.error('Failed to switch locale on backend:', error);
                });
        } else {
            console.error('CSRF token not found');
        }
    }
}

// Load preferred locale from localStorage on initialization
export function initializeLocale() {
    const savedLocale = localStorage.getItem('preferred-locale');
    if (savedLocale && availableLocales.some((l) => l.code === savedLocale)) {
        switchLocale(savedLocale);
    }
}

export default i18n;
