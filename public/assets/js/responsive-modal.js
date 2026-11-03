(function () {
    var MOBILE_QUERY = window.matchMedia('(max-width: 767.98px)');
    var maps = {
        desktop: {
            root: 'rm modal fade',
            dialog: 'modal-dialog modal-dialog-centered',
            'content-wrap': 'rm-content-wrap',
            content: 'modal-content',
            header: 'modal-header',
            title: 'modal-title',
            description: 'text-body-secondary small mb-0',
            body: 'modal-body',
            footer: 'modal-footer',
        },
        mobile: {
            root: 'rm offcanvas offcanvas-bottom rounded-top-4',
            dialog: 'rm-contents',
            'content-wrap': 'rm-contents',
            content: 'rm-mobile-content',
            header: 'offcanvas-header',
            title: 'offcanvas-title',
            description: 'text-body-secondary small mb-0',
            body: 'offcanvas-body',
            footer: 'rm-mobile-footer',
        },
    };
    var states = new WeakMap();

    function getState(root) {
        var state = states.get(root);
        if (!state) {
            state = {
                instance: null,
                mode: null,
                pendingMode: null,
                reopen: false,
                trigger: null,
            };
            states.set(root, state);
        }
        return state;
    }

    function setProps(node, json) {
        var previous = node._rmAppliedProps || [];
        previous.forEach(function (name) {
            node.removeAttribute(name);
        });

        var next;
        try {
            next = JSON.parse(json || '{}');
        } catch (error) {
            console.error('Responsive modal received invalid attributes.', error);
            next = {};
        }

        Object.keys(next).forEach(function (name) {
            var value = next[name];
            if (value === false || value === null) return;
            node.setAttribute(name, value === true ? '' : String(value));
        });
        node._rmAppliedProps = Object.keys(next);
    }

    function apply(root, mode) {
        var classes = maps[mode];
        var nodes = [root].concat(Array.prototype.slice.call(root.querySelectorAll('[data-rm-part]')));

        root.style.removeProperty('display');
        root.removeAttribute('aria-modal');
        root.removeAttribute('role');
        root.setAttribute('aria-hidden', 'true');

        nodes.forEach(function (node) {
            var part = node.dataset.rmPart;
            if (!part || !classes[part]) return;

            node.className = (classes[part] + ' ' + (node.dataset.rmClass || '')).trim();
            setProps(node, mode === 'mobile' ? node.dataset.rmMobile : node.dataset.rmDesktop);
        });

        getState(root).mode = mode;
    }

    function createInstance(root, state) {
        return state.mode === 'mobile'
            ? window.bootstrap.Offcanvas.getOrCreateInstance(root)
            : window.bootstrap.Modal.getOrCreateInstance(root);
    }

    function emit(root, open) {
        root.dispatchEvent(new CustomEvent('rm:openchange', {
            bubbles: true,
            detail: { open: open },
        }));
    }

    function finishClose(root, state) {
        if (state.instance) state.instance.dispose();
        state.instance = null;
        state.mode = null;

        if (state.pendingMode) {
            var nextMode = state.pendingMode;
            var reopen = state.reopen;
            state.pendingMode = null;
            state.reopen = false;
            apply(root, nextMode);

            if (reopen) {
                state.instance = createInstance(root, state);
                state.instance.show();
                return;
            }
        }

        emit(root, false);
        if (state.trigger && document.contains(state.trigger)) {
            state.trigger.focus();
        }
        state.trigger = null;
    }

    function bind(root) {
        var state = getState(root);
        if (state.bound) return;
        state.bound = true;

        apply(root, MOBILE_QUERY.matches ? 'mobile' : 'desktop');

        root.addEventListener('shown.bs.modal', function () {
            if (state.pendingMode) {
                state.instance.hide();
                return;
            }
            emit(root, true);
        });
        root.addEventListener('shown.bs.offcanvas', function () {
            if (state.pendingMode) {
                state.instance.hide();
                return;
            }
            emit(root, true);
        });
        root.addEventListener('hidden.bs.modal', function () {
            finishClose(root, state);
        });
        root.addEventListener('hidden.bs.offcanvas', function () {
            finishClose(root, state);
        });

        if (root.hasAttribute('data-rm-default-open')) {
            state.instance = createInstance(root, state);
            state.instance.show();
        }
    }

    function init(container) {
        var scope = container || document;
        var roots = [];

        if (scope.matches && scope.matches('[data-rm]')) {
            roots.push(scope);
        }
        roots = roots.concat(Array.prototype.slice.call(scope.querySelectorAll('[data-rm]')));
        roots.forEach(bind);
    }

    function open(root, trigger) {
        var state = getState(root);
        state.trigger = trigger || state.trigger;

        if (state.pendingMode) {
            state.reopen = true;
            return;
        }

        var mode = MOBILE_QUERY.matches ? 'mobile' : 'desktop';
        if (state.mode !== mode) {
            if (state.instance) state.instance.dispose();
            state.instance = null;
            apply(root, mode);
        }

        state.instance = createInstance(root, state);
        state.instance.show();
    }

    function close(root) {
        var state = getState(root);
        state.reopen = false;
        if (state.instance) state.instance.hide();
    }

    function changeMode() {
        document.querySelectorAll('[data-rm]').forEach(function (root) {
            var state = getState(root);
            var mode = MOBILE_QUERY.matches ? 'mobile' : 'desktop';
            if (state.mode === mode && !state.pendingMode) return;

            if (root.classList.contains('show')) {
                state.pendingMode = mode;
                state.reopen = true;
                if (state.instance) state.instance.hide();
                return;
            }

            if (state.instance) state.instance.dispose();
            state.instance = null;
            state.pendingMode = null;
            state.reopen = false;
            apply(root, mode);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.bootstrap || !window.bootstrap.Modal || !window.bootstrap.Offcanvas) {
            console.error('Responsive modal requires the Bootstrap 5 JavaScript bundle.');
            return;
        }

        init(document);

        document.addEventListener('click', function (event) {
            var target = event.target instanceof Element ? event.target : null;
            if (!target) return;

            var openButton = target.closest('[data-rm-open]');
            if (openButton) {
                var selector = openButton.getAttribute('data-rm-open');
                var root = selector ? document.querySelector(selector) : null;
                if (root) {
                    event.preventDefault();
                    open(root, openButton);
                }
                return;
            }

            var closeButton = target.closest('[data-rm-close]');
            if (closeButton) {
                var modalRoot = closeButton.closest('[data-rm]');
                if (modalRoot) close(modalRoot);
            }
        });

        if (typeof MOBILE_QUERY.addEventListener === 'function') {
            MOBILE_QUERY.addEventListener('change', changeMode);
        } else {
            MOBILE_QUERY.addListener(changeMode);
        }
    });

    window.ResponsiveModal = {
        isMobile: function () {
            return MOBILE_QUERY.matches;
        },
        init: init,
        open: function (selector) {
            var root = document.querySelector(selector);
            if (root) open(root, null);
        },
        close: function (selector) {
            var root = document.querySelector(selector);
            if (root) close(root);
        },
    };
})();
