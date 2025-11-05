// resources/js/composables/useGlobalLoader.ts
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export const isGloballyLoading = ref(false);

router.on('start', () => (isGloballyLoading.value = true));
router.on('finish', () => (isGloballyLoading.value = false));
router.on('error', () => (isGloballyLoading.value = false));
