import { onMounted, ref } from 'vue';

// Only allow light
type Appearance = 'light';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    // Always force light mode (remove any dark class)
    document.documentElement.classList.remove('dark');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Force light theme always
    updateTheme('light');
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
        if (savedAppearance) {
            appearance.value = savedAppearance;
        } else {
            appearance.value = 'light';
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
