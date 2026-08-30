import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import babel from '@rolldown/plugin-babel';
import tailwindcss from '@tailwindcss/vite';
import react, { reactCompilerPreset } from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig, lazyPlugins } from 'vite-plus';

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
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
            ],
        },
    },
    plugins: lazyPlugins(() => [
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
        react(),
        babel({
            presets: [reactCompilerPreset()],
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ]),
    lint: {
        ignorePatterns: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'tailwind.config.js',
            'resources/js/actions/**',
            'resources/js/components/ui/*',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: 'css',
        ignorePatterns: [
            '.github/**',
            'composer.json',
            'resources/js/components/ui/*',
            'resources/views/mail/*',
        ],
        sortTailwindcss: {
            functions: ['clsx', 'cn', 'cva'],
            entryPoint: 'resources/css/app.css',
        },
    },
});
