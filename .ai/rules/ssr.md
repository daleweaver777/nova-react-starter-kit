---
paths:
    - 'resources/js/**/*.ts'
    - 'resources/js/**/*.tsx'
---

# Server-side rendering

## Guard browser globals read during render

Inertia SSR is enabled in `config/inertia.php`. Reading `navigator`, `window`, `document` or `localStorage` during module initialization or render, including a `useState` initializer or a helper called from render, throws in Node.

Use a `typeof` guard and a safe server default for one-off reads. For reactive browser state, use `useSyncExternalStore` with a stable server snapshot as shown by `use-appearance.tsx` and `use-mobile.tsx`. Event handlers and effects run only in the browser and may access browser APIs directly.

The Vite development server does not reliably exercise SSR; verify relevant changes with `pnpm run build:ssr`.
