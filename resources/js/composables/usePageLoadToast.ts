import { useToast } from '@/composables/useToast';

export function usePageLoadToast() {
    const { success } = useToast();

    // Show toast only on real navigation (skip the very first load if you want)
    let first = true;
    const show = (title: string) => {
        if (first) {
            first = false;
            return;
        }
        success(`Loaded: ${title}`, 2000); // 2 seconds
    };

    return { show };
}
