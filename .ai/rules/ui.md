---
paths:
  - 'resources/js/components/ui/*.tsx'
---

# Ui

## Re-apply the two sonner customizations after any shadcn apply
The Laravel starter kit customizes this registry file in two ways, and `shadcn apply --preset` silently overwrites both:

1. `useFlashToast()` — the hook's only caller. Without it every `Inertia::flash('toast', ...)` on the server (ProfileController, SecurityController) reaches a mounted Toaster that never fires.
2. `theme={appearance}` from `useAppearance()` instead of `useTheme()` from `next-themes`. This app has no next-themes ThemeProvider, so the stock version falls back to `theme="system"` and toasts follow the OS rather than the in-app Light/Dark setting.

After any `shadcn apply`/`add` that touches sonner, restore both and re-check that `next-themes` has not been re-added to package.json.

## Inputs are 16px below md on purpose — do not flatten to one size
`ui/input.tsx` and `ui/textarea.tsx` carry `text-base ... md:text-sm`, so inputs render 16px below 768px and 14px at md and up. This is stock shadcn and it is deliberate: iOS Safari auto-zooms the page whenever you focus an input smaller than 16px. `InputGroupInput` wraps `Input`, so every `PasswordInput` inherits it too. Only the text jumps — `h-8` keeps the box at 32px either way.

This was reviewed and kept (2026-08-12) after it was reported as inconsistent when resizing a desktop window. Do not "fix" it by dropping `md:text-sm`: `text-sm` everywhere makes iOS zoom on every field focus, and `text-base` everywhere leaves inputs 2px larger than the labels and buttons beside them.

If it ever does need to be uniform, width is the wrong axis — use `text-sm pointer-coarse:text-base` (Tailwind 4.3+ supports `pointer-coarse`). That pins a resized desktop window at 14px while real touch devices keep 16px.

## Keep registry components on the density scale — no bracketed hard units
The density preset (see `.ai/rules/css.md`) only reaches values written on Tailwind's scale. A hard-coded `text-[0.8rem]` or `min-w-[96px]` opts out and stays the same size in both densities. The shadcn registry ships these occasionally, so re-check after every `shadcn add`/`apply`.

`tests/Unit/UiScaleTest.php` guards it: bracketed values containing a number plus rem/px/em fail unless they reference `var(--…)`. Three genuine-geometry exemptions are listed in that file (dialog viewport gutter, sidebar drag rail, tooltip arrow centring) — add to that list only for real geometry, never to silence a control size.

Prefer the scale step over the literal: `rounded-sm/md/lg` over `rounded-[calc(var(--radius)-3px)]`, `ring-3` over `ring-[3px]`, `translate-x-10` over `translate-x-[2.5rem]`.

Sidebar widths are the one size that lives in JS rather than a class, so they are the one that can silently fall off the scale: `SIDEBAR_WIDTH` and friends in `sidebar.tsx` must stay `calc(var(--spacing) * N)`, not rem literals.

## Overlay widths use the container scale, never the spacing scale
Density rescales `--spacing`, not `--container-*`. That is deliberate: a dialog is sized by how much text reads comfortably on a line, which does not change because the chrome got tighter.

So `max-w-xs` (container — 20rem at any density) is correct for a dialog/sheet, and `max-w-64` (spacing — 16rem, shrinking to 14rem in compact) is wrong: the overlay visibly narrows in compact. Stock shadcn already uses the container scale here — check the registry before "fixing" a width:

    curl -sL https://ui.shadcn.com/r/styles/base-nova/<component>.json

`tests/Unit/UiScaleTest.php` guards alert-dialog, dialog and sheet against numeric `max-w-*`.

Menus are the opposite case: `min-w-*` on a dropdown popup is a *minimum* that should densify with the rest, so the spacing scale is right there. `DropdownMenuSubContent` uses `min-w-24`, which is exactly stock's 96px at default density.
