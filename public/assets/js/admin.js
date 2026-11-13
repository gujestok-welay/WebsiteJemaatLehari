(function () {
    var modalSelector = '#news-create-modal';
    var maxCoverBytes = 10 * 1024 * 1024;
    var allowedCoverTypes = ['image/jpeg', 'image/png', 'image/webp'];

    function getNewsForm() {
        var modal = document.querySelector(modalSelector);
        return modal ? modal.querySelector('[data-news-form]') : null;
    }

    function resizeNewsTitle(field) {
        if (!(field instanceof HTMLTextAreaElement)) return;

        field.style.height = 'auto';
        field.style.height = field.scrollHeight + 'px';
    }

    function resetNewsForm() {
        var form = getNewsForm();
        if (!form) return;

        form.reset();
        form.querySelector('[name="id"]').value = '';
        form.querySelector('[name="title"]').value = '';
        form.querySelector('[name="slug"]').value = '';
        form.querySelector('[name="summary"]').value = '';
        form.querySelector('[name="content"]').value = '';
        form.querySelector('[name="status"]').value = '';
        resizeNewsTitle(form.querySelector('[data-news-title]'));
        var coverInput = form.querySelector('[name="cover_image"]');
        coverInput.value = '';
        coverInput.setCustomValidity('');
        var fileError = form.querySelector('[data-news-file-error]');
        fileError.textContent = '';
        fileError.hidden = true;
        form.querySelector('[data-news-current-cover]').hidden = true;

        var title = document.getElementById('news-create-title');
        var description = document.getElementById('news-create-description');
        var submit = form.querySelector('[data-news-submit]');
        var error = form.querySelector('[data-news-error]');

        if (submit) submit.disabled = false;
        if (title) title.textContent = 'Tambah Berita';
        if (description) description.textContent = 'Lengkapi informasi berita.';
        if (submit) submit.textContent = 'Simpan Berita';
        if (error) error.remove();
    }

    function populateNewsForm(trigger) {
        var form = getNewsForm();
        if (!form) return;

        var news;
        try {
            news = JSON.parse(trigger.getAttribute('data-news-edit') || '');
        } catch (error) {
            console.error('Data berita untuk modal edit tidak valid.', error);
            resetNewsForm();
            return;
        }

        form.querySelector('[name="id"]').value = String(news.id || '');
        form.querySelector('[name="title"]').value = news.title || '';
        resizeNewsTitle(form.querySelector('[data-news-title]'));
        form.querySelector('[name="slug"]').value = news.slug || '';
        form.querySelector('[name="summary"]').value = news.summary || '';
        form.querySelector('[name="content"]').value = news.content || '';
        form.querySelector('[name="status"]').value = news.status || '';
        var coverInput = form.querySelector('[name="cover_image"]');
        coverInput.value = '';
        coverInput.setCustomValidity('');
        var fileError = form.querySelector('[data-news-file-error]');
        fileError.textContent = '';
        fileError.hidden = true;
        form.querySelector('[data-news-current-cover]').hidden = !news.cover_image;

        var title = document.getElementById('news-create-title');
        var description = document.getElementById('news-create-description');
        var submit = form.querySelector('[data-news-submit]');
        var error = form.querySelector('[data-news-error]');

        if (title) title.textContent = 'Edit Berita';
        if (description) description.textContent = 'Perbarui informasi berita.';
        if (submit) submit.textContent = 'Simpan Perubahan';
        if (error) error.remove();
    }

    function validateCoverFile(form) {
        var input = form.querySelector('[name="cover_image"]');
        var error = form.querySelector('[data-news-file-error]');
        var file = input.files && input.files[0];
        var message = '';

        if (file && file.size > maxCoverBytes) {
            message = 'Ukuran gambar maksimal 10 MB.';
        } else if (file && file.type && allowedCoverTypes.indexOf(file.type) === -1) {
            message = 'Format gambar tidak diizinkan. Gunakan JPG, PNG, atau WEBP.';
        }

        input.setCustomValidity(message);
        error.textContent = message;
        error.hidden = message === '';

        return message === '';
    }

    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        var editTrigger = target.closest('[data-news-edit]');
        if (editTrigger) {
            populateNewsForm(editTrigger);
            return;
        }

        var openTrigger = target.closest('[data-rm-open="' + modalSelector + '"]');
        if (openTrigger) resetNewsForm();
    }, true);

    document.addEventListener('rm:openchange', function (event) {
        if (event.target.id === 'news-create-modal') {
            if (!event.detail.open) {
                resetNewsForm();
            } else {
                resizeNewsTitle(event.target.querySelector('[data-news-title]'));
            }
        }
    });

    document.addEventListener('input', function (event) {
        if (event.target instanceof HTMLTextAreaElement && event.target.hasAttribute('data-news-title')) {
            resizeNewsTitle(event.target);
        }
    });

    window.addEventListener('resize', function () {
        var form = getNewsForm();
        var title = form && form.querySelector('[data-news-title]');
        if (title && title.getClientRects().length > 0) resizeNewsTitle(title);
    });

    document.addEventListener('change', function (event) {
        var input = event.target;
        if (input instanceof HTMLInputElement && input.name === 'cover_image' && input.form.matches('[data-news-form]')) {
            validateCoverFile(input.form);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-news-form]')) return;

        if (!validateCoverFile(form)) {
            event.preventDefault();
            form.querySelector('[name="cover_image"]').reportValidity();
            return;
        }

        var submit = form.querySelector('[data-news-submit]');
        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Menyimpan...';
        }
    });
})();

