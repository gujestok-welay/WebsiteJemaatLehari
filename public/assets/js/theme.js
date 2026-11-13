'use strict';

(() => {
    const storageKey = 'jemaat-leahari-theme';
    const colorScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const root = document.documentElement;
    let preference = 'system';

    try {
        const storedPreference = window.localStorage.getItem(storageKey);
        if (['light', 'dark', 'system'].includes(storedPreference)) {
            preference = storedPreference;
        } else if (storedPreference !== null) {
            console.warn('Preferensi tema tidak dikenal; menggunakan pengaturan perangkat.');
            window.localStorage.removeItem(storageKey);
        }
    } catch (error) {
        console.error('Preferensi tema tidak dapat dibaca dari penyimpanan browser.', error);
    }

    const applyTheme = () => {
        root.dataset.theme = preference === 'system'
            ? (colorScheme.matches ? 'dark' : 'light')
            : preference;
        root.dataset.themePreference = preference;
    };

    applyTheme();

    const updateControls = () => {
        const isDark = root.dataset.theme === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach((control) => {
            const nextTheme = isDark ? 'terang' : 'gelap';
            const label = `Aktifkan tema ${nextTheme}`;
            control.setAttribute('aria-checked', String(isDark));
            control.setAttribute('aria-label', label);
            control.setAttribute('title', label);
            control.querySelector('[data-theme-status]').textContent = `Tema ${isDark ? 'gelap' : 'terang'} aktif`;
        });
    };

    const initializeControls = () => {
        document.querySelectorAll('[data-theme-toggle]').forEach((control) => {
            control.addEventListener('click', () => {
                preference = root.dataset.theme === 'dark' ? 'light' : 'dark';
                try {
                    window.localStorage.setItem(storageKey, preference);
                } catch (error) {
                    console.error('Preferensi tema tidak dapat disimpan di browser ini.', error);
                }
                applyTheme();
                updateControls();
            });
        });
        updateControls();
    };

    document.addEventListener('DOMContentLoaded', initializeControls, { once: true });
    colorScheme.addEventListener('change', () => {
        if (preference === 'system') {
            applyTheme();
            updateControls();
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey) return;
        preference = ['light', 'dark', 'system'].includes(event.newValue)
            ? event.newValue
            : 'system';
        applyTheme();
        updateControls();
    });
})();
