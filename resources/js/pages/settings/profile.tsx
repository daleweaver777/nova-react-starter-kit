import { Form, Head, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
/* @chisel-email-verification */
import TextLink from '@/components/text-link';
/* @end-chisel-email-verification */
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
    /* @chisel-email-verification */
    FieldDescription,
    /* @end-chisel-email-verification */
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { edit } from '@/routes/profile';
/* @chisel-email-verification */
import { send } from '@/routes/verification';
/* @end-chisel-email-verification */
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile(
    /* @chisel-email-verification */
    {
        mustVerifyEmail,
        status,
    }: {
        mustVerifyEmail: boolean;
        status?: string;
    },
    /* @end-chisel-email-verification */
) {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Profile settings" />

            <Card>
                <CardHeader>
                    <CardTitle>Profile</CardTitle>
                    <CardDescription>
                        Update your name and email address
                    </CardDescription>
                </CardHeader>

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="flex flex-col gap-(--card-spacing)"
                >
                    {({ processing, errors }) => (
                        <>
                            <CardContent>
                                <FieldGroup>
                                    <Field data-invalid={!!errors.name}>
                                        <FieldLabel htmlFor="name">
                                            Name
                                        </FieldLabel>

                                        <Input
                                            id="name"
                                            defaultValue={auth.user.name}
                                            name="name"
                                            required
                                            autoComplete="name"
                                            placeholder="Full name"
                                            aria-invalid={!!errors.name}
                                            aria-describedby={
                                                errors.name
                                                    ? 'name-error'
                                                    : undefined
                                            }
                                        />

                                        <FieldError id="name-error">
                                            {errors.name}
                                        </FieldError>
                                    </Field>

                                    <Field data-invalid={!!errors.email}>
                                        <FieldLabel htmlFor="email">
                                            Email address
                                        </FieldLabel>

                                        <Input
                                            id="email"
                                            type="email"
                                            defaultValue={auth.user.email}
                                            name="email"
                                            required
                                            autoComplete="username"
                                            placeholder="Email address"
                                            aria-invalid={!!errors.email}
                                            aria-describedby={
                                                errors.email
                                                    ? 'email-error'
                                                    : undefined
                                            }
                                        />

                                        <FieldError id="email-error">
                                            {errors.email}
                                        </FieldError>

                                        {/* @chisel-email-verification */}
                                        {mustVerifyEmail &&
                                            auth.user.email_verified_at ===
                                                null && (
                                                <FieldDescription>
                                                    Your email address is
                                                    unverified.{' '}
                                                    <TextLink
                                                        href={send()}
                                                        as="button"
                                                    >
                                                        Click here to re-send
                                                        the verification email.
                                                    </TextLink>
                                                    {status ===
                                                        'verification-link-sent' && (
                                                        <span className="mt-2 block font-medium text-green-600 dark:text-green-400">
                                                            A new verification
                                                            link has been sent
                                                            to your email
                                                            address.
                                                        </span>
                                                    )}
                                                </FieldDescription>
                                            )}
                                        {/* @end-chisel-email-verification */}
                                    </Field>
                                </FieldGroup>
                            </CardContent>

                            <CardFooter className="justify-end">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Save
                                </Button>
                            </CardFooter>
                        </>
                    )}
                </Form>
            </Card>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profile settings',
            href: edit(),
        },
    ],
};
