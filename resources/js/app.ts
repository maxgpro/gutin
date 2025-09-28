import '../css/app.css';

import { Toaster } from '@/components/ui/sonner';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, Fragment, h } from 'vue';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import { initializeTheme } from './composables/useAppearance';
import { i18n, initializeLocale } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(Fragment, null, [h(Toaster, { class: 'pointer-events-auto' }), h(App, props)]),
        })
            .use(plugin)
            .use(i18n)
            .mount(el);

        // Global flash-to-toast bridge
        router.on('success', (event) => {
            try {
                const page = event.detail.page as any;
                const flash = page?.props?.flash;
                if (!flash) return;

                const success = typeof flash.success === 'function' ? flash.success() : flash.success;
                const error = typeof flash.error === 'function' ? flash.error() : flash.error;
                const warning = typeof flash.warning === 'function' ? flash.warning() : flash.warning;
                const info = typeof flash.info === 'function' ? flash.info() : flash.info;

                if (success) toast.success(success);
                if (error) toast.error(error);
                if (warning && (toast as any).warning) (toast as any).warning(warning);
                if (info && (toast as any).info) (toast as any).info(info);
            } catch {
                // no-op
            }
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Initialize locale from localStorage or backend
initializeLocale();
