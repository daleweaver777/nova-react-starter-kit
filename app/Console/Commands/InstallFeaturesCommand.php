<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Laravel\Chisel\Chisel;
use Laravel\Chisel\Question;
use Laravel\Chisel\Script;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;

class InstallFeaturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:features
        {--answers= : JSON string of answers to skip interactive prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Choose which starter kit features to keep';

    public function handle(): int
    {
        if ($this->shouldDeferInstallerHooks()) {
            return self::SUCCESS;
        }

        if (! file_exists(base_path('chisel.php'))) {
            return self::SUCCESS;
        }

        /** @var Script $script */
        $script = require base_path('chisel.php');

        try {
            $providedAnswers = $this->providedAnswers($script);
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $answers = $script
            ->collectAnswers()
            ->onQuestion(fn (Question $question) => multiselect(
                label: $question->label,
                options: $question->options,
                default: $question->default ?? [],
                required: $question->required,
                hint: $question->hint,
            ))
            ->interactive($this->input->isInteractive())
            ->withAnswers($providedAnswers);

        $skipNode = $this->shouldSkipNode();

        if (! $skipNode) {
            $this->installNodeDependencies();
        }

        $script->chisel($answers);

        if (! $skipNode) {
            $this->buildAssets();
            $this->removeNodeModules();
        }

        return self::SUCCESS;
    }

    /**
     * Resolve answers supplied ahead of time so the kit can be installed unattended.
     *
     * `--answers` wins. Otherwise `NOVA_AUTH_FEATURES` takes a comma-separated list of
     * feature keys, or `none` to disable every optional feature:
     *
     *     NOVA_AUTH_FEATURES=registration laravel new my-app --using=...
     *
     * This is read here rather than in `shouldDeferInstallerHooks()` on purpose. That
     * guard is what stops the hook running twice -- once while `composer create-project`
     * is still going and again when the installer invokes it for real -- so the env var
     * must not short-circuit it. Deferring first and reading the variable on the real
     * run keeps both behaviours intact.
     *
     * @return array<string, mixed>
     */
    protected function providedAnswers(Script $script): array
    {
        if ($this->option('answers') !== null) {
            return json_decode((string) $this->option('answers'), true, 512, JSON_THROW_ON_ERROR);
        }

        $value = $this->environmentValue('NOVA_AUTH_FEATURES');

        if ($value === null || trim($value) === '') {
            return [];
        }

        $features = array_values(array_filter(
            array_map(trim(...), explode(',', $value)),
            fn (string $feature): bool => $feature !== '',
        ));

        if (array_map(strtolower(...), $features) === ['none']) {
            $features = [];
        }

        $this->validateFeatures($script, $features);

        return ['auth_features' => $features];
    }

    /**
     * Fail loudly on a typo, which would otherwise read as "disable that feature".
     *
     * @param  array<int, string>  $features
     */
    protected function validateFeatures(Script $script, array $features): void
    {
        $question = collect($script->questions())
            ->first(fn (Question $question): bool => $question->name === 'auth_features');

        if (! $question instanceof Question) {
            return;
        }

        $known = array_map(strval(...), array_keys($question->options));
        $unknown = array_values(array_diff($features, $known));

        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf(
                'Unknown NOVA_AUTH_FEATURES value(s): %s. Valid features are: %s (or "none").',
                implode(', ', $unknown),
                implode(', ', $known),
            ));
        }
    }

    protected function environmentValue(string $name): ?string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        return is_string($value) ? $value : null;
    }

    protected function shouldDeferInstallerHooks(): bool
    {
        if ($this->option('answers') !== null) {
            return false;
        }

        return $this->installerFlag('LARAVEL_INSTALLER_DEFER_HOOKS');
    }

    protected function shouldSkipNode(): bool
    {
        return $this->installerFlag('LARAVEL_INSTALLER_NO_NODE');
    }

    protected function installerFlag(string $name): bool
    {
        return filter_var(
            $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name),
            FILTER_VALIDATE_BOOL,
        );
    }

    protected function installNodeDependencies(): void
    {
        $npm = Chisel::in(base_path())->npm();
        $packageManager = $npm->packageManager();

        spin(
            fn () => $npm->install(),
            "Installing dependencies with {$packageManager->value}...",
        );
    }

    protected function buildAssets(): void
    {
        $npm = Chisel::in(base_path())->npm();

        spin(
            fn () => $npm->run('build'),
            'Building assets...',
        );
    }

    /**
     * Hand the installer a clean slate for whichever package manager it picked.
     *
     * This hook runs before `laravel new` has chosen a package manager, and no
     * signal for that choice is passed down -- so the install above always uses
     * npm (see the `setup` composer script, which is what Chisel reads). If the
     * user then passes `--pnpm`, `--yarn` or `--bun`, the installer deletes the
     * foreign *lock file* but leaves `node_modules` alone, and pnpm/yarn/bun
     * will happily install *over* npm's flat tree and exit 0. The result is a
     * hybrid tree where `tsc` resolves stale declarations, so a brand-new app
     * fails `types:check` with errors like `'auth' is of type 'unknown'`.
     *
     * Removing the directory costs one re-install -- ~2s, since the lock file
     * this hook just wrote is kept and stays valid for npm -- and is the only
     * way to keep the kit correct across every package manager.
     */
    protected function removeNodeModules(): void
    {
        $path = base_path('node_modules');

        if (is_dir($path)) {
            (new Filesystem)->deleteDirectory($path);
        }
    }
}
