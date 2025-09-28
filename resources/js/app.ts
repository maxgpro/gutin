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
import { toastConfig, type FlashMessages } from './config';

// Helper function to process flash messages
function processFlashMessages(flash: FlashMessages | undefined) {
    if (!flash) return;

    try {
        const success = typeof flash.success === 'function' ? flash.success() : flash.success;
        const error = typeof flash.error === 'function' ? flash.error() : flash.error;
        const warning = typeof flash.warning === 'function' ? flash.warning() : flash.warning;
        const info = typeof flash.info === 'function' ? flash.info() : flash.info;

        // Show toasts with configured delays to avoid overlapping
        if (success) toast.success(success, toastConfig.defaults.success);
        if (error) setTimeout(() => toast.error(error, toastConfig.defaults.error), toastConfig.delays.error);
        if (warning) setTimeout(() => toast.warning?.(warning, toastConfig.defaults.warning) || toast(warning, toastConfig.defaults.warning), toastConfig.delays.warning);
        if (info) setTimeout(() => toast.info?.(info, toastConfig.defaults.info) || toast(info, toastConfig.defaults.info), toastConfig.delays.info);
    } catch (err) {
        console.warn('Flash message processing error:', err);
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h(Fragment, null, [
                h(Toaster, { 
                    class: 'pointer-events-auto',
                    ...toastConfig.toaster,
                }), 
                h(App, props)
            ]),
        });

        app.use(plugin).use(i18n).mount(el);

        // Process flash messages on initial page load
        if (props.initialPage?.props?.flash) {
            // Small delay to ensure toaster is mounted
            setTimeout(() => {
                processFlashMessages(props.initialPage.props.flash as FlashMessages);
            }, toastConfig.initialLoadDelay);
        }

        // Global flash-to-toast bridge for navigation
        router.on('success', (event) => {
            const page = event.detail.page as any;
            processFlashMessages(page?.props?.flash as FlashMessages);
        });

        // Handle navigation errors
        router.on('error', (event) => {
            console.error('Navigation error:', event.detail);
            // You can add error toasts here if needed
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
