---
paths:
    - components.json
    - package.json
    - pnpm-workspace.yaml
    - resources/css/app.css
    - 'resources/js/components/ui/**'
    - 'resources/js/hooks/use-mobile.*'
---

# Shadcn registry updates

## Run the restoration checklist after every apply or relevant add

Current shadcn preset application overwrites intentional local changes without reporting conflicts. After `shadcn apply` or an `add` that touches the named files:

1. Remove duplicate `tw-animate-css`, `shadcn/tailwind.css` and Inter imports from `app.css`; restore its trailing newline.
2. Delete a generated `hooks/use-mobile.ts`; keep the local `use-mobile.tsx` as the only extensionless resolution target.
3. Restore Sonner's `useAppearance()` and `useFlashToast()` integrations; remove `next-themes` if nothing else uses it.
4. Restore sidebar widths as `calc(var(--spacing) * N)` rather than rem literals.
5. Review registry components for hard-coded rem/px/em sizes, numeric overlay `max-w-*` utilities and other density opt-outs.
6. Resolve any pnpm `allowBuilds` placeholder explicitly; do not approve a build merely to silence the prompt.

Run the unit tests, lint, type check, formatting check and SSR build after restoration.
