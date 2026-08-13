<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class AuthPageLayoutTitleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return iterable<string, array{string}>
     */
    public static function authPages(): iterable
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources/js/pages/auth')
            ->name('*.tsx');

        foreach ($files as $file) {
            yield $file->getRelativePathname() => [$file->getRealPath()];
        }
    }

    /**
     * `auth-simple-layout` renders the page heading, its description and the logo link's
     * `sr-only` accessible name from layout props. `setLayoutProps` only applies during
     * the page's render -- which happens after the layout has already rendered -- so a
     * page relying on it alone server-renders with an empty heading and shows nothing
     * until hydration. A static `layout` object is resolved up front and fills that gap;
     * `setLayoutProps` still wins afterwards, so pages may declare both.
     */
    #[DataProvider('authPages')]
    public function test_it_gives_every_auth_page_a_server_rendered_layout_title(string $path)
    {
        $this->assertMatchesRegularExpression('/\.layout\s*=/', file_get_contents($path));
    }
}
