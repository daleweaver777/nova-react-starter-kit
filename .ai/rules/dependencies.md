---
paths:
    - package.json
    - pnpm-workspace.yaml
    - .npmrc
    - .gitignore
    - composer.lock
    - package-lock.json
    - pnpm-lock.yaml
    - yarn.lock
    - bun.lock
    - bun.lockb
---

# Dependencies

These rules ship unchanged in the starter kit and generated applications. Observe the context-specific cases below.

## Keep pnpm-workspace.yaml for every package manager

The kit cannot know the selected package manager when its files are copied. npm, Yarn and Bun ignore `pnpm-workspace.yaml`, so keep it even in applications installed with another manager.

Its release-age, trust and exotic-subdependency settings are pnpm-only. `.npmrc` supplies `ignore-scripts=true` to npm; pnpm 11 and later read only authentication and registry settings from `.npmrc`. `publicHoistPattern: ['@inertiajs/core']` is inherited from the upstream kit and is required with pnpm's strict dependency tree.

## The kit resolves fresh; generated applications pin

The starter kit commits no package-manager or Composer lockfile. Contributor lockfiles belong in `.git/info/exclude`, not `.gitignore`, because `.gitignore` is copied into generated applications and those applications should commit their lockfiles.

The kit keeps `minimumReleaseAge` at 1440 minutes so a newly published transitive patch does not make a fresh installation impossible. A generated application with a committed lockfile may raise it to 10080 minutes. Frozen installs are unaffected, but adding or deliberately updating a dependency younger than seven days will wait for the quarantine window.

## Audit provenance exclusions; never add one just to make install pass

`trustPolicy: no-downgrade` rejects a package whose provenance weakens between resolutions. The current exclusions cover pre-provenance transitive majors (`semver` and `undici-types`) and inconsistent attestations from `laravel-vite-plugin`.

Before changing the list, identify the resolved path with `pnpm why <package>` and inspect every relevant version with `npm view <package>@<version> dist.attestations`. Remove exclusions when the old major leaves the graph or the publisher resumes continuous attestations.

## npm and pnpm block dependency scripts differently

npm gets `ignore-scripts=true` from `.npmrc`, so it runs no dependency lifecycle scripts. pnpm uses `allowBuilds`: unlisted dependency builds are denied, and reviewed packages that do not need a build are recorded explicitly as `false`.

Keep `strictDepBuilds: false` in this lockfile-free kit. With pnpm's default-deny build policy, `false` changes a newly discovered build from a fatal install error to a warning; it does not execute the script. Never set an `allowBuilds` entry to `true` without reviewing the exact package and script. Replace any CLI-generated `set this to true or false` placeholder before committing.
