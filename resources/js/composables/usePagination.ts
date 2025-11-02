// resources/js/composables/usePagination.ts
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

export function usePagination() {
    const page = usePage();
    const perPage = ref(10);

    const sync = () => {
        // 1. Use current per_page from URL (via Laravel)
        const current = page.props.value?.perPage;
        if (current && typeof current === 'number') {
            perPage.value = current;
            return;
        }

        // 2. Fallback to server default
        const defaultFromServer = page.props.value?.defaultPerPage;
        if (defaultFromServer && typeof defaultFromServer === 'number') {
            perPage.value = defaultFromServer;
        }
    };

    // Sync on every Inertia navigation
    sync();
    router.on('navigate', sync);

    // Watch props (backup)
    watch(
        () => [page.props.value?.perPage, page.props.value?.defaultPerPage],
        sync,
        { immediate: true },
    );

    const update = () => {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage.value.toString());
        url.searchParams.set('page', '1');

        router.get(
            url.toString(),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return { perPage, update };
}
