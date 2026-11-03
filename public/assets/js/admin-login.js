(() => {
    const form = document.querySelector('.login-form');
    const submit = form?.querySelector('[type="submit"]');
    const spinner = submit?.querySelector('.spinner-border');
    const label = submit?.querySelector('.login-submit-label');
    const status = form?.querySelector('.login-status');

    if (form && submit && spinner && label && status) {
        form.addEventListener('submit', (event) => {
            if (submit.disabled) {
                event.preventDefault();
                return;
            }

            submit.disabled = true;
            spinner.hidden = false;
            label.textContent = 'Memproses…';
            status.textContent = 'Sedang memproses login.';
            form.setAttribute('aria-busy', 'true');
        });

        window.addEventListener('pageshow', () => {
            submit.disabled = false;
            spinner.hidden = true;
            label.textContent = 'Masuk';
            status.textContent = '';
            form.removeAttribute('aria-busy');
        });
    }

    const password = document.getElementById('password');
    const toggle = document.querySelector('.password-toggle');

    if (!password || !toggle) return;

    const updateVisibility = (visible) => {
        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        toggle.querySelector('.eye-open').toggleAttribute('hidden', visible);
        toggle.querySelector('.eye-closed').toggleAttribute('hidden', !visible);
    };

    updateVisibility(false);
    toggle.hidden = false;
    toggle.addEventListener('click', () => updateVisibility(password.type === 'password'));
    // A restored page must not expose a previously revealed password.
    window.addEventListener('pageshow', () => updateVisibility(false));
})();
