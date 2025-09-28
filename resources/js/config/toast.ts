import type { ExternalToast } from 'vue-sonner';

/**
 * Configuration for Sonner toast notifications
 */
export const toastConfig = {
    // Default Toaster component settings
    toaster: {
        position: 'bottom-right' as const,
        closeButton: false,
        // closeButtonPosition: 'top-left' as const,
        richColors: false,
        duration: 4000,
        expand: true,
        visibleToasts: 3,
    },

    // Delays for multiple toasts to prevent overlapping
    delays: {
        success: 0,
        error: 100,
        warning: 200,
        info: 300,
    },

    // Default toast options for different types
    defaults: {
        success: {
            // No additional options for success by default
        } as ExternalToast,

        error: {
            duration: 6000, // Longer duration for errors
        } as ExternalToast,

        warning: {
            duration: 5000, // Slightly longer for warnings
        } as ExternalToast,

        info: {
            // Standard duration for info
        } as ExternalToast,
    },

    // Initial page load delay to ensure toaster is mounted
    initialLoadDelay: 100,
} as const;

/**
 * Flash messages type definition
 */
export interface FlashMessages {
    success?: (() => string | null) | string | null;
    error?: (() => string | null) | string | null;
    warning?: (() => string | null) | string | null;
    info?: (() => string | null) | string | null;
}
