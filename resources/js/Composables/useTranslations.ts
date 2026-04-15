import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '../types';

/**
 * Resolves a dot-notation key (e.g. "personnel.title") against the
 * `translations` prop shared via HandleInertiaRequests. Supports ":param"
 * placeholders (Laravel-style), e.g. t('personnel.subtitle', { count: 11 }).
 *
 * Returns the key itself when not found — so missing keys are visually
 * obvious during development.
 */
export function useTranslations() {
    const page = usePage<PageProps>();

    function resolve(key: string): unknown {
        const segments = key.split('.');
        let node: unknown = page.props.translations;

        for (const segment of segments) {
            if (node && typeof node === 'object' && segment in (node as Record<string, unknown>)) {
                node = (node as Record<string, unknown>)[segment];
            } else {
                return undefined;
            }
        }
        return node;
    }

    const t = (key: string, replacements: Record<string, string | number> = {}): string => {
        const node = resolve(key);
        if (typeof node !== 'string') {
            return key;
        }

        return Object.entries(replacements).reduce(
            (acc, [name, value]) => acc.replace(new RegExp(`:${name}\\b`, 'g'), String(value)),
            node,
        );
    };

    /**
     * Resolves a translation key that points to an array/object — used for
     * structured content like codex items, dictionary terms, info goals, etc.
     */
    const tRaw = <T = unknown>(key: string, fallback: T): T => {
        const node = resolve(key);
        return (node === undefined ? fallback : node) as T;
    };

    return { t, tRaw };
}
