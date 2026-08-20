import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

type FormErrors = Record<string, unknown>;

export function focusFirstFormError(formId: string, errors: FormErrors): void {
    const form = document.getElementById(formId);

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    for (const fieldName of Object.keys(errors)) {
        const field = form.elements.namedItem(fieldName);

        if (!(field instanceof HTMLElement)) {
            continue;
        }

        field.focus();

        if (document.activeElement === field) {
            return;
        }
    }
}
