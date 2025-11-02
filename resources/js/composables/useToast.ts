// resources/js/composables/useToast.ts
import { ref } from 'vue';

interface Toast {
    id: number;
    type: 'success' | 'error';
    message: string;
}

const toasts = ref<Toast[]>([]);
let id = 0;

export function useToast() {
    const add = (type: Toast['type'], message: string, duration = 5000) => {
        const toast: Toast = { id: ++id, type, message };
        toasts.value.push(toast);
        if (duration > 0) setTimeout(() => remove(toast.id), duration);
    };

    const remove = (id: number) => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    };

    return {
        toasts,
        success: (msg: string) => add('success', msg),
        error: (msg: string) => add('error', msg),
        remove,
    };
}
