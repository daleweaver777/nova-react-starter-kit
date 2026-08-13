<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DensityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The density preset in `app.css` keys off `<html data-density>`, so the attribute has
     * to be stamped server-side from the cookie. If it were only applied by the client
     * hook, every page would paint at the wrong scale before hydration.
     */
    public function test_the_root_view_defaults_to_the_standard_density()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-density="default"', escape: false);
    }

    /**
     * `withUnencryptedCookie`, not `withCookie`: the density cookie is written by the
     * client hook in plain text, so `bootstrap/app.php` exempts it from cookie
     * encryption. The test has to send it the same way the browser does.
     */
    public function test_the_root_view_reflects_the_density_cookie()
    {
        $this->actingAs(User::factory()->create());

        $this->withUnencryptedCookie('density', 'compact')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-density="compact"', escape: false);
    }

    public function test_the_appearance_settings_page_offers_both_density_options()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))->assertOk();

        $this->assertStringContainsString('DensityTabs', file_get_contents(
            dirname(__DIR__, 3).'/resources/js/pages/settings/appearance.tsx'
        ));
    }
}
