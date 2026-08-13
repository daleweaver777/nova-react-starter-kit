import { useSyncExternalStore } from 'react';

export type ResolvedAppearance = 'light' | 'dark';
export type Appearance = ResolvedAppearance | 'system';

export type UseAppearanceReturn = {
    readonly appearance: Appearance;
    readonly resolvedAppearance: ResolvedAppearance;
    readonly updateAppearance: (mode: Appearance) => void;
};

type AppearanceSnapshot = {
    readonly appearance: Appearance;
    readonly resolvedAppearance: ResolvedAppearance;
};

const listeners = new Set<() => void>();
let currentAppearance: Appearance = 'system';

const SERVER_SNAPSHOT: AppearanceSnapshot = {
    appearance: 'system',
    resolvedAppearance: 'light',
};

/**
 * Cached so `getSnapshot` returns a stable reference. `useSyncExternalStore`
 * calls it on every render and bails out when the value is `Object.is`-equal,
 * so building a fresh object here would loop forever. `applyTheme` is the only
 * writer, which also keeps the snapshot in step with the `dark` class.
 */
let currentSnapshot: AppearanceSnapshot = SERVER_SNAPSHOT;

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = (): Appearance => {
    if (typeof window === 'undefined') {
        return 'system';
    }

    return (localStorage.getItem('appearance') as Appearance) || 'system';
};

const isDarkMode = (appearance: Appearance): boolean => {
    return appearance === 'dark' || (appearance === 'system' && prefersDark());
};

const applyTheme = (appearance: Appearance): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = isDarkMode(appearance);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

    const resolvedAppearance: ResolvedAppearance = isDark ? 'dark' : 'light';

    if (
        currentSnapshot.appearance !== appearance ||
        currentSnapshot.resolvedAppearance !== resolvedAppearance
    ) {
        currentSnapshot = { appearance, resolvedAppearance };
    }
};

const subscribe = (callback: () => void): (() => void) => {
    listeners.add(callback);

    return () => {
        listeners.delete(callback);
    };
};

const notify = (): void => listeners.forEach((listener) => listener());

const mediaQuery = (): MediaQueryList | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

/**
 * On "system", the OS flipping light/dark changes `resolvedAppearance` without
 * changing `appearance`. Re-apply the theme and notify so subscribers re-render
 * -- otherwise consumers of `resolvedAppearance` (the setup modal's QR-code
 * inversion) keep the stale value until something else re-renders them.
 */
const handleSystemThemeChange = (): void => {
    applyTheme(currentAppearance);
    notify();
};

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (!localStorage.getItem('appearance')) {
        localStorage.setItem('appearance', 'system');
        setCookie('appearance', 'system');
    }

    currentAppearance = getStoredAppearance();
    applyTheme(currentAppearance);

    // Set up system theme change listener
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const updateAppearance = (mode: Appearance): void => {
    currentAppearance = mode;

    // Store in localStorage for client-side persistence...
    localStorage.setItem('appearance', mode);

    // Store in cookie for SSR...
    setCookie('appearance', mode);

    applyTheme(mode);
    notify();
};

const getSnapshot = (): AppearanceSnapshot => currentSnapshot;

const getServerSnapshot = (): AppearanceSnapshot => SERVER_SNAPSHOT;

export function useAppearance(): UseAppearanceReturn {
    const { appearance, resolvedAppearance } = useSyncExternalStore(
        subscribe,
        getSnapshot,
        getServerSnapshot,
    );

    return { appearance, resolvedAppearance, updateAppearance } as const;
}
