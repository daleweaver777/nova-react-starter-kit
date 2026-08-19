<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class TwoFactorChallengeAccessibilityTest extends TestCase
{
    public function test_login_challenge_authentication_code_has_an_accessible_label_and_error(): void
    {
        $challenge = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/pages/auth/two-factor-challenge.tsx'
        );

        $this->assertIsString($challenge);
        $this->assertMatchesRegularExpression(
            '/<FieldLabel\s+htmlFor="code"\s+className="sr-only"\s*>\s+Authentication code\s+<\/FieldLabel>/',
            $challenge
        );
        $this->assertMatchesRegularExpression(
            '/<InputOTP[\s\S]*?id="code"[\s\S]*?aria-invalid=\{!!errors\.code\}[\s\S]*?aria-describedby=\{\s*errors\.code\s*\?\s*\'code-error\'\s*:\s*undefined\s*\}/',
            $challenge
        );
        $this->assertMatchesRegularExpression(
            '/<FieldError id="code-error">\s*\{errors\.code\}\s*<\/FieldError>/',
            $challenge
        );
    }

    public function test_setup_modal_authentication_code_has_an_accessible_label_and_error(): void
    {
        $setupModal = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/two-factor-setup-modal.tsx'
        );

        $this->assertIsString($setupModal);
        $this->assertMatchesRegularExpression(
            '/<FieldLabel htmlFor="otp" className="sr-only">\s+Authentication code\s+<\/FieldLabel>/',
            $setupModal
        );
        $this->assertMatchesRegularExpression(
            '/<InputOTP[\s\S]*?id="otp"[\s\S]*?aria-invalid=\{\s*!!errors\?\.confirmTwoFactorAuthentication\s*\?\.code\s*\}[\s\S]*?aria-describedby=\{\s*errors\?\.confirmTwoFactorAuthentication\?\.code\s*\?\s*\'otp-error\'\s*:\s*undefined\s*\}/',
            $setupModal
        );
        $this->assertMatchesRegularExpression(
            '/<FieldError id="otp-error">\s*\{errors\?\.confirmTwoFactorAuthentication\?\.code\}\s*<\/FieldError>/',
            $setupModal
        );
    }
}
