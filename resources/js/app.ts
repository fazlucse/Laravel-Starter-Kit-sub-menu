import GlobalPageLoader from '@/components/custom/PageLoader.vue'; // <-- add this
import { createInertiaApp, router } from '@inertiajs/vue3';
import 'flatpickr/dist/flatpickr.min.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h, nextTick } from 'vue';
import '../css/app.css';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) }).use(plugin);

        // -----------------------------------------------------------------
        // 1. Mount the global loader *outside* the Inertia root
        // -----------------------------------------------------------------
        const loaderEl = document.createElement('div');
        document.body.appendChild(loaderEl);
        createApp(GlobalPageLoader).mount(loaderEl);

        vueApp.mount(el);
    },

    // -----------------------------------------------------------------
    // 2. Inertia native progress bar (still works, we just hide it)
    // -----------------------------------------------------------------
    progress: {
        color: '#4B5563', // your colour
        showSpinner: false, // hide the tiny native spinner
        delay: 150, // optional – avoid flash on fast loads
    },
});

// -----------------------------------------------------------------
// 3. OPTIONAL: Forward Inertia progress → GlobalPageLoader
//     (so even *manual* router.visit() calls show the loader)
// -----------------------------------------------------------------
router.on('start', () => router.progress.start());
router.on('finish', () => router.progress.finish());

// Initialise theme after first render
nextTick(initializeTheme);
