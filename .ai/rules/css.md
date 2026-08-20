---
paths:
    - resources/css/app.css
---

# CSS

## Keep project CSS outside the shadcn-owned preset region

The plain `:root`, `.dark`, `@theme inline` and `@layer base` blocks are registry output. Keep exactly one of each and do not put project-specific declarations inside them. A preset that changes their values may replace them.

Project-specific CSS—the `@source` directives, dark custom variant, density overrides and coarse-pointer guard—lives above the preset region. The import block stays first because CSS requires imports to precede other rules.

## Orientation variants come from `shadcn/tailwind.css`

Base UI renders orientation as `data-orientation="vertical|horizontal"`, and the `ui/` components style it with `data-vertical:` / `data-horizontal:`. Those variants are declared upstream in `shadcn/tailwind.css` (imported at the top of `app.css`) as `:where([data-orientation="…"])` — do not redeclare them locally.

Spell them `data-vertical:` / `data-horizontal:` at call sites. `data-[orientation=vertical]:` compiles to the same CSS, but tailwind-merge treats the two spellings as unrelated, so an override written the other way will not replace the stock class.

## Density is a runtime setting driven by Tailwind's own scale primitives

`:root[data-density='compact']` in `app.css` overrides `--spacing`, `--text-*`, `--text-*--line-height` and `--radius`. Tailwind compiles scale utilities against those primitives, so this rescales the whole app at runtime—including components added later by `shadcn add`.

Do NOT replace it with bespoke tokens (`--control-h`, `--text-body`): a fresh registry component would ignore them until hand-edited, which is the drift the block exists to prevent.

Default density declares nothing — stock Tailwind IS the default. `--spacing: 0.21875rem` is 7/8 of stock, taken from the Mira preset's control ratios, so `h-8` lands on exactly 28px.

The block sits above the `shadcn apply --preset` region on purpose and still wins: Tailwind declares `--spacing` inside `@layer theme`, and unlayered CSS always beats layered CSS.

The `@media (pointer: coarse)` guard below it is load-bearing — compact drops `--text-base` to 14px, and iOS Safari zooms any focused field under 16px. `tests/Unit/UiScaleTest.php` asserts the preset still overrides the primitives.

## shadcn apply duplicates the import block

The current CLI re-appends `tw-animate-css`, `shadcn/tailwind.css` and `@fontsource-variable/inter` and strips the trailing newline. Follow the restoration checklist in `.ai/rules/shadcn.md`; neither issue breaks the build, so automated compilation alone will not catch them.
