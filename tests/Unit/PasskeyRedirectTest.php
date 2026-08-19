<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PasskeyRedirectTest extends TestCase
{
    public function test_passkey_success_falls_back_to_the_typed_dashboard_route(): void
    {
        $component = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/passkey-verify.tsx',
        );

        $this->assertIsString($component);
        $this->assertStringContainsString(
            "import { dashboard } from '@/routes';",
            $component,
        );
        $this->assertStringContainsString(
            'router.visit(response.redirect ?? dashboard());',
            $component,
        );
        $this->assertStringNotContainsString("response.redirect ?? '/dashboard'", $component);
    }
}
