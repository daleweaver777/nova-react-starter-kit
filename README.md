# Shadcn UI Nova — React Starter Kit

A fork of [`laravel/react-starter-kit`](https://github.com/laravel/react-starter-kit) carrying the Nova theme: the shadcn `base-nova` style on [Base UI](https://base-ui.com) (not Radix), an Inter type stack, and a compact/standard density setting.

It keeps the upstream kit's install-time scaffolding intact, so the auth-feature prompt still works exactly as it does for the official kit.

## Installing

```sh
laravel new my-app --using=https://github.com/dalew/nova-react-starter-kit --pest
```

The installer asks which authentication features to keep and trims the rest — files, config, routes, tests, npm packages and all. Pin a release by appending a ref:

```sh
laravel new my-app --using=https://github.com/dalew/nova-react-starter-kit#v1.0.0 --pest
```

### Unattended installs

`NOVA_AUTH_FEATURES` answers the prompt ahead of time. Pass a comma-separated list, or `none` for a login-only app:

```sh
NOVA_AUTH_FEATURES=registration laravel new my-app \
  --using=https://github.com/dalew/nova-react-starter-kit --pest
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
pnpm doctor
```

## What this repo ships

Only two things that are not stock upstream or generated: `.ai/rules/` and `doctor.config.jsonc`. Everything else an app needs — `CLAUDE.md`, `AGENTS.md`, `.mcp.json`, `boost.json`, `.claude/skills/` — is produced by `laravel new --boost`, so it stays current rather than pinned to whatever this fork last committed.

## Working on this kit

Like the upstream kit, no lockfiles are committed: every install resolves fresh.

That has one sharp edge for contributors. With no `composer.lock`, `composer install` behaves like `composer update`, which fires `post-update-cmd` — and that runs `install:features`, which chisels the repo and deletes its own scaffolding. Set the guard the installer sets:

```sh
LARAVEL_INSTALLER_DEFER_HOOKS=1 composer install
```

### Rebasing on upstream

```sh
git remote add upstream https://github.com/laravel/react-starter-kit.git
git fetch upstream && git merge upstream/main
```

The delta is concentrated in `resources/js` and `resources/css`. Because the UI components are ported from Radix to Base UI, any upstream change under `resources/js/components/ui/` lands as a conflict to resolve by hand.

Keep the `@chisel-*` markers balanced when editing marked files — `laravel/chisel` throws on two consecutive opening or closing markers for the same tag, and an unbalanced pair silently produces invalid output for whoever disables that feature.

## License

MIT, as with the upstream starter kit.
