---
paths:
  - pnpm-workspace.yaml
---

# General

These rules are shared verbatim between the starter kit and apps generated from it. Where the two differ, both cases are called out.

## Lockfiles: the kit resolves fresh, an app pins
The starter kit commits no `pnpm-lock.yaml` or `composer.lock` — like the upstream kit, every install resolves against whatever is current that day. A generated app is the opposite: it commits both, and should.

In the kit, lockfiles produced while working on it are excluded via `.git/info/exclude`, never `.gitignore`, because `.gitignore` is copied into every generated app and would stop those apps tracking their own lockfiles.

This drives `minimumReleaseAge`. It stays at `1440` (24h) in the kit: a longer window rejects any dependency that published a patch in the meantime, and a rejected resolution blocks `pnpm install` and every `pnpm run` script — breaking the install for a reason the person installing cannot act on. In a generated app the lockfile pins the resolution, so raise it there to `10080` (7 days), which costs nothing.

## Adding to trustPolicyExclude requires checking provenance first
`trustPolicy: no-downgrade` rejects a package whose provenance attestation weakens between resolutions.

- `semver` and `undici-types` — pre-provenance majors pulled transitively by `@babel/core` and `@types/node`, which read as downgrades against their attested siblings.
- `laravel-vite-plugin` — attests inconsistently rather than continuously (3.0.0 none, 3.1.0 attested, 3.2.0 none again). Required in the kit, where nothing is pinned. An app with a committed lockfile will not hit it until it next re-resolves, and then needs the same entry.

Before adding anything else, confirm with `npm view <pkg>@<version> dist.attestations` that the package is genuinely pre-provenance or inconsistently attested, rather than compromised.

## strictDepBuilds is off because ignore-scripts is on
`.npmrc` sets `ignore-scripts=true`, so no dependency runs install scripts. pnpm 10+ still fails the install to make you acknowledge the ones it skipped (`ERR_PNPM_IGNORED_BUILDS`, currently `unrs-resolver`), which breaks `composer setup` and any plain `pnpm install`. The Laravel installer passes `--ignore-scripts` and slips past it, so this surfaces only after the app exists — an app with a warm `node_modules` will not see it until a clean install. Turning the gate off is not a loosening: the scripts were already refused.

## Declare pnpm in the composer setup script
`chisel` picks its package manager from the `setup`/`dev` composer scripts when no lockfile exists. If `setup` says `npm install`, the install hook builds a flat npm `node_modules`, then the Laravel installer layers pnpm on top; the hybrid tree makes `tsc` resolve stale declarations and `pnpm types:check` fails in a brand-new app. Keep `pnpm` in those scripts — the installer rewrites them for whichever manager was chosen.
