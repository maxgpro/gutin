import { useI18n } from 'vue-i18n';

/**
 * Get localized field value from multilingual object
 * @param data - Object with localized fields (e.g., {ru: 'Название', en: 'Title'})
 * @param locale - Target locale (optional, uses current if not provided)
 * @param fallback - Fallback value if translation not found
 */
export function useLocalizedField() {
    const { locale } = useI18n();

    return {
        getLocalized: (data: Record<string, string> | string | null | undefined, targetLocale?: string, fallback = ''): string => {
            if (!data) return fallback;

            // If data is already a string, return it
            if (typeof data === 'string') return data;

            const currentLocale = targetLocale || locale.value;

            // Try current locale first
            if (data[currentLocale]) {
                return data[currentLocale];
            }

            // Try fallback locales
            const fallbackLocales = ['en', 'ru', 'fr'];
            for (const fallbackLocale of fallbackLocales) {
                if (data[fallbackLocale]) {
                    return data[fallbackLocale];
                }
            }

            // Return first available value
            const values = Object.values(data).filter(Boolean);
            return values.length > 0 ? values[0] : fallback;
        },
    };
}

/**
 * Format date according to current locale
 */
export function useLocalizedDate() {
    const { locale, t } = useI18n();

    return {
        formatDate: (date: string | Date, options: Intl.DateTimeFormatOptions = {}) => {
            if (!date) return '';

            const dateObj = typeof date === 'string' ? new Date(date) : date;

            const defaultOptions: Intl.DateTimeFormatOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                ...options,
            };

            return new Intl.DateTimeFormat(locale.value, defaultOptions).format(dateObj);
        },

        formatDateTime: (date: string | Date) => {
            const { formatDate } = useLocalizedDate();
            return formatDate(date, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        formatRelative: (date: string | Date) => {
            if (!date) return '';

            const dateObj = typeof date === 'string' ? new Date(date) : date;
            const now = new Date();
            const diffMs = now.getTime() - dateObj.getTime();
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

            if (diffDays === 0) return t('common.today');
            if (diffDays === 1) return t('common.yesterday');
            if (diffDays < 7) return t('common.days_ago', { count: diffDays }, diffDays);

            const { formatDate } = useLocalizedDate();
            return formatDate(date);
        },
    };
}
