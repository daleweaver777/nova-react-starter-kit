import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        /**
         * Vite defaults to `localhost`, which Node 17+ resolves to ::1 here, so
         * laravel-vite-plugin writes `http://[::1]:5173` into `public/hot` and
         * every asset URL points at IPv6 loopback. Chrome blocks that as a
         * private-network request from the http://*.test origin, so the module
         * scripts never load: the page renders blank with no console error.
         * Pin to IPv4 so the hot file stays reachable.
         */
        host: '127.0.0.1',
    },
    plugins: [
        /**
         * No `fonts:` entry on purpose. `app.css` imports
         * `@fontsource-variable/inter` and `--font-sans` resolves to
         * `'Inter Variable'`, so a `bunny('Inter')` block here would register a
         * second, never-rendered family and preload four unused woff2 files on
         * every page.
         */
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
});
