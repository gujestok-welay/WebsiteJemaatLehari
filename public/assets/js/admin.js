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
