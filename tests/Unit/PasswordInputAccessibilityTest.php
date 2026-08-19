<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PasswordInputAccessibilityTest extends TestCase
{
    public function test_password_reveal_control_is_keyboard_accessible(): void
    {
        $passwordInput = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/password-input.tsx'
        );

        $this->assertIsString($passwordInput);
        $this->assertMatchesRegularExpression(
            '/<InputGroupButton[\s\S]*?type="button"[\s\S]*?aria-label=/',
            $passwordInput
        );
        $this->assertStringNotContainsString('tabIndex={-1}', $passwordInput);
        $this->assertStringContainsString("'Show password'", $passwordInput);
        $this->assertStringContainsString("'Hide password'", $passwordInput);
    }

    public function test_password_reveal_control_stays_on_the_compact_density_scale(): void
    {
        $passwordInput = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/password-input.tsx'
        );

        $this->assertIsString($passwordInput);
        $this->assertMatchesRegularExpression(
            '/<InputGroupButton[\s\S]*?size="icon-xs"[\s\S]*?className="size-7"/',
            $passwordInput
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\[[^\]]*\d(?:\.\d+)?(?:rem|px|em)\b[^\]]*\]/',
            $passwordInput
        );
    }
}
