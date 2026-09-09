<script>
    (() => {
        const storageKey = 'piece-radar-theme';
        const root = document.documentElement;

        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

        try {
            const storedTheme = localStorage.getItem(storageKey);
            const theme = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : systemTheme;

            root.classList.toggle('dark', theme === 'dark');
            root.dataset.theme = theme;
            root.style.colorScheme = theme;
        } catch (error) {
            root.classList.toggle('dark', systemTheme === 'dark');
            root.dataset.theme = systemTheme;
            root.style.colorScheme = systemTheme;
        }
    })();
</script>
