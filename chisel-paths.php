<?php

return [
    'login' => 'resources/js/pages/auth/login.tsx',
    'register' => 'resources/js/pages/auth/register.tsx',
    'welcome' => 'resources/js/pages/welcome.tsx',
    'profile' => 'resources/js/pages/settings/profile.tsx',
    'security' => 'resources/js/pages/settings/security.tsx',
    'verify_email' => 'resources/js/pages/auth/verify-email.tsx',
    'two_factor_challenge' => 'resources/js/pages/auth/two-factor-challenge.tsx',
    'confirm_password' => 'resources/js/pages/auth/confirm-password.tsx',
    'auth_types' => 'resources/js/types/auth.ts',

    'two_factor_files' => [
        'resources/js/components/manage-two-factor.tsx',
        'resources/js/components/two-factor-setup-modal.tsx',
        'resources/js/components/two-factor-recovery-codes.tsx',
        'resources/js/components/ui/input-otp.tsx',
        'resources/js/hooks/use-two-factor-auth.ts',
        // Only the two-factor components above import these, so without 2FA they
        // are dead files. (True of the upstream kit as well, which leaves them
        // behind.) `ui/dialog.tsx` is deliberately NOT listed: upstream ships it
        // with several consumers and it is a generic primitive worth keeping,
        // even though this kit's Base UI port leaves it unimported without 2FA.
        'resources/js/components/alert-error.tsx',
        'resources/js/hooks/use-clipboard.ts',
    ],

    'two_factor_otp_package' => 'input-otp',

    'passkey_files' => [
        'resources/js/components/passkey-item.tsx',
        'resources/js/components/passkey-register.tsx',
        'resources/js/components/passkey-verify.tsx',
        'resources/js/components/manage-passkeys.tsx',
        // Added by this kit purely to build the passkey UI; upstream ships
        // neither. `ui/badge.tsx` is deliberately NOT listed: upstream ships it
        // with no importers at all, so it is a generic primitive, not a
        // passkey-owned file.
        'resources/js/components/ui/empty.tsx',
        'resources/js/components/ui/item.tsx',
    ],
];
