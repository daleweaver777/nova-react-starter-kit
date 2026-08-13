---
paths:
  - pnpm-workspace.yaml
---

# General

## The kit ships no lockfile — that constrains the supply-chain settings
Like the upstream starter kit, no `pnpm-lock.yaml` or `composer.lock` is committed here; every install resolves fresh against whatever is current that day. Lockfiles generated while working on the kit are excluded via `.git/info/exclude`, not `.gitignore`, because `.gitignore` is copied into every generated app and those apps *should* commit their lockfiles.

That is why `minimumReleaseAge` stays at `1440` (24h). A longer window rejects any dependency that published a patch in the meantime, and a rejected resolution blocks `pnpm install` and every `pnpm run` script — breaking the install for a reason the person installing cannot act on. Raise it to `10080` (7 days) in the generated app instead, where the lockfile pins the resolution and the longer window costs nothing.

## Adding to trustPolicyExclude requires checking provenance first
`trustPolicy: no-downgrade` rejects a package whose provenance attestation weakens between resolutions. Three entries are excluded:

- `semver` and `undici-types` — pre-provenance majors pulled transitively by `@babel/core` and `@types/node`, which read as downgrades against their attested siblings.
- `laravel-vite-plugin` — publishes attestations inconsistently rather than continuously (3.0.0 none, 3.1.0 attested, 3.2.0 none again). Without the exclusion `pnpm install` hard-fails on every fresh install.

Before adding anything else, confirm with `npm view <pkg>@<version> dist.attestations` that the package is genuinely pre-provenance or inconsistently attested, rather than compromised.

## strictDepBuilds is off because ignore-scripts is on
`.npmrc` sets `ignore-scripts=true`, so no dependency runs install scripts. pnpm 10+ still fails the install to make you acknowledge the ones it skipped (`ERR_PNPM_IGNORED_BUILDS`, currently `unrs-resolver`), which breaks `composer setup` and any plain `pnpm install`. The Laravel installer passes `--ignore-scripts` and slips past it, so this only bites after the app exists. Turning the gate off is not a loosening — the scripts were already refused.

## Declare pnpm in the composer setup script
`chisel` picks its package manager from the `setup`/`dev` composer scripts when no lockfile exists. If `setup` says `npm install`, the install hook builds a flat npm `node_modules`, then the Laravel installer layers pnpm on top; the hybrid tree makes `tsc` resolve stale declarations and `pnpm types:check` fails in a brand-new app. Keep `pnpm` in those scripts — the installer rewrites them for whichever manager was chosen.
