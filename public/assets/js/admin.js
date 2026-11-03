(function () {
    var sidebar = document.getElementById('adminSidebar');
    var toggle = document.getElementById('sidebarToggle');
    var backdrop = document.getElementById('sidebarBackdrop');

    if (!sidebar || !toggle || !backdrop) return;

    var desktopBreakpoint = window.matchMedia('(min-width: 992px)');

    function syncDesktopExpanded() {
        var expanded = desktopBreakpoint.matches
            && (sidebar.matches(':hover') || sidebar.contains(document.activeElement));

        document.body.classList.toggle('sidebar-expanded', expanded);
    }

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        backdrop.classList.toggle('is-visible', open);
        backdrop.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('sidebar-open', open);

        if (open) {
            var firstLink = sidebar.querySelector('a');
            if (firstLink) firstLink.focus();
        } else {
            toggle.focus();
        }
    }

    sidebar.addEventListener('mouseenter', syncDesktopExpanded);
    sidebar.addEventListener('mouseleave', syncDesktopExpanded);
    sidebar.addEventListener('focusin', syncDesktopExpanded);
    sidebar.addEventListener('focusout', function () {
        window.requestAnimationFrame(syncDesktopExpanded);
    });

    toggle.addEventListener('click', function () {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    backdrop.addEventListener('click', function () {
        setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
        }
    });

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) setOpen(false);
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992 && toggle.getAttribute('aria-expanded') === 'true') {
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-visible');
            backdrop.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('sidebar-open');
        }
        syncDesktopExpanded();
    });
})();

(function () {
    var main = document.getElementById('main-content');
    var topbarTitle = document.querySelector('.topbar-title');

    if (!main || !topbarTitle || !window.fetch || !window.AbortController || !window.history.pushState) return;

    var requestController = null;
    var pageTitleSuffix = ' · Media Center Jemaat Leahari';

    function isPartialRoute(url) {
        var path = url.pathname.replace(/\/+$/, '');
        return /\/admin\/(?:dashboard|berita|jadwal(?:\/(?:create|edit\/\d+))?|profil|statistik)$/.test(path);
    }

    function updateNavigation(path) {
        var sidebar = document.getElementById('adminSidebar');
        if (!sidebar) return;

        var currentPath = path.replace(/\/+$/, '');
        sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
            var linkPath = new URL(link.href, window.location.href).pathname.replace(/\/+$/, '');
            var active = linkPath === currentPath || currentPath.indexOf(linkPath + '/') === 0;

            link.classList.toggle('is-active', active);
            if (active) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    async function navigate(url, addHistoryEntry) {
        if (requestController) requestController.abort();
        requestController = new AbortController();
        main.setAttribute('aria-busy', 'true');

        try {
            var response = await window.fetch(url.href, {
                credentials: 'same-origin',
                headers: { 'X-Partial': '1' },
                signal: requestController.signal
            });

            if (!response.ok || response.headers.get('X-Partial-Response') !== '1') {
                window.location.assign(url.href);
                return;
            }

            var html = await response.text();
            var encodedTitle = response.headers.get('X-Page-Title');
            if (!encodedTitle) {
                window.location.assign(url.href);
                return;
            }
            var title = decodeURIComponent(encodedTitle || '');

            main.innerHTML = html;
            main.removeAttribute('aria-busy');
            topbarTitle.textContent = title;
            document.title = title + pageTitleSuffix;
            updateNavigation(url.pathname);

            if (addHistoryEntry) {
                window.history.pushState({ adminPartial: true }, '', url.href);
            }

            window.scrollTo(0, 0);
            var heading = main.querySelector('h1');
            if (heading) {
                heading.setAttribute('tabindex', '-1');
                heading.focus({ preventScroll: true });
            }
        } catch (error) {
            main.removeAttribute('aria-busy');
            if (error.name === 'AbortError') return;

            console.error('Navigasi admin parsial gagal; halaman akan dimuat ulang.', error);
            window.location.assign(url.href);
        }
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        var link = event.target.closest('a[href]');
        if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-rm-open')) return;

        var url = new URL(link.href, window.location.href);
        if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return;
        if (url.origin !== window.location.origin || !isPartialRoute(url)) return;

        event.preventDefault();
        navigate(url, true);
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (form.method.toLowerCase() !== 'get') return;

        var url = new URL(form.action, window.location.href);
        if (url.origin !== window.location.origin || !isPartialRoute(url)) return;

        var params = new URLSearchParams(new FormData(form));
        url.search = params.toString();
        event.preventDefault();
        navigate(url, true);
    });

    window.addEventListener('popstate', function () {
        var url = new URL(window.location.href);
        if (!isPartialRoute(url)) {
            window.location.reload();
            return;
        }

        navigate(url, false);
    });
})();
