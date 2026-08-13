import { useSyncExternalStore } from 'react';

export type Density = 'default' | 'compact';

export type UseDensityReturn = {
    readonly density: Density;
    readonly updateDensity: (mode: Density) => void;
};

const SERVER_SNAPSHOT: Density = 'default';

const listeners = new Set<() => void>();

/**
 * The snapshot is a plain string, so `Object.is` already gives
 * `useSyncExternalStore` the stable identity it needs -- unlike
 * `use-appearance.tsx`, which has to cache an object. Density has no "system"
 * mode either, so there is no media query to track and no resolved value to
 * carry.
 */
let currentDensity: Density = SERVER_SNAPSHOT;

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredDensity = (): Density => {
    if (typeof window === 'undefined') {
        return SERVER_SNAPSHOT;
    }

    return (localStorage.getItem('density') as Density) || SERVER_SNAPSHOT;
};

/**
 * `app.css` scopes the compact scale to `:root[data-density='compact']`, so the
 * attribute is the whole mechanism -- no class list, no re-render. Blade stamps
 * the same attribute from the cookie, which is what keeps first paint correct.
 */
const applyDensity = (density: Density): void => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.density = density;
    currentDensity = density;
};

const subscribe = (callback: () => void): (() => void) => {
    listeners.add(callback);

    return () => {
        listeners.delete(callback);
    };
};

const notify = (): void => listeners.forEach((listener) => listener());

export function initializeDensity(): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (!localStorage.getItem('density')) {
        localStorage.setItem('density', SERVER_SNAPSHOT);
        setCookie('density', SERVER_SNAPSHOT);
    }

    applyDensity(getStoredDensity());
}

const updateDensity = (mode: Density): void => {
    // Store in localStorage for client-side persistence...
    localStorage.setItem('density', mode);

    // Store in cookie for SSR...
    setCookie('density', mode);

    applyDensity(mode);
    notify();
};

const getSnapshot = (): Density => currentDensity;

const getServerSnapshot = (): Density => SERVER_SNAPSHOT;

export function useDensity(): UseDensityReturn {
    const density = useSyncExternalStore(
        subscribe,
        getSnapshot,
        getServerSnapshot,
    );

    return { density, updateDensity } as const;
}
