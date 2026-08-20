---
paths:
    - 'resources/js/**/*.tsx'
---

# React TSX

## Base UI Button needs an explicit type="submit"

`ui/button.tsx` wraps Base UI's `Button` primitive, which renders `type="button"` by default — unlike the plain `<button>` Radix rendered. A submit button inside an Inertia `<Form>` must therefore spell `type="submit"` or the form silently never submits (no error, no network request).

`tests/Unit/SubmitButtonTypeTest.php` checks each `<Form>...</Form>` block for `type="submit"`.

## Style links with buttonVariants, never Button render={<Link/>}

Base UI's Button primitive (`ui/button.tsx`) assumes `nativeButton` is true and logs a console error when `render` produces an `<a>`. Setting `nativeButton={false}` silences it but makes Base UI stamp `role="button"` on the anchor, so the a11y tree reports a button with an href and the element loses link semantics.

For anything that navigates, render a real `Link`/`<a>` and apply `buttonVariants({ variant, size })` via `cn()`. Add `aria-current="page"` for the active item. See `layouts/settings/layout.tsx` and `pages/welcome.tsx`.

The mirror case: components whose Base UI default is `nativeButton` false (`DropdownMenuItem`, other menu items) error when `render` IS a real `<button>` — e.g. `<Link as="button">`. Pass `nativeButton` there (see `user-menu-content.tsx` logout).

`SidebarMenuButton` uses `useRender` directly and has no `nativeButton`, so `render={<Link/>}` is fine there.

## Password fields must clear with resetOnError

Inertia preserves component state when a submission returns validation errors, and these forms use uncontrolled inputs, so a rejected password stays in the field. Every `<Form>` containing a `PasswordInput` must pass `resetOnError`. Prefer an array naming only the password fields; the bare boolean form is valid when every field should reset.

`tests/Unit/PasswordResetOnErrorTest.php` checks each form containing `PasswordInput` for `resetOnError`. It does not validate that an array names every password field, so review that detail.

## Always wrap buttonVariants() in cn()

`buttonVariants()` is cva, not tailwind-merge. Its base includes `border border-transparent`, and the `outline` variant adds `border-border` — both survive in the returned string, and `border-transparent` wins in the generated CSS. Result: an outline link renders with no border at all (silent, looks "almost right").

`<Button>` is unaffected because it already does `cn(buttonVariants(...), className)`; twMerge drops the losing border-color. Raw call sites do not.

So always write `className={cn(buttonVariants({ variant }))}` even when there is nothing else to merge. The same trap applies to another cva helper only when its base and selected variant can emit conflicting Tailwind classes.

## There is no base type size — text outside a component renders at 16px

`app.css` sets no font-size on `html`/`body`, so anything not inside a component that declares one inherits the browser default 16px — two steps above Nova's 14px body scale. This matches shadcn: the Nova reference is also 16px at the root, and gets away with it because every text node there lives inside a `[data-slot]` component that sets its own size.

Do NOT "fix" this by adding `body { @apply text-sm }`. It diverges from the preset and hides the useful signal that otherwise unstyled copy may be missing a component such as `FieldDescription`, `CardDescription`, `Alert` or `FieldError`.

The usual trap is a bare `TextLink`, which deliberately carries no size so it can inherit from surrounding copy. `Field`/`FieldGroup` do not set one either, so a TextLink inside a Field needs an explicit `text-sm` — see the "Forgot your password?" link in `pages/auth/login.tsx`, which sits in a `Field orientation="horizontal"` next to the Remember-me label. Every other call site works only because its wrapper div says `text-sm`.

To check a page, inspect visible 16px leaf text in devtools. Explicit `text-base` titles and small-screen form controls are intentional; inherited 16px body copy without an explicit component or type utility is not.

## buttonVariants carries shrink-0 — use flex-1, not w-full, in a row

`buttonVariants` includes `shrink-0` in its base. Put several `w-full` links or buttons in a `flex-row` and they cannot shrink, so everything after the first overflows off-screen with no error — it just silently disappears.

Use `flex-1` instead (`flex: 1 1 0%`). basis-0 means they start at zero and grow into equal shares, so shrink never has to apply. Reset with `lg:w-full lg:flex-none` where the container turns back into a column.

Hit for real in `layouts/settings/layout.tsx`, where only "Profile" rendered below lg.

## Field's vertical variant stamps *:w-full — items-center cannot center

`ui/field.tsx`'s vertical variant is `flex flex-col *:w-full`. Every direct child is forced to full width, so adding `items-center` to a `Field` is dead code — the child fills the row and its own contents stay flush left.

Centre from inside the child instead. For `InputOTP` that means `containerClassName="justify-center"`; both `two-factor-setup-modal.tsx` and `pages/auth/two-factor-challenge.tsx` use that. The modal shipped left-aligned for a while because `items-center` looked like it should work.
