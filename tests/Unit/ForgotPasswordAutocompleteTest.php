<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ForgotPasswordAutocompleteTest extends TestCase
{
    public function test_email_field_uses_email_autocomplete(): void
    {
        $forgotPasswordPage = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/pages/auth/forgot-password.tsx',
        );

        $this->assertIsString($forgotPasswordPage);
        $this->assertMatchesRegularExpression(
            '/<Input\b(?=[^>]*\bname="email")(?=[^>]*\bautoComplete="email")[^>]*>/s',
            $forgotPasswordPage,
        );
    }
}
