<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class PasswordResetOnErrorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return iterable<string, array{string}>
     */
    public static function passwordForms(): iterable
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources/js')
            ->name('*.tsx')
            ->contains('PasswordInput')
            ->filter(fn ($file) => (bool) preg_match('/<Form[\s>]/', $file->getContents()));

        foreach ($files as $file) {
            yield $file->getRelativePathname() => [$file->getRealPath()];
        }
    }

    /**
     * Inertia preserves component state when a submission comes back with validation
     * errors, and the auth forms use uncontrolled inputs, so a rejected password stays
     * in the field unless the form opts in to `resetOnError`.
     */
    #[DataProvider('passwordForms')]
    public function test_it_clears_password_fields_when_a_form_comes_back_with_errors(string $path)
    {
        preg_match_all('/<Form\b.*?<\/Form>/s', file_get_contents($path), $matches);

        $passwordForms = array_values(array_filter(
            $matches[0],
            fn (string $form): bool => str_contains($form, 'PasswordInput'),
        ));

        $this->assertNotEmpty($passwordForms);

        foreach ($passwordForms as $index => $form) {
            $this->assertStringContainsString(
                'resetOnError',
                $form,
                basename($path).' password form '.($index + 1).' does not reset after an error',
            );
        }
    }
}
