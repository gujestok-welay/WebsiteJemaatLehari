(function () {
  const openButtons = [...document.querySelectorAll('[data-photo-viewer-open]')];
  const dialog = document.querySelector('[data-photo-viewer]');

  if (openButtons.length === 0 || !dialog) {
    return;
  }

  let photos;
  try {
    photos = JSON.parse(dialog.dataset.photoViewerPhotos || '[]');
  } catch (error) {
    console.error('Data dokumentasi foto galeri tidak valid.', error);
    return;
  }

  if (!Array.isArray(photos) || photos.length === 0 || photos.some((photo) => (
    !photo || typeof photo.url !== 'string' || !photo.url || typeof photo.label !== 'string' || !photo.label
  ))) {
    console.error('Daftar dokumentasi untuk galeri foto tidak memenuhi format yang diperlukan.');
    return;
  }

  const title = dialog.dataset.photoViewerTitle;
  const image = dialog.querySelector('[data-photo-viewer-image]');
  const counter = dialog.querySelector('[data-photo-viewer-counter]');
  const closeButton = dialog.querySelector('[data-photo-viewer-close]');
  const previousButton = dialog.querySelector('[data-photo-viewer-previous]');
  const nextButton = dialog.querySelector('[data-photo-viewer-next]');
  const stage = dialog.querySelector('[data-photo-viewer-stage]');
  let currentIndex = 0;
  let pointerStart = null;

  if (!title || !image || !counter || !closeButton || !previousButton || !nextButton || !stage) {
    console.error('Kontrol dokumentasi foto galeri tidak lengkap.');
    return;
  }

  function renderPhoto() {
    const photo = photos[currentIndex];
    image.src = photo.url;
    image.alt = `${title} — ${photo.label}`;
    counter.textContent = `${currentIndex + 1} / ${photos.length}`;
  }

  function movePhoto(offset) {
    currentIndex = (currentIndex + offset + photos.length) % photos.length;
    renderPhoto();
  }

  openButtons.forEach((openButton) => {
    openButton.hidden = false;
    openButton.addEventListener('click', () => {
      const requestedIndex = Number(openButton.dataset.photoViewerIndex || '0');
      if (!Number.isInteger(requestedIndex) || requestedIndex < 0 || requestedIndex >= photos.length) {
        console.error('Indeks foto dokumentasi galeri tidak valid.');
        return;
      }

      currentIndex = requestedIndex;
      renderPhoto();
      dialog.showModal();
      closeButton.focus();
    });
  });

  closeButton.addEventListener('click', () => dialog.close());
  previousButton.addEventListener('click', () => movePhoto(-1));
  nextButton.addEventListener('click', () => movePhoto(1));

  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) {
      dialog.close();
    }
  });

  dialog.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      movePhoto(-1);
    } else if (event.key === 'ArrowRight') {
      event.preventDefault();
      movePhoto(1);
    }
  });

  stage.addEventListener('pointerdown', (event) => {
    if (event.pointerType === 'touch') {
      pointerStart = { id: event.pointerId, x: event.clientX, y: event.clientY };
    }
  });

  stage.addEventListener('pointerup', (event) => {
    if (!pointerStart || event.pointerType !== 'touch' || pointerStart.id !== event.pointerId) {
      return;
    }

    const horizontalDistance = event.clientX - pointerStart.x;
    const verticalDistance = Math.abs(event.clientY - pointerStart.y);
    pointerStart = null;

    if (Math.abs(horizontalDistance) >= 48 && Math.abs(horizontalDistance) > verticalDistance) {
      movePhoto(horizontalDistance < 0 ? 1 : -1);
    }
  });

  stage.addEventListener('pointercancel', () => {
    pointerStart = null;
  });
})();
