<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTabOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The forgot-password link sits in the "Remember me" row rather than beside the
     * password label, so tabbing out of the email field lands on the password input
     * instead of detouring through the link.
     */
    public function test_it_places_the_forgot_password_link_after_the_password_input()
    {
        $login = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/auth/login.tsx');

        $passwordInputPosition = strpos($login, '<PasswordInput');
        $forgotLinkPosition = strpos($login, 'Forgot your password?');

        $this->assertNotFalse($passwordInputPosition);
        $this->assertNotFalse($forgotLinkPosition);
        $this->assertGreaterThan($passwordInputPosition, $forgotLinkPosition);
    }
}
