import { ref, watchEffect } from 'vue';

const STORAGE_KEY = 'pult-dark-mode';

/**
 * Reactive dark-mode state backed by localStorage.
 * Toggles the `.dark` class on <html> — works with Tailwind 4's
 * `@variant dark (&:where(.dark, .dark *))` configured in app.css.
 *
 * Shared singleton across all components that import it.
 */
const isDark = ref(false);
let initialised = false;

export function useDarkMode() {
    if (!initialised && typeof window !== 'undefined') {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored !== null) {
            isDark.value = stored === '1';
        } else {
            // Fall back to OS preference on first visit
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        initialised = true;
    }

    watchEffect(() => {
        if (typeof document === 'undefined') return;
        document.documentElement.classList.toggle('dark', isDark.value);
        localStorage.setItem(STORAGE_KEY, isDark.value ? '1' : '0');
    });

    function toggle() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggle };
}
