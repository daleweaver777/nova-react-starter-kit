<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class SubmitButtonTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return iterable<string, array{string}>
     */
    public static function inertiaForms(): iterable
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources/js')
            ->name('*.tsx')
            ->contains('/<Form[\s>]/');

        foreach ($files as $file) {
            yield $file->getRelativePathname() => [$file->getRealPath()];
        }
    }

    /**
     * Base UI's `Button` primitive renders `type="button"` by default, unlike the plain
     * `<button>` that Radix rendered. Every `<Form>` therefore needs at least one button
     * that opts back in to `type="submit"`, or the form silently never submits.
     */
    #[DataProvider('inertiaForms')]
    public function test_it_gives_every_inertia_form_an_explicit_submit_button(string $path)
    {
        preg_match_all('/<Form\b.*?<\/Form>/s', file_get_contents($path), $matches);

        $this->assertNotEmpty($matches[0]);

        foreach ($matches[0] as $index => $form) {
            $this->assertStringContainsString(
                'type="submit"',
                $form,
                basename($path).' form '.($index + 1).' has no explicit submit button',
            );
        }
    }
}
