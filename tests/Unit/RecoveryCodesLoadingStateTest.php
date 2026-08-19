<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class RecoveryCodesLoadingStateTest extends TestCase
{
    public function test_recovery_code_loading_is_single_flight_and_deterministic(): void
    {
        $component = (string) file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/two-factor-recovery-codes.tsx'
        );

        $this->assertMatchesRegularExpression(
            '/if \(isFetchingRecoveryCodesRef\.current\) \{\s+return;\s+\}/',
            $component,
        );
        $this->assertMatchesRegularExpression(
            '/setCodesAreVisible\(true\);.*await loadRecoveryCodes\(\);/s',
            $component,
        );
        $this->assertStringNotContainsString(
            'setCodesAreVisible((areVisible) => !areVisible)',
            $component,
        );
        $this->assertStringContainsString(
            'isFetchingRecoveryCodesRef.current = false;',
            $component,
        );
    }

    public function test_recovery_code_button_exposes_its_pending_state(): void
    {
        $component = (string) file_get_contents(
            dirname(__DIR__, 2).'/resources/js/components/two-factor-recovery-codes.tsx'
        );

        $this->assertStringContainsString(
            'disabled={isFetchingRecoveryCodes}',
            $component,
        );
        $this->assertStringContainsString(
            'aria-busy={isFetchingRecoveryCodes}',
            $component,
        );
        $this->assertStringContainsString(
            "? 'Loading recovery codes'",
            $component,
        );
    }
}
