import { useI18n } from 'vue-i18n';
import { toast, type ExternalToast } from 'vue-sonner';

type ToastKind = 'success' | 'error' | 'info' | 'warning' | 'message';

export function useToast() {
    const { t } = useI18n();

    function show(kind: ToastKind, titleKey: string, opts?: { descriptionKey?: string; params?: Record<string, unknown> } & ExternalToast) {
        const params = opts?.params ?? {};
        const title = t(titleKey, params);
        const description = opts?.descriptionKey ? t(opts.descriptionKey, params) : undefined;

        const base: ExternalToast = {
            description,
            ...opts,
        };

        switch (kind) {
            case 'success':
                return toast.success(title, base);
            case 'error':
                return toast.error(title, base);
            case 'warning':
                return toast.warning ? toast.warning(title, base) : toast(title, base);
            case 'info':
                return toast.info ? toast.info(title, base) : toast(title, base);
            default:
                return toast(title, base);
        }
    }

    return { toast, show };
}
