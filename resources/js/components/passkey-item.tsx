import { KeyRound, Trash2 } from 'lucide-react';
import { useState } from 'react';
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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemMedia,
    ItemTitle,
} from '@/components/ui/item';
import type { Passkey } from '@/types/auth';

type Props = {
    passkey: Passkey;
    onDelete: (id: number, onError: () => void) => void;
};

export default function PasskeyItem({ passkey, onDelete }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        setIsDeleting(true);
        onDelete(passkey.id, () => setIsDeleting(false));
    };

    /*
     * `ItemGroup` is `role="list"`, and ARIA requires a list to own listitem
     * children. `ui/item.tsx` renders a plain div and never sets one, so without
     * this the passkeys list exposes zero items to assistive tech. Set here
     * rather than in the registry file so `shadcn apply` cannot undo it.
     */
    return (
        <Item
            role="listitem"
            className="rounded-none border-x-0 border-t-0 border-b border-b-border last:border-b-0"
        >
            <ItemMedia
                variant="icon"
                className="size-8 rounded-lg bg-muted text-muted-foreground"
            >
                <KeyRound />
            </ItemMedia>

            <ItemContent>
                <ItemTitle>
                    {passkey.name}
                    {passkey.authenticator && (
                        <Badge
                            variant="secondary"
                            className="tracking-wide uppercase"
                        >
                            {passkey.authenticator}
                        </Badge>
                    )}
                </ItemTitle>
                <ItemDescription>
                    Added {passkey.created_at_diff}
                    {passkey.last_used_at_diff && (
                        <>
                            <span className="mx-1 text-muted-foreground/50">
                                /
                            </span>
                            Last used {passkey.last_used_at_diff}
                        </>
                    )}
                </ItemDescription>
            </ItemContent>

            <ItemActions>
                <AlertDialog>
                    <AlertDialogTrigger
                        render={
                            <Button
                                variant="ghost"
                                size="sm"
                                className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            />
                        }
                    >
                        <Trash2 />
                        <span className="sr-only">Remove</span>
                    </AlertDialogTrigger>

                    <AlertDialogContent size="sm">
                        <AlertDialogHeader>
                            <AlertDialogMedia className="bg-destructive/10 text-destructive">
                                <Trash2 />
                            </AlertDialogMedia>
                            <AlertDialogTitle>Remove passkey?</AlertDialogTitle>
                            <AlertDialogDescription>
                                The "{passkey.name}" passkey will be removed and
                                you will no longer be able to use it to sign in.
                            </AlertDialogDescription>
                        </AlertDialogHeader>

                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction
                                variant="destructive"
                                onClick={handleDelete}
                                disabled={isDeleting}
                            >
                                Remove
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </ItemActions>
        </Item>
    );
}
