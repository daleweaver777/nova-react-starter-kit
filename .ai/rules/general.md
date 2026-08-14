---
paths:
  - pnpm-workspace.yaml
---

# General

These rules are shared verbatim between the starter kit and apps generated from it. Where the two differ, both cases are called out.

## `pnpm-workspace.yaml` ships unconditionally, even for npm users
Keep the file. The upstream kit ships one too, and it cannot be conditionally omitted: nothing knows which package manager was picked until long after the files are on disk. npm, yarn and bun ignore it entirely, so it is inert rather than wrong.

The consequence worth knowing is that everything it configures — `minimumReleaseAge`, `trustPolicy`, `blockExoticSubdeps` — is pnpm-only. An app installed with npm silently gets none of that supply-chain hardening. `.npmrc` (`ignore-scripts=true`) is the only part that applies to npm as well.

`publicHoistPattern: ['@inertiajs/core']` is upstream's and is load-bearing under pnpm's strict tree — do not drop it while trimming.

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

## The composer setup script must say `npm`, and the install hook must clean up
Two rules that only make sense together. Both are about the kit; a generated app has already been rewritten and is free of this.

`chisel` picks its package manager from the `setup`/`dev` composer scripts when no lockfile exists, so those scripts decide what the `install:features` hook runs. Keep them saying `npm`:

- The Laravel installer's `configureComposerScripts()` rewrites those scripts with `str_replace(['npm', 'npx', 'ppnpm'], [$pm, ...])`. It assumes the source says `npm`. Writing `pnpm install` there means an npm user keeps `pnpm install` and a bun user gets `pbun install`.
- npm ships with Node. `pnpm install` in the hook hard-fails the whole install on any machine without pnpm on `PATH` — which is most of them, and there is no fallback.

But the hook runs *before* `laravel new` has chosen a package manager, and nothing passes that choice down. So when the user passes `--pnpm`/`--yarn`/`--bun`, the installer deletes the foreign *lock file* and leaves `node_modules` — and pnpm/yarn/bun install straight over npm's flat tree and exit 0. The hybrid tree makes `tsc` resolve stale declarations, so a brand-new app fails `types:check` with `'auth' is of type 'unknown'`. `InstallFeaturesCommand::removeNodeModules()` deletes the directory after the hook's build so the installer's manager starts clean. Do not drop it as a redundant re-install: the lock file is kept, so the second install is ~2s, and it is the only thing making the kit package-manager agnostic.
