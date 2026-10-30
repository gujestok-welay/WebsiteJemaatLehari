(() => {
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
