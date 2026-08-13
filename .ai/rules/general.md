---
paths:
  - pnpm-workspace.yaml
---

# General

## minimumReleaseAge is staged at 1440 — raise it to 10080
Supply-chain hardening is intentionally below target. `minimumReleaseAge: 1440` (24h) is a staging value; the goal is `10080` (7 days).

A rejected lockfile blocks `pnpm install` *and* every `pnpm run` script, so the cutoff cannot exceed the age of the newest pinned package. Raise it once the lockfile ages past the new value, or after rebuilding with `pnpm clean --lockfile && pnpm install` — note that a full rebuild downgrades @base-ui/react and the rolldown bindings.

`trustPolicyExclude` lists `semver` and `undici-types` only because those pre-provenance majors (pulled by @babel/core and @types/node) read as trust downgrades against their attested siblings. Do not add to that list without checking the package is genuinely pre-provenance rather than compromised.
