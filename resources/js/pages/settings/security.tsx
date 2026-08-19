import { Form, Head } from '@inertiajs/react';
import { useRef } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
/* @chisel-passkeys */
import type { Props as ManagePasskeysProps } from '@/components/manage-passkeys';
import ManagePasskeys from '@/components/manage-passkeys';
/* @end-chisel-passkeys */
/* @chisel-2fa */
import type { Props as ManageTwoFactorProps } from '@/components/manage-two-factor';
import ManageTwoFactor from '@/components/manage-two-factor';
/* @end-chisel-2fa */
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} /* @chisel-passkeys */ & ManagePasskeysProps /* @end-chisel-passkeys */ /* @chisel-2fa */ &
    ManageTwoFactorProps /* @end-chisel-2fa */;

export default function Security(props: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <>
            <Head title="Security settings" />

            <Card>
                <CardHeader>
                    <CardTitle>Update password</CardTitle>
                    <CardDescription>
                        Ensure your account is using a long, random password to
                        stay secure
                    </CardDescription>
                </CardHeader>

                <Form
                    {...SecurityController.update.form()}
                    noValidate
                    options={{
                        preserveScroll: true,
                    }}
                    resetOnError={[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]}
                    resetOnSuccess
                    onError={(errors) => {
                        if (errors.password) {
                            passwordInput.current?.focus();
                        }

                        if (errors.current_password) {
                            currentPasswordInput.current?.focus();
                        }
                    }}
                    className="flex flex-col gap-(--card-spacing)"
                >
                    {({ errors, processing }) => (
                        <>
                            <CardContent>
                                <FieldGroup>
                                    <Field
                                        data-invalid={!!errors.current_password}
                                    >
                                        <FieldLabel htmlFor="current_password">
                                            Current password
                                        </FieldLabel>

                                        <PasswordInput
                                            id="current_password"
                                            ref={currentPasswordInput}
                                            name="current_password"
                                            autoComplete="current-password"
                                            placeholder="Current password"
                                            aria-invalid={
                                                !!errors.current_password
                                            }
                                            aria-describedby={
                                                errors.current_password
                                                    ? 'current-password-error'
                                                    : undefined
                                            }
                                        />

                                        <FieldError id="current-password-error">
                                            {errors.current_password}
                                        </FieldError>
                                    </Field>

                                    <Field data-invalid={!!errors.password}>
                                        <FieldLabel htmlFor="password">
                                            New password
                                        </FieldLabel>

                                        <PasswordInput
                                            id="password"
                                            ref={passwordInput}
                                            name="password"
                                            autoComplete="new-password"
                                            placeholder="New password"
                                            passwordrules={props.passwordRules}
                                            aria-invalid={!!errors.password}
                                            aria-describedby={
                                                errors.password
                                                    ? 'password-error'
                                                    : undefined
                                            }
                                        />

                                        <FieldError id="password-error">
                                            {errors.password}
                                        </FieldError>
                                    </Field>

                                    <Field
                                        data-invalid={
                                            !!errors.password_confirmation
                                        }
                                    >
                                        <FieldLabel htmlFor="password_confirmation">
                                            Confirm password
                                        </FieldLabel>

                                        <PasswordInput
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            autoComplete="new-password"
                                            placeholder="Confirm password"
                                            passwordrules={props.passwordRules}
                                            aria-invalid={
                                                !!errors.password_confirmation
                                            }
                                            aria-describedby={
                                                errors.password_confirmation
                                                    ? 'password-confirmation-error'
                                                    : undefined
                                            }
                                        />

                                        <FieldError id="password-confirmation-error">
                                            {errors.password_confirmation}
                                        </FieldError>
                                    </Field>
                                </FieldGroup>
                            </CardContent>

                            <CardFooter className="justify-end">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="update-password-button"
                                >
                                    Save
                                </Button>
                            </CardFooter>
                        </>
                    )}
                </Form>
            </Card>

            {/* @chisel-2fa */}
            <ManageTwoFactor
                canManageTwoFactor={props.canManageTwoFactor}
                requiresConfirmation={props.requiresConfirmation}
                twoFactorEnabled={props.twoFactorEnabled}
            />
            {/* @end-chisel-2fa */}

            {/* @chisel-passkeys */}
            <ManagePasskeys
                canManagePasskeys={props.canManagePasskeys}
                passkeys={props.passkeys}
            />
            {/* @end-chisel-passkeys */}
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Security settings',
            href: edit(),
        },
    ],
};
