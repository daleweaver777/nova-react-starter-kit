# Shadcn UI Nova — React Starter Kit

A fork of [`laravel/react-starter-kit`](https://github.com/laravel/react-starter-kit) carrying the Nova theme: the shadcn `base-nova` style on [Base UI](https://base-ui.com) (not Radix), an Inter type stack, and a compact/standard density setting.

It keeps the upstream kit's install-time scaffolding intact, so the auth-feature prompt still works exactly as it does for the official kit.

## Installing

```sh
laravel new my-app --using=https://github.com/daleweaver777/nova-react-starter-kit --pest
```

The installer asks which authentication features to keep and trims the rest — files, config, routes, tests, npm packages and all.

Any package manager works: add `--pnpm`, `--bun` or `--yarn` to pick one, or pass nothing and get npm.

Install from `main`. It is the only ref that carries the Nova theme, and the command above already resolves to it; appending `#main` is equivalent, just explicit:

```sh
laravel new my-app --using=https://github.com/daleweaver777/nova-react-starter-kit#main --pest
```

Do not install from this repo's tags. The `upstream-sync-*` tags point at commits in *upstream's* history and contain none of the Nova theme, so installing one silently gives you a stock Laravel kit. They are comparison markers only — see [Syncing with upstream](#syncing-with-upstream).

### Unattended installs

`NOVA_AUTH_FEATURES` answers the prompt ahead of time. Pass a comma-separated list, or `none` for a login-only app:

```sh
NOVA_AUTH_FEATURES=registration laravel new my-app \
  --using=https://github.com/daleweaver777/nova-react-starter-kit --pest
```

Valid values: `email-verification`, `registration`, `2fa`, `passkeys`, `password-confirmation`. An unrecognised value fails the install rather than silently dropping the feature.

## After installing

```sh
cd my-app
composer nova:tools   # installs the shadcn + migrate-radix-to-base agent skills
```

`composer nova:tools` prompts for which agent to install the skills for, so run it yourself rather than in CI.

Run the React Doctor lint sweep whenever you want it — it is deliberately not wired to a hook, and not a dependency:

```sh
npm run doctor
```

Use `run` rather than the bare script name: `pnpm doctor` resolves to pnpm's own built-in `doctor` command instead of this script. `pnpm run doctor` is fine.

## What this repo ships

Only two things that are not stock upstream or generated: `.ai/rules/` and `doctor.config.jsonc`. Everything else an app needs — `CLAUDE.md`, `AGENTS.md`, `.mcp.json`, `boost.json`, `.claude/skills/` — is produced by `laravel new --boost`, so it stays current rather than pinned to whatever this fork last committed.

## Working on this kit

Like the upstream kit, no lockfiles are committed: every install resolves fresh.

That has one sharp edge for contributors. With no `composer.lock`, `composer install` behaves like `composer update`, which fires `post-update-cmd` — and that runs `install:features`, which chisels the repo and deletes its own scaffolding. Set the guard the installer sets:

```sh
LARAVEL_INSTALLER_DEFER_HOOKS=1 composer install
```

Working on the kit also generates `composer.lock`, `package-lock.json` and `pnpm-lock.yaml`, none of which should be committed. They are not in `.gitignore` deliberately — that file is copied into every generated app, and those apps *should* commit their lockfiles. Exclude them locally instead, once per clone:

```sh
printf 'composer.lock\npackage-lock.json\npnpm-lock.yaml\n' >> .git/info/exclude
```

### Syncing with upstream

The fork point is recorded as an annotated tag, `upstream-sync-<date>`, pointing at the upstream commit this kit was last reconciled against. Its only job is to make the comparison against the official kit cheap: a sync reviews what upstream has added since, instead of re-reading the whole delta each time.

These tags are bookkeeping and nothing else. Each points into *upstream's* history, so none of them contain the Nova theme and none is an install target — installs come from `main`.

A sync then runs:

```sh
git remote add upstream https://github.com/laravel/react-starter-kit.git
git fetch upstream
git diff upstream-sync-2026-08-12..upstream/main   # only what upstream added since
git merge upstream/main
```

After a successful merge, move the baseline forward and push it, or the next sync re-reviews work already resolved:

```sh
git tag -a upstream-sync-$(date +%F) upstream/main -m "Fork point: laravel/react-starter-kit main"
git push origin upstream-sync-$(date +%F)
```

Upstream ships a "Sync Laravel skeleton changes" commit every week or two, so expect to do this regularly rather than in one large catch-up.

#### Where the conflicts land

The delta is concentrated in `resources/js` and `resources/css`; the PHP side is four files and merges cleanly. Because the UI components are ported from Radix to Base UI, any upstream change under `resources/js/components/ui/` lands as a conflict to resolve by hand.

Two areas are worth extra care. `pages/settings/profile.tsx`, `pages/settings/appearance.tsx`, `layouts/settings/layout.tsx`, `two-factor-setup-modal.tsx` and both manifests are among upstream's most frequently touched files *and* rewritten here, so they conflict on most syncs.

The other is deletions. This kit removes nine components upstream still maintains — `app-header.tsx`, `auth-split-layout.tsx`, `app-header-layout.tsx`, `auth-card-layout.tsx`, `input-error.tsx`, `ui/select.tsx`, `ui/icon.tsx`, `ui/navigation-menu.tsx` and `ui/collapsible.tsx`. Upstream edits to these arrive as modify/delete conflicts; resolve them with `git rm`, or they silently return to the tree.

Keep the `@chisel-*` markers balanced when editing marked files — `laravel/chisel` throws on two consecutive opening or closing markers for the same tag, and an unbalanced pair silently produces invalid output for whoever disables that feature.

## License

MIT, as with the upstream starter kit.
