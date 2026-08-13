---
paths:
  - 'tests/**'
---

# Tests

## Ship PHPUnit class style — the installer converts to Pest
Tests in this kit are written as PHPUnit classes (`class FooTest extends TestCase`), not Pest functions, even though the finished app uses Pest.

`laravel new --pest` installs Pest and then runs `pest --drift` to convert the whole `tests/` directory. Writing Pest style here would break `--phpunit` installs and give drift nothing to do. Match the upstream kit: namespaced class, `use RefreshDatabase;`, `public function test_snake_case_name()`. Data providers convert cleanly — `#[DataProvider('name')]` plus a `public static function name(): iterable` becomes a Pest `dataset()`.

## Never use class constants or properties in a test
Drift moves method *bodies* into closures but leaves class members where they are, so a `private const ALLOWED = [...]` ends up at file top level, where `private` is a parse error. One such constant breaks the entire suite in every app created with `--pest`, and it will not show up until after `laravel new`.

Keep fixtures inside the method body as local variables. This is why `UiScaleTest` holds its allow-list in the test rather than as a constant.

## `AI_AGENT` breaks Pest — unset it when running tests from an agent
`laravel/pao` (a dev dependency of the kit) switches into agent-output mode when `AI_AGENT` is set in the environment, which coding agents export. It then JSON-wraps all test output and mangles Pest's argument parsing.

The damage is not limited to formatting. `pest --drift` fails under it with a misleading error:

```
The [--drift] argument only accepts the directory to convert as argument.
```

That message is wrong — passing a directory fails too, and the option is fine. Only the environment is at fault. Prefix any Pest or installer command with `env -u AI_AGENT -u CLAUDECODE` when running from an agent session:

```sh
env -u AI_AGENT -u CLAUDECODE php artisan test
env -u AI_AGENT -u CLAUDECODE php ./vendor/bin/pest --drift
```

`laravel new` inherits the variable too. An app created from inside an agent session silently keeps PHPUnit-style tests and leaves `pestphp/pest-plugin-drift` in `require-dev`, because the installer's drift step failed and its cleanup step never ran. The tests still pass, so nothing announces the problem. Unset the variable for the whole `laravel new` invocation, or re-run `pest --drift` afterwards and remove the plugin.
