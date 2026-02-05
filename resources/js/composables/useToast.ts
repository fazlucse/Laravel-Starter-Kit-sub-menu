// resources/js/composables/useToast.ts
import ToastAlert from '@/components/custom/ToastAlert.vue';
import { usePage } from '@inertiajs/vue3';
import { createApp, h, onMounted, ref, watchEffect } from 'vue';

export function useToast() {
    const showToast = ref(false);
    const toastType = ref<'success' | 'error'>('success');
    const toastMessage = ref('');

    // -----------------------------------------------------------------
    // 1. Show a toast (internal helper)
    // -----------------------------------------------------------------
    const show = (type: 'success' | 'error', msg: string) => {
        toastType.value = type;
        toastMessage.value = msg;
        showToast.value = true;

        setTimeout(() => {
            showToast.value = false;
        }, 3000);
    };

    // -----------------------------------------------------------------
    // 2. Public API
    // -----------------------------------------------------------------
    const success = (msg: string) => show('success', msg);
    const error = (msg: string) => show('error', msg);
    const loading = (msg = 'Loading…') => show('success', msg); // treat loading as a success-style toast
    const hide = () => {
        showToast.value = false;
    };

    // -----------------------------------------------------------------
    // 3. **GLOBAL** flash → toast conversion
    // -----------------------------------------------------------------
    const page = usePage();
    watchEffect(() => {
        const flash = page.props.flash as any;
        if (flash?.success) success(flash.success);
        if (flash?.error) error(flash.error);
    });

    // -----------------------------------------------------------------
    // 4. Mount ToastAlert once (outside the Vue tree)
    // -----------------------------------------------------------------
    onMounted(() => {
        const container = document.createElement('div');
        document.body.appendChild(container);

        const app = createApp({
            setup() {
                return () =>
                    h(ToastAlert, {
                        type: toastType.value,
                        message: toastMessage.value,
                        show: showToast.value,
                        'onUpdate:show': (v: boolean) => (showToast.value = v),
                    });
            },
        });
        app.mount(container);
    });

    return { success, error, loading, hide };
}
