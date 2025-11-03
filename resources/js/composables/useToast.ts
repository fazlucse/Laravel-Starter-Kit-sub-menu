// resources/js/composables/useToast.ts
import { ref } from 'vue';

const toast = ref({ type: 'success', message: '', show: false } as any);

export const useToast = () => {
    const success = (msg: string) => {
        toast.value = { type: 'success', message: msg, show: true };
    };
    return { toast, success };
};
