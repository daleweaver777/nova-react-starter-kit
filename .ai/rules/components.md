---
paths:
  - 'resources/js/components/**'
---

# Components

## anchor-has-content false-fires on Base UI render props
react-doctor's `anchor-has-content` flags `render={<a href=... />}` as an empty anchor (e.g. nav-footer.tsx). It is a false positive.

Base UI composes via `useRender` + `mergeProps`, so the childless `<a/>` literal receives the parent's children at runtime — `SidebarMenuButton`/`Button` pass the icon and `<span>{title}</span>` straight through. The detector only reads the JSX literal and cannot see the composition.

Do not "fix" it by adding `aria-label` — that overrides an accessible name the visible text already provides correctly. Leave the markup alone.

`doctor.config.jsonc` now suppresses the rule for `nav-footer.tsx` via `ignore.overrides`, so scans are clean. If a new component renders a bare `<a>` through a `render` prop it will false-fire again — add that file to the same override rather than editing the markup.

## Destructive confirmations use AlertDialog, size="sm"
Deleting an account, removing a passkey, regenerating recovery codes and disabling 2FA all confirm through `ui/alert-dialog.tsx`, not `Dialog`: AlertDialog ignores an outside click. (Escape still closes it — that is standard dialog a11y, not a bug.)

Conventions: `size="sm"` keeps the header stacked and centred at every width and splits the footer into two equal columns — `size="default"` switches to icon-beside-title at `sm`. Media is `AlertDialogMedia` tinted `bg-destructive/10 text-destructive`; the action is `AlertDialogAction variant="destructive"`.

`AlertDialogAction` renders through Base UI's Button, so one inside an Inertia `<Form>` needs an explicit `type="submit"` or the form silently never submits.

Control the dialog only when its host stays mounted after success — `two-factor-recovery-codes.tsx` needs `open`/`onOpenChange` because that card persists, while the delete-account and remove-passkey dialogs close by unmounting.

`delete-user.tsx`'s tints ascend body -> footer -> button (/0 -> /5 -> /10 light, /0 -> /10 -> /20 dark). That order is load-bearing: `Button variant="destructive"` is itself `bg-destructive/10`, so a footer at /10 swallows the button.
