import { Form } from '@inertiajs/react';
import { Trash2Icon } from 'lucide-react';
import { useId } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import PasswordInput from '@/components/password-input';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogMedia,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { focusFirstFormError } from '@/lib/utils';

export default function DeleteUser() {
    const formId = useId();

    return (
        <Card className="text-destructive ring-destructive/25">
            <CardHeader>
                <CardTitle>Delete account</CardTitle>
                <CardDescription className="text-destructive/90">
                    Delete your account and all of its resources
                </CardDescription>
            </CardHeader>
            <CardContent>
                <p>Please proceed with caution, this cannot be undone.</p>
            </CardContent>

            <CardFooter className="justify-end border-destructive/20 bg-destructive/5 dark:bg-destructive/10">
                <AlertDialog>
                    <AlertDialogTrigger
                        render={
                            <Button
                                variant="destructive"
                                data-test="delete-user-button"
                            />
                        }
                    >
                        Delete
                    </AlertDialogTrigger>

                    <AlertDialogContent size="sm">
                        <Form
                            id={formId}
                            {...ProfileController.destroy.form()}
                            noValidate
                            options={{
                                preserveScroll: true,
                            }}
                            onError={(errors) =>
                                focusFirstFormError(formId, errors)
                            }
                            resetOnError={['password']}
                            resetOnSuccess
                            className="grid gap-4"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <AlertDialogHeader>
                                        <AlertDialogMedia className="bg-destructive/10 text-destructive">
                                            <Trash2Icon />
                                        </AlertDialogMedia>
                                        <AlertDialogTitle>
                                            Delete account?
                                        </AlertDialogTitle>
                                        <AlertDialogDescription>
                                            Once your account is deleted, all of
                                            its resources and data will also be
                                            permanently deleted. Please enter
                                            your password to confirm you would
                                            like to permanently delete your
                                            account.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <Field data-invalid={!!errors.password}>
                                        <FieldLabel
                                            htmlFor="password"
                                            className="sr-only"
                                        >
                                            Password
                                        </FieldLabel>

                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            placeholder="Password"
                                            autoComplete="current-password"
                                            aria-invalid={!!errors.password}
                                            aria-describedby={
                                                errors.password
                                                    ? 'delete-password-error'
                                                    : undefined
                                            }
                                        />

                                        <FieldError id="delete-password-error">
                                            {errors.password}
                                        </FieldError>
                                    </Field>

                                    <AlertDialogFooter>
                                        <AlertDialogCancel
                                            onClick={() =>
                                                resetAndClearErrors()
                                            }
                                        >
                                            Cancel
                                        </AlertDialogCancel>

                                        <AlertDialogAction
                                            type="submit"
                                            variant="destructive"
                                            disabled={processing}
                                            data-test="confirm-delete-user-button"
                                        >
                                            Delete
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </>
                            )}
                        </Form>
                    </AlertDialogContent>
                </AlertDialog>
            </CardFooter>
        </Card>
    );
}
