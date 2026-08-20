---
paths:
    - 'tests/**'
---

# Tests

## Determine whether this is the kit or a generated application

Tests in this starter-kit repository are PHPUnit classes (`class FooTest extends TestCase`). `laravel new --pest` installs Pest and runs `pest --drift` across `tests/`, so the generated application contains Pest functions instead. In a generated application, follow its installed framework and surrounding test style; do not convert tests back to PHPUnit.

`laravel new --pest` installs Pest and then runs `pest --drift` to convert the whole `tests/` directory. Writing Pest style here would break `--phpunit` installs and give drift nothing to do. Match the upstream kit: namespaced class, `use RefreshDatabase;`, `public function test_snake_case_name()`. Data providers convert cleanly — `#[DataProvider('name')]` plus a `public static function name(): iterable` becomes a Pest `dataset()`.

## Keep pre-Drift fixtures inside starter-kit test methods

In starter-kit tests that must pass through Drift, do not add class constants or properties. Drift moves method bodies into closures but can leave class members at file scope, where a declaration such as `private const` is a parse error.

Keep fixtures inside the method body as local variables. This is why `UiScaleTest` holds its allow-list in the test rather than as a constant.

## Disable Pao explicitly when conventional tool output is required

`laravel/pao` detects many agents, including Codex through `CODEX_*` variables, and emits compact JSON for supported PHP tools. That is intentional for routine agent runs. When diagnosing raw output or running a conversion command that should not be intercepted, use Pao's supported switch rather than unsetting an incomplete list of agent variables:

```sh
PAO_DISABLE=1 php artisan test
PAO_DISABLE=1 php ./vendor/bin/pest --drift
```
