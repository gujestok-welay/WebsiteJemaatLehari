(function () {
  const MAX_PHOTOS = 30;
  const MAX_FILE_BYTES = 10 * 1024 * 1024;
  const allowedExtensions = new Set(['jpg', 'jpeg', 'png', 'webp']);
  const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
  const uploadZones = document.querySelectorAll('[data-gallery-upload-dropzone]');
  const photoInput = document.querySelector('.gallery-admin-form input[name="photos[]"]');
  const photoStatus = document.querySelector('[data-photo-selection-status]');

  if (!photoInput || !photoStatus || uploadZones.length === 0) {
    return;
  }

  let selectedPhotos = [];
  let selectedPoster = null;
  const existingPhotos = document.querySelectorAll('.gallery-admin-photo-list > label');
  const removeCheckboxes = document.querySelectorAll('input[name="remove_media[]"]');

  function remainingPhotoCount() {
    const markedForRemoval = [...removeCheckboxes].filter((checkbox) => checkbox.checked).length;
    return existingPhotos.length - markedForRemoval;
  }

  function setSelectedFiles(input, files) {
    const transfer = new DataTransfer();
    files.forEach((file) => transfer.items.add(file));
    input.files = transfer.files;
  }

  function combineFiles(currentFiles, newFiles) {
    const seen = new Set();

    return [...currentFiles, ...newFiles].filter((file) => {
      const key = [file.name, file.size, file.lastModified, file.type].join('\0');
      if (seen.has(key)) {
        return false;
      }

      seen.add(key);
      return true;
    });
  }

  function fileError(file) {
    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowedExtensions.has(extension) || (file.type && !allowedTypes.has(file.type))) {
      return `${file.name}: gunakan JPG, PNG, atau WEBP.`;
    }
    if (file.size > MAX_FILE_BYTES) {
      return `${file.name}: ukuran file melebihi 10 MB.`;
    }
    return null;
  }

  function formatFileSize(bytes) {
    if (bytes < 1024 * 1024) {
      return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  function renderFiles(list, files, kind, onRemove) {
    (list._galleryPreviewUrls || []).forEach((url) => URL.revokeObjectURL(url));
    list._galleryPreviewUrls = [];
    list.replaceChildren();
    list.hidden = files.length === 0;

    files.forEach((file, index) => {
      const item = document.createElement('li');
      const preview = document.createElement('img');
      const details = document.createElement('span');
      const name = document.createElement('strong');
      const size = document.createElement('small');
      const removeButton = document.createElement('button');
      const objectUrl = URL.createObjectURL(file);

      list._galleryPreviewUrls.push(objectUrl);
      preview.src = objectUrl;
      preview.alt = '';
      preview.width = 52;
      preview.height = 52;
      preview.setAttribute('aria-hidden', 'true');

      name.textContent = file.name;
      size.textContent = formatFileSize(file.size);
      details.append(name, size);

      removeButton.type = 'button';
      removeButton.className = 'gallery-admin-upload-remove';
      removeButton.setAttribute('aria-label', `Hapus ${file.name} dari pilihan`);
      removeButton.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14" /></svg>';
      removeButton.addEventListener('click', () => onRemove(index));

      item.dataset.uploadKind = kind;
      item.append(preview, details, removeButton);
      list.append(item);
    });
  }

  function updatePhotoStatus(message = '') {
    const totalCount = remainingPhotoCount() + selectedPhotos.length;
    photoStatus.textContent = [
      `${selectedPhotos.length} foto baru dipilih. Total setelah disimpan: ${totalCount}/${MAX_PHOTOS} foto.`,
      message,
    ].filter(Boolean).join(' ');
  }

  function showPhotoError(message) {
    updatePhotoStatus(message);
  }

  function setPhotos(files) {
    const acceptedFiles = [];
    const errors = [];

    files.forEach((file) => {
      const error = fileError(file);
      if (error) {
        errors.push(error);
      } else {
        acceptedFiles.push(file);
      }
    });

    const combinedFiles = combineFiles(selectedPhotos, acceptedFiles);
    if (remainingPhotoCount() + combinedFiles.length > MAX_PHOTOS) {
      setSelectedFiles(photoInput, selectedPhotos);
      showPhotoError(`Maksimal ${MAX_PHOTOS} foto per kegiatan; pilihan terbaru tidak ditambahkan.`);
      return;
    }

    selectedPhotos = combinedFiles;
    setSelectedFiles(photoInput, selectedPhotos);
    renderPhotoFiles();
    updatePhotoStatus(errors.join(' '));
  }

  function renderPhotoFiles() {
    renderFiles(
      document.querySelector('[data-gallery-upload-files="photos"]'),
      selectedPhotos,
      'photos',
      (index) => {
        selectedPhotos.splice(index, 1);
        setSelectedFiles(photoInput, selectedPhotos);
        renderPhotoFiles();
        updatePhotoStatus();
      }
    );
  }

  const posterInput = document.querySelector('.gallery-admin-form input[name="poster"]');
  const posterList = document.querySelector('[data-gallery-upload-files="poster"]');
  const posterStatus = document.querySelector('[data-gallery-upload-status="poster"]');

  function renderPoster() {
    renderFiles(
      posterList,
      selectedPoster ? [selectedPoster] : [],
      'poster',
      () => {
        selectedPoster = null;
        setSelectedFiles(posterInput, []);
        renderPoster();
        updatePosterStatus();
      }
    );
  }

  function updatePosterStatus(message = '') {
    posterStatus.textContent = selectedPoster
      ? `${selectedPoster.name} dipilih (${formatFileSize(selectedPoster.size)}). ${message}`.trim()
      : (message || 'Belum ada poster baru dipilih.');
  }

  function setPoster(files) {
    const file = files[0];
    if (!file) {
      return;
    }

    const error = fileError(file);
    if (error) {
      setSelectedFiles(posterInput, selectedPoster ? [selectedPoster] : []);
      updatePosterStatus(error);
      return;
    }

    selectedPoster = file;
    setSelectedFiles(posterInput, [file]);
    renderPoster();
    updatePosterStatus();
  }

  uploadZones.forEach((zone) => {
    const kind = zone.dataset.galleryUploadDropzone;
    const input = zone.querySelector('input[type="file"]');
    if (!input) {
      console.error(`Kontrol unggah Galeri "${kind}" tidak memiliki input file.`);
      return;
    }

    input.addEventListener('change', () => {
      if (kind === 'photos') {
        setPhotos([...input.files]);
      } else if (kind === 'poster') {
        setPoster([...input.files]);
      }
    });

    zone.addEventListener('dragenter', (event) => {
      event.preventDefault();
      zone.classList.add('is-dragging');
    });

    zone.addEventListener('dragover', (event) => {
      event.preventDefault();
      if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'copy';
      }
      zone.classList.add('is-dragging');
    });

    zone.addEventListener('dragleave', (event) => {
      if (!zone.contains(event.relatedTarget)) {
        zone.classList.remove('is-dragging');
      }
    });

    zone.addEventListener('drop', (event) => {
      event.preventDefault();
      zone.classList.remove('is-dragging');
      const files = [...(event.dataTransfer ? event.dataTransfer.files : [])];
      if (kind === 'photos') {
        setPhotos(files);
      } else if (kind === 'poster') {
        setPoster(files);
      }
    });
  });

  removeCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', () => updatePhotoStatus());
  });

  renderPhotoFiles();
  renderPoster();
  updatePhotoStatus();
})();