(function () {
    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        var addButton = target.closest('[data-add-item]');
        if (addButton) {
            var list = addButton.closest('[data-content-list]');
            var template = list.querySelector('[data-list-template]');
            var index = Number(list.dataset.nextIndex);
            var item = template.content.cloneNode(true);

            item.querySelectorAll('[name], [id], label[for]').forEach(function (element) {
                ['name', 'id', 'for'].forEach(function (attribute) {
                    if (element.hasAttribute(attribute)) {
                        element.setAttribute(attribute, element.getAttribute(attribute).replace(/__INDEX__/g, String(index)));
                    }
                });
            });
            item.querySelectorAll('input:not([type="hidden"]), textarea').forEach(function (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;
                } else {
                    field.value = '';
                }
            });
            item.querySelectorAll('select').forEach(function (field) {
                field.selectedIndex = 0;
            });

            list.dataset.nextIndex = String(index + 1);
            list.querySelector('.profile-content-list-items').append(item);
            return;
        }

        var removeButton = target.closest('[data-remove-item]');
        if (removeButton) {
            removeButton.closest('[data-content-item]').remove();
        }
    });
})();

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
    var scheduleRequestController = null;
    var lastScheduleUrl = new URL(window.location.href);
    var pageTitleSuffix = ' · Media Center Jemaat Leahari';

    function isPartialRoute(url) {
        var path = url.pathname.replace(/\/+$/, '');
        return /\/admin\/(?:dashboard|berita|jadwal(?:\/(?:create|edit\/\d+))?|profil|statistik)$/.test(path);
    }

    function isScheduleRoute(url) {
        return /\/admin\/jadwal\/?$/.test(url.pathname);
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
        if (scheduleRequestController) scheduleRequestController.abort();
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

            var destination = new URL(response.url, url.href);
            if (destination.origin !== window.location.origin || !isPartialRoute(destination)) {
                window.location.assign(destination.href);
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
            if (window.ResponsiveModal) window.ResponsiveModal.init(main);
            main.removeAttribute('aria-busy');
            topbarTitle.textContent = title;
            document.title = title + pageTitleSuffix;
            updateNavigation(destination.pathname);

            if (addHistoryEntry) {
                window.history.pushState({ adminPartial: true }, '', destination.href);
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

    async function navigateSchedule(url, addHistoryEntry, focus) {
        var schedulePage = main.querySelector('[data-schedule-page]');
        var controls = schedulePage && schedulePage.querySelector('[data-schedule-controls]');
        var toolbar = schedulePage && schedulePage.querySelector('[data-schedule-toolbar]');
        var results = schedulePage && schedulePage.querySelector('[data-schedule-results]');
        if (!schedulePage || !controls || !toolbar || !results) {
            navigate(url, addHistoryEntry);
            return;
        }

        if (requestController) requestController.abort();
        if (scheduleRequestController) scheduleRequestController.abort();
        scheduleRequestController = new AbortController();
        lastScheduleUrl = new URL(url.href);
        var request = scheduleRequestController;
        var errorMessage = schedulePage.querySelector('[data-schedule-error]');
        var loadingStatus = schedulePage.querySelector('[data-schedule-loading-status]');
        [controls, toolbar, results].forEach(function (region) {
            region.setAttribute('aria-busy', 'true');
        });
        if (errorMessage) errorMessage.hidden = true;
        if (loadingStatus) loadingStatus.textContent = 'Memuat jadwal...';

        try {
            var response = await window.fetch(url.href, {
                credentials: 'same-origin',
                headers: { 'X-Schedule-Partial': '1' },
                signal: request.signal
            });
            var destination = new URL(response.url, url.href);
            if (destination.origin !== window.location.origin || !isScheduleRoute(destination)) {
                window.location.assign(destination.href);
                return;
            }
            if (!response.ok || response.headers.get('X-Schedule-Partial-Response') !== '1') {
                throw new Error('Respons filter jadwal tidak dapat digunakan.');
            }

            var html = await response.text();
            if (request.signal.aborted) return;
            var documentFragment = new DOMParser().parseFromString(html, 'text/html');
            var updatedControls = documentFragment.querySelector('[data-schedule-controls]');
            var updatedToolbar = documentFragment.querySelector('[data-schedule-toolbar]');
            var updatedResults = documentFragment.querySelector('[data-schedule-results]');
            if (!updatedControls || !updatedToolbar || !updatedResults) {
                throw new Error('Respons filter jadwal tidak memuat bagian yang diharapkan.');
            }

            controls.replaceWith(updatedControls);
            toolbar.replaceWith(updatedToolbar);
            results.replaceWith(updatedResults);
            if (errorMessage) errorMessage.hidden = true;
            if (loadingStatus) {
                var resultCount = updatedResults.querySelector('.schedule-result-count');
                loadingStatus.textContent = resultCount ? resultCount.textContent.trim() : 'Jadwal diperbarui.';
            }

            if (addHistoryEntry && destination.href !== window.location.href) {
                window.history.pushState({ adminPartial: true, adminSchedulePartial: true }, '', destination.href);
            }
            if (!addHistoryEntry && destination.href !== window.location.href) {
                window.history.replaceState(window.history.state, '', destination.href);
            }

            if (focus && focus.type === 'search') {
                var searchInput = updatedToolbar.querySelector('input[name="q"]');
                if (searchInput) {
                    searchInput.focus({ preventScroll: true });
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                }
            } else if (focus && focus.href) {
                var focusRegion = focus.region === 'controls'
                    ? updatedControls
                    : (focus.region === 'toolbar' ? updatedToolbar : updatedResults);
                var focusedLink = Array.prototype.find.call(
                    focusRegion.querySelectorAll('a[href]'),
                    function (link) { return new URL(link.href, window.location.href).href === destination.href; }
                );
                if (focusedLink) focusedLink.focus({ preventScroll: true });
                else {
                    var resultCount = updatedResults.querySelector('.schedule-result-count');
                    if (resultCount) resultCount.focus({ preventScroll: true });
                }
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('Pembaruan filter jadwal gagal.', error);
            if (errorMessage) errorMessage.hidden = false;
            if (loadingStatus) loadingStatus.textContent = 'Filter gagal dimuat. Jadwal yang tampil belum berubah.';
        } finally {
            if (!request.signal.aborted) {
                [controls, toolbar, results].forEach(function (region) {
                    if (region.isConnected) region.removeAttribute('aria-busy');
                });
            }
        }
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        var retryButton = target.closest('[data-schedule-retry]');
        if (retryButton) {
            event.preventDefault();
            navigateSchedule(lastScheduleUrl, lastScheduleUrl.href !== window.location.href);
            return;
        }

        var link = target.closest('a[href]');
        if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-rm-open')) return;

        var url = new URL(link.href, window.location.href);
        if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return;
        if (isScheduleRoute(url) && link.closest('[data-schedule-controls], [data-schedule-toolbar], [data-schedule-filter-link]')) {
            event.preventDefault();
            if (url.href !== window.location.href) {
                var focusRegion = link.closest('[data-schedule-controls]')
                    ? 'controls'
                    : (link.closest('[data-schedule-toolbar]') ? 'toolbar' : 'results');
                navigateSchedule(url, true, { type: 'link', href: link.href, region: focusRegion });
            }
            return;
        }
        if (url.origin !== window.location.origin || !isPartialRoute(url)) return;

        event.preventDefault();
        navigate(url, true);
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (form.method.toLowerCase() !== 'get') return;

        var url = new URL(form.action, window.location.href);
        if (url.origin === window.location.origin && isScheduleRoute(url) && form.closest('[data-schedule-toolbar]')) {
            var searchParams = new URLSearchParams(new FormData(form));
            url.search = searchParams.toString();
            event.preventDefault();
            navigateSchedule(url, true, { type: 'search' });
            return;
        }
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

        if (isScheduleRoute(url) && main.querySelector('[data-schedule-page]')) {
            navigateSchedule(url, false);
            return;
        }

        navigate(url, false);
    });
})();

(function () {
    var maxServiceMapBytes = 5 * 1024 * 1024;
    var serviceMapTypes = ['image/jpeg', 'image/png', 'image/webp'];

    function getModal(form) {
        return form.closest('[data-rm]');
    }

    function setFormMessage(form, message) {
        var modal = getModal(form);
        var alert = form.querySelector('[data-profile-form-message]')
            || (modal && modal.querySelector('[data-profile-form-message]'));
        if (!alert) return;

        alert.textContent = message;
        alert.hidden = message === '';
    }

    function clearFieldErrors(form) {
        form.querySelectorAll('[data-profile-inline-error]').forEach(function (error) {
            error.remove();
        });
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (field) {
            field.removeAttribute('aria-invalid');
        });
    }

    function showFieldErrors(form, errors) {
        Object.keys(errors || {}).forEach(function (name) {
            var field = Array.prototype.find.call(form.elements, function (control) {
                return control.name === name || control.name.endsWith('[' + name + ']');
            });
            if (!field) return;

            var wrapper = field.closest('.profile-field') || field.parentElement;
            if (!wrapper) return;

            field.setAttribute('aria-invalid', 'true');
            var message = document.createElement('small');
            message.className = 'profile-field-error';
            message.dataset.profileInlineError = '';
            message.setAttribute('role', 'alert');
            message.textContent = errors[name];
            wrapper.append(message);
        });
    }

    function updateCsrf(data) {
        if (!data || !data.csrf || !data.csrf.name || !data.csrf.hash) return;

        document.querySelectorAll('input[type="hidden"]').forEach(function (field) {
            if (field.name === data.csrf.name) field.value = data.csrf.hash;
        });
    }

    function formatSummaryValue(name, value) {
        if (Array.isArray(value)) return value.join(', ');
        if (['householdCount', 'totalJiwa'].indexOf(name) !== -1 && typeof value === 'number') {
            return new Intl.NumberFormat('id-ID').format(value);
        }
        return String(value);
    }

    function updateSummary(summary) {
        Object.keys(summary || {}).forEach(function (name) {
            document.querySelectorAll('[data-profile-value="' + name + '"]').forEach(function (node) {
                node.textContent = formatSummaryValue(name, summary[name]);
            });
        });

        if (summary && (summary.serviceMapUrl || summary.serviceMapAlt)) {
            document.querySelectorAll('[data-profile-map-image], [data-profile-map-modal-image]').forEach(function (image) {
                if (summary.serviceMapUrl) image.src = summary.serviceMapUrl;
                if (summary.serviceMapAlt) image.alt = summary.serviceMapAlt;
            });
        }
    }

    function announce(message) {
        var liveRegion = document.querySelector('[data-profile-live]');
        if (liveRegion) liveRegion.textContent = message;
    }

    function validateServiceMap(form) {
        var fileInput = form.querySelector('[name="service_map"]');
        var fileError = form.querySelector('[data-profile-file-error]');
        var file = fileInput && fileInput.files ? fileInput.files[0] : null;
        var message = '';

        if (file && file.size > maxServiceMapBytes) {
            message = 'Ukuran peta maksimal 5 MB.';
        } else if (file && file.type && serviceMapTypes.indexOf(file.type) === -1) {
            message = 'Format peta tidak valid. Gunakan JPG, PNG, atau WEBP.';
        }

        if (fileInput) {
            fileInput.setCustomValidity(message);
            if (message) fileInput.setAttribute('aria-invalid', 'true');
            else fileInput.removeAttribute('aria-invalid');
        }
        if (fileError) {
            fileError.textContent = message;
            fileError.hidden = message === '';
        }

        return message === '';
    }

    function updateCensusCalculation(form) {
        var total = 0;
        form.querySelectorAll('input[name^="census[age_rows]"]').forEach(function (input) {
            if (!input.name.endsWith('[male]') && !input.name.endsWith('[female]')) return;
            var value = Number.parseInt(input.value, 10);
            if (Number.isFinite(value) && value > 0) total += value;
        });

        var output = form.querySelector('[data-profile-census-total]');
        if (output) output.textContent = new Intl.NumberFormat('id-ID').format(total);
    }

    function setCensusStep(form, requestedIndex) {
        var panels = Array.prototype.slice.call(form.querySelectorAll('[data-census-step-panel]'));
        if (panels.length === 0) return;

        var activeIndex = Math.max(0, Math.min(requestedIndex, panels.length - 1));
        panels.forEach(function (panel, index) {
            panel.hidden = index !== activeIndex;
        });

        var steps = Array.prototype.slice.call(form.querySelectorAll('[data-census-step-target]'));
        steps.forEach(function (step, index) {
            var item = step.closest('li');
            if (index === activeIndex) {
                step.setAttribute('aria-current', 'step');
            } else {
                step.removeAttribute('aria-current');
            }
            if (item) item.classList.toggle('is-complete', index < activeIndex);
        });

        var previous = form.querySelector('[data-census-step-previous]');
        var next = form.querySelector('[data-census-step-next]');
        var count = form.querySelector('[data-census-step-count]');
        var navigation = form.querySelector('[data-census-step-nav]');
        var controls = form.querySelector('[data-census-step-controls]');
        if (previous) previous.disabled = activeIndex === 0;
        if (next) next.disabled = activeIndex === panels.length - 1;
        if (count) count.textContent = 'Bagian ' + (activeIndex + 1) + ' dari ' + panels.length;
        if (navigation) navigation.hidden = false;
        if (controls) controls.hidden = false;
        form.classList.add('is-census-enhanced');
        form.dataset.censusCurrentStep = String(activeIndex);
    }

    function setBusy(form, busy) {
        form.setAttribute('aria-busy', busy ? 'true' : 'false');
        var submit = form.querySelector('[data-profile-submit]');
        if (!submit) return;

        if (busy) {
            submit.dataset.originalText = submit.textContent;
            submit.textContent = 'Menyimpan...';
            submit.disabled = true;
        } else {
            submit.textContent = submit.dataset.originalText || submit.textContent;
            submit.disabled = false;
        }
    }

    document.querySelectorAll('form[data-profile-census-form]').forEach(function (form) {
        setCensusStep(form, 0);
    });

    document.addEventListener('input', function (event) {
        var field = event.target;
        var form = field instanceof HTMLElement ? field.closest('form[data-profile-ajax-form]') : null;
        if (!form) return;

        form.dataset.profileDirty = 'true';
        clearFieldErrors(form);
        setFormMessage(form, '');
        if (form.matches('[data-profile-census-form]')) updateCensusCalculation(form);
    });

    document.addEventListener('change', function (event) {
        var field = event.target;
        if (field instanceof Element && field.matches('[data-profile-period-select]')) {
            var url = new URL(window.location.href);
            url.searchParams.set('period_id', field.value);
            url.searchParams.delete('edit_member');
            window.location.assign(url.href);
            return;
        }

        var form = field instanceof HTMLElement ? field.closest('form[data-profile-ajax-form]') : null;
        if (!form) return;

        form.dataset.profileDirty = 'true';
        clearFieldErrors(form);
        setFormMessage(form, '');

        if (form.matches('[data-profile-map-form]')) {
            if (validateServiceMap(form) && field.name === 'service_map' && field.files && field.files[0]) {
                var reader = new FileReader();
                reader.onload = function () {
                    var image = form.querySelector('[data-profile-map-modal-image]');
                    if (image && typeof reader.result === 'string') image.src = reader.result;
                };
                reader.readAsDataURL(field.files[0]);
            }
        }
        if (form.matches('[data-profile-census-form]')) updateCensusCalculation(form);

    });

    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        var censusStepControl = target.closest('[data-census-step-target], [data-census-step-next], [data-census-step-previous]');
        if (censusStepControl) {
            var censusForm = censusStepControl.closest('form[data-profile-census-form]');
            if (censusForm) {
                var panels = Array.prototype.slice.call(censusForm.querySelectorAll('[data-census-step-panel]'));
                var currentIndex = Number.parseInt(censusForm.dataset.censusCurrentStep, 10) || 0;
                var targetIndex = currentIndex;

                if (censusStepControl.hasAttribute('data-census-step-target')) {
                    targetIndex = panels.findIndex(function (panel) {
                        return panel.id === censusStepControl.dataset.censusStepTarget;
                    });
                } else if (censusStepControl.hasAttribute('data-census-step-next')) {
                    targetIndex += 1;
                } else {
                    targetIndex -= 1;
                }

                setCensusStep(censusForm, targetIndex);
            }
            return;
        }

        var addOrRemove = target.closest('[data-add-item], [data-remove-item]');
        if (addOrRemove) {
            var listForm = addOrRemove.closest('form[data-profile-ajax-form]');
            if (listForm) {
                listForm.dataset.profileDirty = 'true';
                if (listForm.matches('[data-profile-census-form]')) {
                    window.requestAnimationFrame(function () { updateCensusCalculation(listForm); });
                }
            }
        }

        var editPeriod = target.closest('[data-edit-period]');
        if (editPeriod) {
            try {
                var period = JSON.parse(editPeriod.dataset.period || '');
                var periodForm = document.querySelector('[data-profile-period-id]').form;
                periodForm.querySelector('[name="id"]').value = String(period.id || 0);
                periodForm.querySelector('[name="period_name"]').value = period.period_name || '';
                periodForm.querySelector('[name="start_date"]').value = period.start_date || '';
                periodForm.querySelector('[name="end_date"]').value = period.end_date || '';
                periodForm.querySelector('[name="is_active"]').value = String(period.is_active || 0);
                periodForm.dataset.profileDirty = 'false';
                periodForm.querySelector('[data-profile-submit]').textContent = 'Simpan perubahan periode';
            } catch (error) {
                console.error('Data periode untuk form edit tidak valid.', error);
            }
        }

        if (target.closest('[data-edit-period-reset]')) {
            var periodResetForm = target.closest('form');
            periodResetForm.reset();
            periodResetForm.querySelector('[name="id"]').value = '0';
            periodResetForm.querySelector('[name="period_name"]').value = '';
            periodResetForm.querySelector('[name="start_date"]').value = '';
            periodResetForm.querySelector('[name="end_date"]').value = '';
            periodResetForm.querySelector('[name="is_active"]').value = '0';
            periodResetForm.dataset.profileDirty = 'false';
            periodResetForm.querySelector('[data-profile-submit]').textContent = 'Simpan periode';
        }

        var editMember = target.closest('[data-edit-member]');
        if (editMember) {
            var memberForm = document.getElementById('member-form');
            try {
                if (!memberForm) throw new Error('Form anggota tidak ditemukan.');
                var memberDetails = editMember.closest('details');
                if (memberDetails) memberDetails.open = true;
                var member = JSON.parse(editMember.dataset.member || '');
                memberForm.querySelector('[name="id"]').value = String(member.id || 0);
                memberForm.querySelector('[name="periode_id"]').value = String(member.periode_id || 0);
                memberForm.querySelector('[name="name"]').value = member.name || '';
                memberForm.querySelector('[name="position"]').value = member.position || '';
                memberForm.dataset.profileDirty = 'false';
                var memberSaveButton = memberForm.querySelector('[data-profile-submit]');
                memberSaveButton.classList.add('is-icon-only');
                memberSaveButton.querySelector('.profile-submit-label').textContent = 'Simpan perubahan anggota';
                var photo = memberForm.querySelector('.profile-member-photo-preview');
                if (!photo && member.photo_path) {
                    photo = document.createElement('img');
                    photo.className = 'profile-member-photo profile-member-photo-preview';
                    memberForm.querySelector('[name="photo"]').after(photo);
                }
                if (photo) {
                    photo.hidden = !member.photo_path;
                    if (member.photo_path) {
                        photo.src = new URL(member.photo_path, window.location.origin + '/').href;
                        photo.alt = 'Foto ' + member.name;
                    }
                }
                var memberNameField = memberForm.querySelector('[name="name"]');
                var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                memberForm.scrollIntoView({
                    behavior: prefersReducedMotion ? 'auto' : 'smooth',
                    block: 'center'
                });
                memberNameField.focus({ preventScroll: true });
            } catch (error) {
                console.error('Data anggota untuk form edit tidak valid.', error);
                if (memberForm) setFormMessage(memberForm, 'Data anggota tidak dapat dimuat. Muat ulang halaman dan coba lagi.');
            }
        }

        if (target.closest('[data-edit-member-reset]')) {
            var memberResetForm = target.closest('form');
            memberResetForm.reset();
            memberResetForm.querySelector('[name="id"]').value = '0';
            memberResetForm.querySelector('[name="periode_id"]').value = document.getElementById('member-period-id').value;
            memberResetForm.querySelector('[name="name"]').value = '';
            memberResetForm.querySelector('[name="position"]').value = '';
            memberResetForm.dataset.profileDirty = 'false';
            var memberResetSaveButton = memberResetForm.querySelector('[data-profile-submit]');
            memberResetSaveButton.classList.remove('is-icon-only');
            memberResetSaveButton.querySelector('.profile-submit-label').textContent = 'Simpan anggota';
            var memberPreview = memberResetForm.querySelector('.profile-member-photo-preview');
            if (memberPreview) memberPreview.hidden = true;
        }
    }, true);

    function confirmDiscard(event) {
        var modal = event.target;
        if (!(modal instanceof HTMLElement) || !modal.matches('[id^="profile-"][id$="-modal"]')) return;

        var dirtyForm = Array.prototype.find.call(
            modal.querySelectorAll('form[data-profile-ajax-form]'),
            function (form) { return form.dataset.profileDirty === 'true'; }
        );
        if (dirtyForm && !window.confirm('Perubahan belum disimpan. Buang perubahan dan tutup?')) {
            event.preventDefault();
        } else if (dirtyForm) {
            dirtyForm.dataset.profileDirty = 'false';
            window.setTimeout(function () { window.location.reload(); }, 180);
        }
    }

    document.addEventListener('hide.bs.modal', confirmDiscard, true);
    document.addEventListener('hide.bs.offcanvas', confirmDiscard, true);

    document.addEventListener('submit', async function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('form[data-profile-ajax-form]')) return;
        event.preventDefault();

        if (form.hasAttribute('data-profile-confirm') && form.dataset.profileConfirm
            && !window.confirm(form.dataset.profileConfirm)) return;
        if (form.matches('[data-profile-map-form]') && !validateServiceMap(form)) {
            form.querySelector('[name="service_map"]').reportValidity();
            return;
        }

        clearFieldErrors(form);
        setFormMessage(form, '');
        setBusy(form, true);

        try {
            var response = await window.fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });
            var data;
            try {
                data = await response.json();
            } catch (error) {
                throw new Error('Respons server tidak dapat dibaca. Muat ulang halaman lalu coba kembali.');
            }

            updateCsrf(data);
            if (!response.ok || !data.success) {
                setFormMessage(form, data.message || 'Perubahan gagal disimpan. Periksa isian lalu coba kembali.');
                showFieldErrors(form, data.errors);
                setBusy(form, false);
                return;
            }

            updateSummary(data.summary);
            form.dataset.profileDirty = 'false';
            if (form.matches('[data-profile-map-form]')) {
                form.querySelector('[name="service_map"]').value = '';
            }
            announce(data.message || 'Perubahan berhasil disimpan.');
            var modal = getModal(form);
            var reload = form.hasAttribute('data-profile-reload');
            if (modal && window.ResponsiveModal) {
                window.ResponsiveModal.close('#' + modal.id);
            }
            if (reload) window.setTimeout(function () { window.location.reload(); }, 280);
        } catch (error) {
            console.error('Penyimpanan modul Profil gagal.', error);
            setFormMessage(form, error.message || 'Penyimpanan gagal. Periksa koneksi lalu coba kembali.');
        } finally {
            setBusy(form, false);
        }
    });
})();
