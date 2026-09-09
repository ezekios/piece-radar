const themeStorageKey = 'piece-radar-theme';
const colorSchemeQuery = window.matchMedia('(prefers-color-scheme: dark)');

const systemTheme = () => colorSchemeQuery.matches ? 'dark' : 'light';

const resolveTheme = () => {
    try {
        const storedTheme = localStorage.getItem(themeStorageKey);

        if (storedTheme === 'light' || storedTheme === 'dark') {
            return storedTheme;
        }

        return systemTheme();
    } catch (error) {
        return systemTheme();
    }
};

const applyTheme = (theme) => {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.dataset.theme = theme;
    document.documentElement.style.colorScheme = theme;
};

const updateThemeToggles = () => {
    const currentTheme = document.documentElement.dataset.theme || resolveTheme();
    const nextThemeLabel = currentTheme === 'dark' ? 'Activer le mode clair' : 'Activer le mode sombre';

    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
        toggle.setAttribute('aria-label', nextThemeLabel);
        toggle.setAttribute('title', nextThemeLabel);
    });
};

applyTheme(resolveTheme());
updateThemeToggles();

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-theme-toggle]');

    if (! toggle) {
        return;
    }

    const nextTheme = (document.documentElement.dataset.theme || resolveTheme()) === 'dark' ? 'light' : 'dark';

    try {
        localStorage.setItem(themeStorageKey, nextTheme);
    } catch (error) {
        // Theme persistence is a progressive enhancement; toggling still works without storage.
    }

    applyTheme(nextTheme);
    updateThemeToggles();
});

const handleSystemThemeChange = () => {
    try {
        if (localStorage.getItem(themeStorageKey)) {
            return;
        }
    } catch (error) {
        return;
    }

    applyTheme(resolveTheme());
    updateThemeToggles();
};

if (typeof colorSchemeQuery.addEventListener === 'function') {
    colorSchemeQuery.addEventListener('change', handleSystemThemeChange);
} else if (typeof colorSchemeQuery.addListener === 'function') {
    colorSchemeQuery.addListener(handleSystemThemeChange);
}
