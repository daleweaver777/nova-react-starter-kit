<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrimaryNavigationAccessibilityTest extends TestCase
{
    public function test_active_primary_navigation_link_identifies_the_current_page(): void
    {
        $component = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/nav-main.tsx',
        );

        $this->assertIsString($component);
        $this->assertMatchesRegularExpression(
            "/<Link\s+href=\{item\.href\}[\s\S]*?aria-current=\{\s*isActive \? 'page' : undefined\s*\}[\s\S]*?prefetch\s*\/>/",
            $component,
        );
        $this->assertStringContainsString('isActive={isActive}', $component);
    }
}
