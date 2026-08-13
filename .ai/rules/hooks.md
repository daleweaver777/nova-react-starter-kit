---
paths:
  - 'resources/js/hooks/**'
---

# Hooks

## Keep use-mobile.tsx; delete the use-mobile.ts a preset re-adds
`shadcn apply --preset` (and `shadcn add sidebar`) drops its own `resources/js/hooks/use-mobile.ts`. This project already has `use-mobile.tsx`, so `@/hooks/use-mobile` then resolves to two files — Vite picks the `.ts`, silently shadowing the `.tsx`.

Delete the `.ts` copy after any shadcn apply/add. The upstream version calls `setState` synchronously inside a `useEffect`, which fails this project's `react-hooks/set-state-in-effect` lint rule; the local `.tsx` uses `useSyncExternalStore` with a server snapshot and passes.

## react-doctor reports use-flash-toast.ts as unused — do not delete it
`deslop/unused-file` flags `resources/js/hooks/use-flash-toast.ts` as unreachable. It is a false positive. The real chain is `resources/js/app.tsx` -> `@/components/ui/sonner` -> `useFlashToast()`; deslop does not traverse it because `components/ui/**` is outside its entry graph.

Deleting the hook is silent: no build error, no type error, but every `Inertia::flash('toast', ...)` from the server stops rendering. See the sonner rule for the same customization from the other direction.

## useSyncExternalStore: cache the snapshot and notify on OS theme change
`getSnapshot` runs on every render and React bails out only when the value is `Object.is`-equal, so it must return a cached reference. Building a fresh object there loops forever. In `use-appearance.tsx` `applyTheme` is the sole writer of `currentSnapshot`, which also keeps it in step with the `dark` class.

The snapshot has to carry `resolvedAppearance`, not just `appearance`. On "system" an OS light/dark flip changes the resolved value while `appearance` stays `'system'`, so a `notify()` alone would be swallowed by the equality check. The media-query listener must call `notify()` too, or consumers of `resolvedAppearance` (the setup modal's QR inversion) keep a stale value.

Do not read `matchMedia` during render — it belongs in the store, or concurrent rendering will not track it.
