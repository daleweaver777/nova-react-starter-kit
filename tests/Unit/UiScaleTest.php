<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Density is a runtime setting: `app.css` rescales the app by overriding Tailwind's
 * own `--spacing` / `--text-*` / `--radius` primitives, which every utility compiles
 * down to. That only reaches values written on the scale.
 *
 * A hard-coded `text-[0.8rem]` or `min-w-[96px]` opts out and stays the same size in
 * both densities. The shadcn registry ships those occasionally, so a component added
 * later can silently reintroduce the drift this preset exists to remove.
 *
 * Bracketed values that reference a CSS variable are fine -- they follow whatever the
 * variable resolves to.
 *
 * @see resources/css/app.css
 */
class UiScaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return iterable<string, array{string}>
     */
    public static function uiComponents(): iterable
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources/js/components/ui')
            ->name('*.tsx');

        foreach ($files as $file) {
            yield $file->getRelativePathname() => [$file->getRealPath()];
        }
    }

    #[DataProvider('uiComponents')]
    public function test_it_keeps_shadcn_components_on_the_density_scale(string $path)
    {
        // Kept local rather than a class constant on purpose: the Laravel installer
        // runs `pest --drift` to convert these tests to Pest's function style, and
        // drift leaves class constants at file top level, where `private const` is a
        // parse error. Anything inside the method body converts cleanly.
        //
        // Geometry that is deliberately fixed. Each entry is a hairline, a hit area
        // or a viewport gutter -- none should shrink when the type scale does.
        $allowed = [
            'dialog.tsx' => 'calc(100%-2rem)',      // viewport gutter, not a control size
            'navigation-menu.tsx' => '-10px',       // invisible hover bridge above the popup
            'sidebar.tsx' => '2px',                 // drag-rail hairline
            'tooltip.tsx' => 'calc(-50%-2px)',      // arrow centring against its own border
        ];

        $offenders = [];
        $exempt = $allowed[basename($path)] ?? null;

        foreach (file($path) as $number => $line) {
            preg_match_all('/\[[^\]]*\d(?:\.\d+)?(?:rem|px|em)\b[^\]]*\]/', $line, $matches);

            foreach ($matches[0] as $match) {
                if (str_contains($match, 'var(--')) {
                    continue;
                }

                if ($exempt !== null && str_contains($match, $exempt)) {
                    continue;
                }

                $offenders[] = basename($path).':'.($number + 1).' '.$match;
            }
        }

        $this->assertEmpty($offenders);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function overlayComponents(): iterable
    {
        foreach (['alert-dialog.tsx', 'dialog.tsx', 'sheet.tsx'] as $file) {
            yield $file => [$file];
        }
    }

    /**
     * Density rescales the *spacing* scale, not the *container* scale, on purpose: an
     * overlay is sized by how much text reads comfortably on a line, which does not
     * change because the chrome got tighter.
     *
     * So `max-w-xs` (container, 20rem at any density) is right for an overlay and
     * `max-w-64` (spacing, 16rem -> 14rem) is wrong — it makes the dialog visibly
     * narrower in compact. Stock shadcn uses the container scale here; keep it.
     */
    #[DataProvider('overlayComponents')]
    public function test_it_sizes_overlays_on_the_container_scale_not_the_spacing_scale(string $file)
    {
        $contents = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/ui/'.$file
        );

        preg_match_all('/max-w-\d+(?:\.\d+)?\b/', $contents, $matches);

        $this->assertEmpty($matches[0]);
    }

    /**
     * The preset is the whole mechanism. If someone "simplifies" it into bespoke tokens,
     * newly added registry components stop inheriting the density.
     */
    public function test_it_scales_the_app_by_overriding_tailwind_primitives()
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

        $this->assertStringContainsString(":root[data-density='compact']", $css);
        $this->assertStringContainsString('--spacing:', $css);
        $this->assertStringContainsString('--text-sm:', $css);
        $this->assertStringContainsString('--text-sm--line-height:', $css);
    }

    /**
     * The sidebar is the one place whose width lives in JS rather than a class, so it is
     * the one place that can silently fall off the scale again.
     */
    public function test_it_keeps_the_sidebar_widths_on_the_spacing_scale()
    {
        $sidebar = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/ui/sidebar.tsx'
        );

        $this->assertStringContainsString('const SIDEBAR_WIDTH = "calc(var(--spacing) * 64)"', $sidebar);
        $this->assertStringContainsString('const SIDEBAR_WIDTH_MOBILE = "calc(var(--spacing) * 72)"', $sidebar);
        $this->assertStringContainsString('const SIDEBAR_WIDTH_ICON = "calc(var(--spacing) * 12)"', $sidebar);
    }
}
