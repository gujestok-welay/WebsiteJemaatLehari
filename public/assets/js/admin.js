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

    function clearNewsCoverPreview(form) {
        var preview = form.querySelector('[data-news-cover-preview]');
        if (preview._newsCoverPreviewUrl) {
            URL.revokeObjectURL(preview._newsCoverPreviewUrl);
            preview._newsCoverPreviewUrl = '';
        }
        preview.replaceChildren();
        preview.hidden = true;
    }

    function clearNewsSourceImagePreview(form) {
        var imageUrl = form.querySelector('[data-news-source-image-url]');
        var preview = form.querySelector('[data-news-source-image-preview]');
        var imageFigure = form.querySelector('[data-news-source-image-figure]');
        var useImage = form.querySelector('[data-news-use-source-image]');

        imageUrl.value = '';
        imageFigure.replaceChildren();
        preview.hidden = true;
        useImage.checked = false;
        useImage.required = false;
    }

    function renderNewsCoverPreview(form, isValid) {
        var input = form.querySelector('[name="cover_image"]');
        var preview = form.querySelector('[data-news-cover-preview]');
        var file = input.files && input.files[0];
        clearNewsCoverPreview(form);

        if (!file || !isValid) return;

        var item = document.createElement('li');
        var image = document.createElement('img');
        var details = document.createElement('span');
        var name = document.createElement('strong');
        var size = document.createElement('small');
        var removeButton = document.createElement('button');
        var objectUrl = URL.createObjectURL(file);

        preview._newsCoverPreviewUrl = objectUrl;
        image.src = objectUrl;
        image.alt = '';
        image.width = 52;
        image.height = 52;
        image.setAttribute('aria-hidden', 'true');
        name.textContent = file.name;
        size.textContent = file.size < 1024 * 1024
            ? Math.max(1, Math.round(file.size / 1024)) + ' KB'
            : (file.size / (1024 * 1024)).toFixed(1) + ' MB';
        details.append(name, size);

        removeButton.type = 'button';
        removeButton.className = 'gallery-admin-upload-remove';
        removeButton.setAttribute('aria-label', 'Hapus gambar kover');
        removeButton.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14" /></svg>';
        removeButton.addEventListener('click', function () {
            input.value = '';
            input.setCustomValidity('');
            var error = form.querySelector('[data-news-file-error]');
            error.textContent = '';
            error.hidden = true;
            renderNewsCoverPreview(form, false);
        });

        item.append(image, details, removeButton);
        preview.append(item);
        preview.hidden = false;
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
        form.querySelector('[name="article_type"]').value = 'internal';
        form.querySelector('[name="source_url"]').value = '';
        form.querySelector('[name="source_name"]').value = '';
        form.querySelector('[data-news-metadata-status]').textContent = '';
        form.querySelector('[data-news-summary-help]').hidden = true;
        clearNewsSourceImagePreview(form);
        syncNewsType(form);
        resizeNewsTitle(form.querySelector('[data-news-title]'));
        var coverInput = form.querySelector('[name="cover_image"]');
        coverInput.value = '';
        coverInput.setCustomValidity('');
        clearNewsCoverPreview(form);
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

    function syncNewsType(form) {
        var isExternal = form.querySelector('[name="article_type"]').value === 'external';
        var contentField = form.querySelector('[data-news-content-field]');
        var content = form.querySelector('[name="content"]');
        var sourceUrl = form.querySelector('[name="source_url"]');
        var sourceName = form.querySelector('[name="source_name"]');

        contentField.hidden = isExternal;
        content.required = !isExternal;
        form.querySelector('[data-news-source-url-field]').hidden = !isExternal;
        sourceUrl.required = isExternal;
        form.querySelector('[data-news-source-name-field]').hidden = !isExternal;
        sourceName.required = isExternal;
    }

    var initialNewsForm = getNewsForm();
    if (initialNewsForm) syncNewsType(initialNewsForm);

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
        form.querySelector('[name="article_type"]').value = news.article_type || 'internal';
        form.querySelector('[name="source_url"]').value = news.source_url || '';
        form.querySelector('[name="source_name"]').value = news.source_name || '';
        form.querySelector('[data-news-metadata-status]').textContent = '';
        form.querySelector('[data-news-summary-help]').hidden = true;
        clearNewsSourceImagePreview(form);
        syncNewsType(form);
        var coverInput = form.querySelector('[name="cover_image"]');
        coverInput.value = '';
        coverInput.setCustomValidity('');
        clearNewsCoverPreview(form);
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
        if (input instanceof HTMLSelectElement && input.name === 'article_type' && input.form.matches('[data-news-form]')) {
            syncNewsType(input.form);
            return;
        }
        if (input instanceof HTMLInputElement && input.name === 'cover_image' && input.form.matches('[data-news-form]')) {
            var isValid = validateCoverFile(input.form);
            renderNewsCoverPreview(input.form, isValid);
            var sourceImage = input.form.querySelector('[data-news-use-source-image]');
            sourceImage.required = !isValid && input.form.querySelector('[data-news-source-image-url]').value !== '';
        }
    });

    document.addEventListener('input', function (event) {
        var input = event.target;
        if (input instanceof HTMLInputElement && input.hasAttribute('data-news-source-url')) {
            clearNewsSourceImagePreview(input.form);
            input.form.querySelector('[data-news-summary-help]').hidden = true;
        }
    });

    document.addEventListener('click', async function (event) {
        var target = event.target instanceof Element ? event.target.closest('[data-news-fetch-metadata]') : null;
        if (!target) return;

        var form = getNewsForm();
        if (!form) return;

        var urlField = form.querySelector('[name="source_url"]');
        var status = form.querySelector('[data-news-metadata-status]');
        var button = target;
        var typeField = form.querySelector('[name="article_type"]');
        clearNewsSourceImagePreview(form);
        form.querySelector('[data-news-summary-help]').hidden = true;
        typeField.value = 'external';
        typeField.dispatchEvent(new Event('change', { bubbles: true }));
        syncNewsType(form);
        if (!urlField.reportValidity()) return;

        var payload = new URLSearchParams();
        payload.set('url', urlField.value);
        var csrf = form.querySelector('input[name^="csrf_"]');
        if (csrf) payload.set(csrf.name, csrf.value);

        button.disabled = true;
        status.textContent = 'Mengambil metadata...';
        try {
            var response = await fetch(form.dataset.newsMetadataUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: payload.toString(),
                credentials: 'same-origin'
            });
            var result = await response.json();
            var csrfInput = form.querySelector('input[name^="csrf_"]');
            if (csrfInput && result.csrf_token && result.csrf_hash) {
                csrfInput.name = result.csrf_token;
                csrfInput.value = result.csrf_hash;
            }
            if (!response.ok) throw new Error(result.message || 'Metadata tidak dapat diambil.');

            form.querySelector('[name="title"]').value = result.title || '';
            form.querySelector('[name="source_name"]').value = result.source_name || '';
            var summary = form.querySelector('[name="summary"]');
            if (result.summary && !summary.value.trim()) {
                summary.value = result.summary;
                form.querySelector('[data-news-summary-help]').hidden = false;
            }
            if (result.image_url) {
                form.querySelector('[data-news-source-image-url]').value = result.image_url;
                var sourceImage = document.createElement('img');
                sourceImage.className = 'news-source-image-thumbnail';
                sourceImage.alt = 'Pratinjau gambar kover dari artikel sumber';
                sourceImage.referrerPolicy = 'no-referrer';
                sourceImage.src = result.image_url;
                form.querySelector('[data-news-source-image-figure]').replaceChildren(sourceImage);
                form.querySelector('[data-news-source-image-preview]').hidden = false;
                form.querySelector('[data-news-use-source-image]').required =
                    !form.querySelector('[name="cover_image"]').files.length;
            }
            resizeNewsTitle(form.querySelector('[data-news-title]'));
            status.textContent = result.image_url
                ? (result.summary
                    ? 'Judul, ringkasan, dan gambar ditemukan. Tinjau ringkasan serta pastikan izin gambar sebelum menyimpan.'
                    : 'Judul dan gambar ditemukan. Tulis ringkasan sendiri dan pastikan izin gambar sebelum menyimpan.')
                : (result.summary
                    ? 'Judul dan ringkasan terisi. Tinjau ringkasan sebelum diterbitkan.'
                    : 'Judul terisi. Tulis ringkasan sendiri sebelum diterbitkan.');
        } catch (error) {
            status.textContent = error instanceof Error
                ? error.message
                : 'Metadata tidak dapat diambil. Isi judul dan sumber secara manual.';
        } finally {
            button.disabled = false;
        }
    });

    var coverDropzone = document.querySelector('[data-news-cover-dropzone]');
    if (coverDropzone) {
        var coverInput = coverDropzone.querySelector('[name="cover_image"]');

        coverDropzone.addEventListener('dragenter', function (event) {
            event.preventDefault();
            coverDropzone.classList.add('is-dragging');
        });

        coverDropzone.addEventListener('dragover', function (event) {
            event.preventDefault();
            if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
            coverDropzone.classList.add('is-dragging');
        });

        coverDropzone.addEventListener('dragleave', function (event) {
            if (!coverDropzone.contains(event.relatedTarget)) {
                coverDropzone.classList.remove('is-dragging');
            }
        });

        coverDropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            coverDropzone.classList.remove('is-dragging');
            var files = event.dataTransfer ? event.dataTransfer.files : [];
            if (!files.length) return;
            if (files.length > 1) {
                var fileError = coverInput.form.querySelector('[data-news-file-error]');
                fileError.textContent = 'Pilih satu gambar kover saja.';
                fileError.hidden = false;
                return;
            }

            var transfer = new DataTransfer();
            transfer.items.add(files[0]);
            coverInput.files = transfer.files;
            coverInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

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

        var territoryToggle = target.closest('[data-territory-item-toggle]');
        if (territoryToggle) {
            var territoryItem = territoryToggle.closest('[data-content-item]');
            var territoryEditor = territoryItem && territoryItem.querySelector('[data-territory-item-editor]');
            if (!territoryEditor) return;

            var expanded = territoryEditor.hidden;
            territoryEditor.hidden = !expanded;
            territoryToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            territoryToggle.textContent = expanded ? 'Selesai mengubah' : 'Ubah';
            if (expanded) {
                var firstField = territoryEditor.querySelector('input:not([type="hidden"]), select, textarea');
                if (firstField) firstField.focus();
            } else {
                territoryToggle.focus();
            }
            return;
        }

        var ageLabelToggle = target.closest('[data-age-label-toggle]');
        if (ageLabelToggle) {
            var ageItem = ageLabelToggle.closest('[data-content-item]');
            var ageEditor = ageItem && ageItem.querySelector('[data-age-label-editor]');
            var ageInput = ageEditor && ageEditor.querySelector('[data-age-label-input]');
            if (!ageEditor || !ageInput) return;

            if (ageEditor.hidden) {
                ageInput.required = true;
                ageEditor.hidden = false;
                ageLabelToggle.setAttribute('aria-expanded', 'true');
                ageLabelToggle.textContent = 'Selesai mengubah rentang usia';
                ageInput.focus();
            } else {
                ageInput.setCustomValidity(ageInput.value.trim() ? '' : 'Isi rentang usia.');
                if (!ageInput.reportValidity()) return;
                ageInput.required = false;
                ageEditor.hidden = true;
                ageLabelToggle.setAttribute('aria-expanded', 'false');
                ageLabelToggle.textContent = 'Ubah rentang usia';
            }
            return;
        }

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

            var newAgeLabel = item.querySelector('[data-age-label-input]');
            var newTerritoryEditor = item.querySelector('[data-territory-item-editor]');
            list.dataset.nextIndex = String(index + 1);
            list.querySelector('.profile-content-list-items').append(item);
            if (newAgeLabel) newAgeLabel.focus();
            else if (newTerritoryEditor) {
                var firstTerritoryField = newTerritoryEditor.querySelector('input:not([type="hidden"]), select, textarea');
                if (firstTerritoryField) firstTerritoryField.focus();
            }
            return;
        }

        var removeButton = target.closest('[data-remove-item]');
        if (removeButton) {
            removeButton.closest('[data-content-item]').remove();
        }
    });

    document.addEventListener('input', function (event) {
        var input = event.target instanceof Element ? event.target.closest('[data-age-label-input]') : null;
        if (input) {
            input.setCustomValidity('');
            var item = input.closest('[data-content-item]');
            var display = item && item.querySelector('[data-age-label-display]');
            if (display) display.textContent = input.value.trim() ? 'Usia ' + input.value.trim() : 'Rentang usia baru';
        }

        var territoryInput = event.target instanceof Element ? event.target.closest('[data-territory-item-editor] [name]') : null;
        if (territoryInput) updateTerritoryItemSummary(territoryInput.closest('[data-content-item]'));
    });

    document.addEventListener('change', function (event) {
        var territoryInput = event.target instanceof Element ? event.target.closest('[data-territory-item-editor] [name]') : null;
        if (territoryInput) updateTerritoryItemSummary(territoryInput.closest('[data-content-item]'));
    });

    function updateTerritoryItemSummary(item) {
        if (!item) return;
        var list = item.closest('[data-territory-item-list]');
        var summary = item.querySelector('[data-territory-item-summary]');
        if (!list || !summary) return;

        var valueFor = function (fieldName) {
            var field = Array.prototype.find.call(
                item.querySelectorAll('[data-territory-item-editor] [name]'),
                function (input) { return input.name.endsWith('[' + fieldName + ']'); }
            );
            return field && 'value' in field ? field.value.trim() : '';
        };
        var parts;
        var separator;
        switch (list.dataset.territoryItemList) {
            case 'boundaries':
                parts = [valueFor('direction'), valueFor('name')];
                separator = ' — ';
                break;
            case 'sectors':
                parts = [valueFor('name')];
                separator = '';
                break;
            case 'orbitations':
                parts = [valueFor('label'), valueFor('value')];
                separator = ' — ';
                break;
            case 'seasons':
                parts = [valueFor('period'), valueFor('title')];
                separator = ' · ';
                break;
            default:
                return;
        }

        var text = parts.filter(Boolean).join(separator);
        summary.textContent = text || 'Item baru';
    }
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
    var scheduleSearchTimer = 0;
    var lastScheduleUrl = new URL(window.location.href);
    var pageTitleSuffix = ' · Media Center Jemaat Leahari';

    function isPartialRoute(url) {
        var path = url.pathname.replace(/\/+$/, '');
        return /\/admin\/(?:dashboard|berita|galeri|jadwal(?:\/(?:create|edit\/\d+))?|profil|statistik)$/.test(path);
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

    async function navigate(url, addHistoryEntry, focusSelector) {
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
            var focusTarget = focusSelector ? main.querySelector(focusSelector) : null;
            var heading = main.querySelector('h1');
            if (focusTarget) {
                focusTarget.focus({ preventScroll: true });
            } else if (heading) {
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

    document.addEventListener('input', function (event) {
        var field = event.target;
        if (!(field instanceof HTMLInputElement) || !field.matches('[data-schedule-toolbar] input[name="q"]')) return;

        var form = field.form;
        if (!form) return;

        window.clearTimeout(scheduleSearchTimer);
        var url = new URL(form.action, window.location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        scheduleSearchTimer = window.setTimeout(function () {
            scheduleSearchTimer = 0;
            if (url.href !== window.location.href) {
                navigateSchedule(url, false, { type: 'search' });
            }
        }, 250);
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'get') return;

        var url = new URL(form.action, window.location.href);
        if (url.origin === window.location.origin && isScheduleRoute(url) && form.closest('[data-schedule-toolbar]')) {
            window.clearTimeout(scheduleSearchTimer);
            scheduleSearchTimer = 0;
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

    document.addEventListener('change', function (event) {
        var field = event.target;
        if (!(field instanceof HTMLSelectElement)
            || !field.matches('.news-search select[name="status"], .gallery-admin-toolbar select[name="status"]')) return;

        var form = field.form;
        if (!form) return;

        var url = new URL(form.action, window.location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        var focusSelector = field.matches('.gallery-admin-toolbar select[name="status"]')
            ? '.gallery-admin-toolbar select[name="status"]'
            : '.news-search select[name="status"]';
        navigate(url, true, focusSelector);
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
    function profileEditorUrl() {
        var editor = document.querySelector('[data-profile-editor-page]');
        return editor ? editor.dataset.profileEditorUrl : window.location.href;
    }

    function getModal(form) {
        return form.closest('[data-rm]');
    }

    function setFormMessage(form, message, success) {
        var modal = getModal(form);
        var alert = form.querySelector('[data-profile-form-message]')
            || (modal && modal.querySelector('[data-profile-form-message]'));
        if (!alert) return;

        alert.textContent = message;
        alert.hidden = message === '';
        alert.classList.toggle('is-success', Boolean(message) && Boolean(success));
        if (message) alert.setAttribute('role', success ? 'status' : 'alert');
    }

    function dirtyProfileForms() {
        var editor = document.querySelector('[data-profile-editor-page]');
        if (!editor) return [];

        return Array.prototype.filter.call(
            editor.querySelectorAll('form[data-profile-ajax-form]'),
            function (form) { return form.dataset.profileDirty === 'true'; }
        );
    }

    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        var link = target && target.closest('a[href]');
        if (!link || link.target || link.hasAttribute('download')) return;

        var dirtyForms = dirtyProfileForms();
        if (dirtyForms.length === 0) return;

        var destination = new URL(link.href, window.location.href);
        if (destination.href === window.location.href) return;
        if (!window.confirm('Perubahan belum disimpan. Tinggalkan halaman dan buang perubahan?')) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        dirtyForms.forEach(function (form) { form.dataset.profileDirty = 'false'; });
    }, true);

    window.addEventListener('beforeunload', function (event) {
        if (dirtyProfileForms().length === 0) return;

        event.preventDefault();
        event.returnValue = '';
    });

    window.addEventListener('popstate', function (event) {
        var dirtyForms = dirtyProfileForms();
        if (dirtyForms.length === 0) return;

        if (!window.confirm('Perubahan belum disimpan. Tinggalkan halaman dan buang perubahan?')) {
            window.history.pushState(event.state, '', profileEditorUrl());
            event.stopImmediatePropagation();
            return;
        }

        dirtyForms.forEach(function (form) { form.dataset.profileDirty = 'false'; });
    }, true);

    function clearFieldErrors(form) {
        form.querySelectorAll('[data-profile-inline-error]').forEach(function (error) {
            error.remove();
        });
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (field) {
            field.removeAttribute('aria-invalid');
        });
    }

    function showFieldErrors(form, errors) {
        var firstInvalidField = null;
        Object.keys(errors || {}).forEach(function (name) {
            var field = Array.prototype.find.call(form.elements, function (control) {
                return control.name === name || control.name.endsWith('[' + name + ']');
            });
            if (!field) return;

            var wrapper = field.closest('.profile-field') || field.parentElement;
            if (!wrapper) return;

            if (!firstInvalidField) firstInvalidField = field;
            field.setAttribute('aria-invalid', 'true');
            var message = document.createElement('small');
            message.className = 'profile-field-error';
            message.dataset.profileInlineError = '';
            message.setAttribute('role', 'alert');
            message.textContent = errors[name];
            wrapper.append(message);
        });

        if (firstInvalidField) {
            var panel = firstInvalidField.closest('[data-profile-step-panel]');
            var panels = Array.prototype.slice.call(form.querySelectorAll('[data-profile-step-panel]'));
            if (panel && panels.indexOf(panel) !== -1) {
                setProfileStep(form, panels.indexOf(panel));
                firstInvalidField.focus();
            } else {
                var leadershipPanel = firstInvalidField.closest('[data-leadership-step-panel]');
                var leadershipStepper = leadershipPanel && leadershipPanel.closest('[data-leadership-stepper]');
                var leadershipPanels = leadershipStepper
                    ? Array.prototype.slice.call(leadershipStepper.querySelectorAll('[data-leadership-step-panel]'))
                    : [];
                if (leadershipPanel && leadershipStepper && leadershipPanels.indexOf(leadershipPanel) !== -1) {
                    setLeadershipStep(leadershipStepper, leadershipPanels.indexOf(leadershipPanel));
                    firstInvalidField.focus();
                }
            }
        }
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
            document.querySelectorAll('[data-profile-map-image], [data-profile-map-preview]').forEach(function (image) {
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
        var formattedTotal = new Intl.NumberFormat('id-ID').format(total);
        if (output) output.textContent = formattedTotal;

        var householdCountField = form.querySelector('[name="census[household_count]"]');
        var householdSuffixField = form.querySelector('[name="census[household_suffix]"]');
        var populationSuffixField = form.querySelector('[name="census[population_suffix]"]');
        var householdCount = Number.parseInt(householdCountField ? householdCountField.value : '0', 10);
        var householdSuffix = householdSuffixField ? householdSuffixField.value : '';
        var populationSuffix = populationSuffixField ? populationSuffixField.value : '';
        var preview = form.querySelector('[data-census-public-preview]');
        if (preview) {
            preview.textContent = new Intl.NumberFormat('id-ID').format(Number.isFinite(householdCount) ? householdCount : 0)
                + ' ' + householdSuffix.trim()
                + ' ' + formattedTotal
                + ' ' + populationSuffix.trim();
        }
    }

    function updateTerritoryPreview(form) {
        var directionIcons = {
            Utara: 'bi-arrow-up-circle',
            Timur: 'bi-arrow-right-circle',
            Selatan: 'bi-arrow-down-circle',
            Barat: 'bi-arrow-left-circle'
        };
        form.querySelectorAll('[data-boundary-icon]').forEach(function (iconField) {
            var item = iconField.closest('[data-content-item]');
            var direction = item && item.querySelector('select[name$="[direction]"]');
            if (direction && directionIcons[direction.value]) iconField.value = directionIcons[direction.value];
        });

        var valueFor = function (name) {
            var field = form.elements.namedItem(name);
            return field && 'value' in field ? field.value.trim() : '';
        };
        var boundaryPreview = form.querySelector('[data-territory-preview="boundary"]');
        if (boundaryPreview) {
            var boundaryParts = [
                valueFor('territory[boundary_prefix]'),
                valueFor('territory[boundaries][0][direction]')
            ].filter(Boolean);
            var boundaryName = valueFor('territory[boundaries][0][name]');
            boundaryPreview.textContent = boundaryParts.join(' ') + (boundaryName ? ' — ' + boundaryName : '');
        }

        var sectorPreview = form.querySelector('[data-territory-preview="sector"]');
        if (sectorPreview) {
            var sectors = Array.prototype.slice.call(form.querySelectorAll('input[name^="territory[sectors]"][name$="[area]"]'));
            var areaTotal = sectors.reduce(function (total, field) {
                var area = Number.parseFloat(field.value);
                return total + (Number.isFinite(area) && area > 0 ? area : 0);
            }, 0);
            var firstArea = Number.parseFloat(valueFor('territory[sectors][0][area]'));
            var areaShare = areaTotal > 0 && Number.isFinite(firstArea) ? Math.round(firstArea / areaTotal * 100) : 0;
            var formattedArea = Number.isFinite(firstArea)
                ? new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(firstArea)
                : '0.00';
            sectorPreview.textContent = [
                [valueFor('territory[sector_prefix]'), valueFor('territory[sectors][0][name]')].filter(Boolean).join(' '),
                [valueFor('territory[sectors][0][units]'), valueFor('territory[unit_suffix]')].filter(Boolean).join(' '),
                [formattedArea, valueFor('territory[sectors][0][area_unit]')].filter(Boolean).join(' '),
                areaShare + valueFor('territory[area_share_suffix]')
            ].join(' · ');
        }
    }

    function setProfileStep(form, requestedIndex) {
        var panels = Array.prototype.slice.call(form.querySelectorAll('[data-profile-step-panel]'));
        if (panels.length === 0) return;

        var activeIndex = Math.max(0, Math.min(requestedIndex, panels.length - 1));
        panels.forEach(function (panel, index) {
            panel.hidden = index !== activeIndex;
        });

        var steps = Array.prototype.slice.call(form.querySelectorAll('[data-profile-step-target]'));
        var visitedSteps = (form.dataset.profileVisitedSteps || '')
            .split(',')
            .filter(Boolean)
            .map(Number);
        if (visitedSteps.indexOf(activeIndex) === -1) visitedSteps.push(activeIndex);
        form.dataset.profileVisitedSteps = visitedSteps.join(',');
        steps.forEach(function (step, index) {
            var item = step.closest('li');
            if (index === activeIndex) step.setAttribute('aria-current', 'step');
            else step.removeAttribute('aria-current');
            if (item) {
                item.classList.toggle('is-current', index === activeIndex);
                item.classList.remove('is-complete');
                item.classList.toggle('is-visited', visitedSteps.indexOf(index) !== -1);
            }
        });

        var previous = form.querySelector('[data-profile-step-previous]');
        var next = form.querySelector('[data-profile-step-next]');
        var count = form.querySelector('[data-profile-step-count]');
        var navigation = form.querySelector('[data-profile-step-nav]');
        var controls = form.querySelector('[data-profile-step-controls]');
        if (previous) previous.disabled = activeIndex === 0;
        if (next) next.disabled = activeIndex === panels.length - 1;
        if (count) count.textContent = 'Bagian ' + (activeIndex + 1) + ' dari ' + panels.length;
        if (navigation) navigation.hidden = false;
        if (controls) controls.hidden = false;
        var activeStep = steps[activeIndex];
        if (navigation && activeStep) {
            var navigationBounds = navigation.getBoundingClientRect();
            var activeStepBounds = activeStep.getBoundingClientRect();
            if (activeStepBounds.right > navigationBounds.right) {
                navigation.scrollLeft += activeStepBounds.right - navigationBounds.right + 4;
            } else if (activeStepBounds.left < navigationBounds.left) {
                navigation.scrollLeft -= navigationBounds.left - activeStepBounds.left + 4;
            }
        }
        form.dataset.profileCurrentStep = String(activeIndex);
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
        var activeStep = steps[activeIndex];
        if (navigation && activeStep) {
            var navigationBounds = navigation.getBoundingClientRect();
            var activeStepBounds = activeStep.getBoundingClientRect();
            if (activeStepBounds.right > navigationBounds.right) {
                navigation.scrollLeft += activeStepBounds.right - navigationBounds.right + 4;
            } else if (activeStepBounds.left < navigationBounds.left) {
                navigation.scrollLeft -= navigationBounds.left - activeStepBounds.left + 4;
            }
        }
        form.classList.add('is-census-enhanced');
        form.dataset.censusCurrentStep = String(activeIndex);
    }

    function setLeadershipStep(stepper, requestedIndex) {
        var panels = Array.prototype.slice.call(stepper.querySelectorAll('[data-leadership-step-panel]'));
        if (panels.length === 0) return;

        var activeIndex = Math.max(0, Math.min(requestedIndex, panels.length - 1));
        panels.forEach(function (panel, index) {
            panel.hidden = index !== activeIndex;
        });

        var steps = Array.prototype.slice.call(stepper.querySelectorAll('[data-leadership-step-target]'));
        steps.forEach(function (step, index) {
            var item = step.closest('li');
            if (index === activeIndex) step.setAttribute('aria-current', 'step');
            else step.removeAttribute('aria-current');
            if (item) item.classList.toggle('is-complete', index < activeIndex);
        });

        var previous = stepper.querySelector('[data-leadership-step-previous]');
        var next = stepper.querySelector('[data-leadership-step-next]');
        var count = stepper.querySelector('[data-leadership-step-count]');
        var navigation = stepper.querySelector('[data-leadership-step-nav]');
        var controls = stepper.querySelector('[data-leadership-step-controls]');
        if (previous) previous.disabled = activeIndex === 0;
        if (next) next.disabled = activeIndex === panels.length - 1;
        if (count) count.textContent = 'Bagian ' + (activeIndex + 1) + ' dari ' + panels.length;
        if (navigation) navigation.hidden = false;
        if (controls) controls.hidden = false;

        var activeStep = steps[activeIndex];
        if (navigation && activeStep) {
            var navigationBounds = navigation.getBoundingClientRect();
            var activeStepBounds = activeStep.getBoundingClientRect();
            if (activeStepBounds.right > navigationBounds.right) {
                navigation.scrollLeft += activeStepBounds.right - navigationBounds.right + 4;
            } else if (activeStepBounds.left < navigationBounds.left) {
                navigation.scrollLeft -= navigationBounds.left - activeStepBounds.left + 4;
            }
        }
        stepper.dataset.leadershipCurrentStep = String(activeIndex);
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
        updateCensusCalculation(form);
    });
    document.querySelectorAll('form[data-profile-step-form]').forEach(function (form) {
        setProfileStep(form, 0);
        updateTerritoryPreview(form);
    });
    document.querySelectorAll('[data-leadership-stepper]').forEach(function (stepper) {
        setLeadershipStep(stepper, 0);
    });

    document.addEventListener('input', function (event) {
        var field = event.target;
        var form = field instanceof HTMLElement ? field.closest('form[data-profile-ajax-form]') : null;
        if (!form) return;

        form.dataset.profileDirty = 'true';
        clearFieldErrors(form);
        setFormMessage(form, '');
        if (form.matches('[data-profile-census-form]')) updateCensusCalculation(form);
        if (form.matches('[data-profile-step-form]')) updateTerritoryPreview(form);
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
                    var image = form.querySelector('[data-profile-map-preview]');
                    if (image && typeof reader.result === 'string') image.src = reader.result;
                };
                reader.readAsDataURL(field.files[0]);
            }
        }
        if (form.matches('[data-profile-census-form]')) updateCensusCalculation(form);
        if (form.matches('[data-profile-step-form]')) updateTerritoryPreview(form);

    });

    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        var leadershipStepControl = target.closest('[data-leadership-step-target], [data-leadership-step-next], [data-leadership-step-previous]');
        if (leadershipStepControl) {
            var leadershipStepper = leadershipStepControl.closest('[data-leadership-stepper]');
            if (leadershipStepper) {
                var leadershipPanels = Array.prototype.slice.call(leadershipStepper.querySelectorAll('[data-leadership-step-panel]'));
                var leadershipCurrentIndex = Number.parseInt(leadershipStepper.dataset.leadershipCurrentStep, 10) || 0;
                var leadershipTargetIndex = leadershipCurrentIndex;

                if (leadershipStepControl.hasAttribute('data-leadership-step-target')) {
                    leadershipTargetIndex = leadershipPanels.findIndex(function (panel) {
                        return panel.id === leadershipStepControl.dataset.leadershipStepTarget;
                    });
                } else if (leadershipStepControl.hasAttribute('data-leadership-step-next')) {
                    leadershipTargetIndex += 1;
                } else {
                    leadershipTargetIndex -= 1;
                }

                if (leadershipTargetIndex >= 0) setLeadershipStep(leadershipStepper, leadershipTargetIndex);
            }
            return;
        }

        var profileStepControl = target.closest('[data-profile-step-target], [data-profile-step-next], [data-profile-step-previous]');
        if (profileStepControl) {
            var profileForm = profileStepControl.closest('form[data-profile-step-form]');
            if (profileForm) {
                var panels = Array.prototype.slice.call(profileForm.querySelectorAll('[data-profile-step-panel]'));
                var currentIndex = Number.parseInt(profileForm.dataset.profileCurrentStep, 10) || 0;
                var targetIndex = currentIndex;

                if (profileStepControl.hasAttribute('data-profile-step-target')) {
                    targetIndex = panels.findIndex(function (panel) {
                        return panel.id === profileStepControl.dataset.profileStepTarget;
                    });
                } else if (profileStepControl.hasAttribute('data-profile-step-next')) {
                    targetIndex += 1;
                } else {
                    targetIndex -= 1;
                }

                if (targetIndex >= 0) setProfileStep(profileForm, targetIndex);
            }
            return;
        }

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
                var memberStepper = memberForm && memberForm.closest('[data-leadership-stepper]');
                if (!memberForm || !memberStepper) throw new Error('Form anggota tidak ditemukan.');
                setLeadershipStep(memberStepper, 1);
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
            setFormMessage(form, data.message || 'Perubahan berhasil disimpan.', true);
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
