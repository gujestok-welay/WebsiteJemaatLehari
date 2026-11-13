'use strict';

const publicNavDrawer = document.querySelector('#navigasi.public-nav-drawer');
const publicNavToggle = document.querySelector('.site-header [data-bs-toggle="offcanvas"][aria-controls="navigasi"]');
if (publicNavDrawer && publicNavToggle) {
    publicNavDrawer.addEventListener('show.bs.offcanvas', () => {
        publicNavToggle.setAttribute('aria-expanded', 'true');
        publicNavToggle.setAttribute('aria-label', 'Tutup navigasi utama');
    });
    publicNavDrawer.addEventListener('hidden.bs.offcanvas', () => {
        publicNavToggle.setAttribute('aria-expanded', 'false');
        publicNavToggle.setAttribute('aria-label', 'Buka navigasi utama');
    });
}

// Progressive enhancement: the full schedule remains available without JavaScript.
const serviceSelect = document.querySelector('#jenis-ibadah');
if (serviceSelect) {
    const serviceRows = Array.from(document.querySelectorAll('[data-service]'));
    const status = document.querySelector('#hasil-jadwal');
    document.querySelector('.schedule-filter').hidden = false;
    serviceSelect.addEventListener('change', () => {
        let count = 0;
        serviceRows.forEach((row) => {
            row.hidden = serviceSelect.value !== 'semua' && row.dataset.service !== serviceSelect.value;
            if (!row.hidden) count += 1;
        });
        status.textContent = `${count} jadwal contoh ditampilkan.`;
    });
}

document.querySelectorAll('[data-leadership-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
    const images = Array.from(carousel.querySelectorAll('.leader-card-image'));
    const controls = carousel.querySelector('[data-carousel-controls]');
    const previousButton = carousel.querySelector('[data-carousel-previous]');
    const nextButton = carousel.querySelector('[data-carousel-next]');
    const toggleButton = carousel.querySelector('[data-carousel-toggle]');
    const toggleLabel = carousel.querySelector('[data-carousel-toggle-label]');
    const indicators = Array.from(carousel.querySelectorAll('[data-carousel-indicator]'));

    const faceBoxes = new Map();
    let faceDetector = null;
    if (typeof window.FaceDetector === 'function') {
        try {
            faceDetector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
        } catch {
            faceDetector = null;
        }
    }

    const positionImageOnFace = (image, face) => {
        const card = image.closest('.leader-card');
        if (!card || !image.naturalWidth || !image.naturalHeight) return;

        const { width, height } = card.getBoundingClientRect();
        if (!width || !height) return;

        const scale = Math.max(width / image.naturalWidth, height / image.naturalHeight);
        const overflowX = image.naturalWidth * scale - width;
        const overflowY = image.naturalHeight * scale - height;
        const faceCenterX = (face.x + face.width / 2) * scale;
        const faceCenterY = (face.y + face.height / 2) * scale;
        const horizontalPosition = overflowX > 0
            ? Math.min(1, Math.max(0, (faceCenterX - width * 0.5) / overflowX))
            : 0.5;
        const verticalPosition = overflowY > 0
            ? Math.min(1, Math.max(0, (faceCenterY - height * 0.35) / overflowY))
            : 0.5;

        image.style.objectPosition = `${horizontalPosition * 100}% ${verticalPosition * 100}%`;
    };

    images.forEach((image) => {
        image.dataset.faceTracking = 'fallback';
        if (!faceDetector) return;

        const detectFace = async () => {
            try {
                await image.decode();
                const faces = await faceDetector.detect(image);
                const box = faces[0] && faces[0].boundingBox;
                if (!box || ![box.x, box.y, box.width, box.height].every(Number.isFinite)) return;

                const face = { x: box.x, y: box.y, width: box.width, height: box.height };
                faceBoxes.set(image, face);
                positionImageOnFace(image, face);
                image.dataset.faceTracking = 'detected';
            } catch {
                image.dataset.faceTracking = 'fallback';
            }
        };

        detectFace();
    });

    window.addEventListener('resize', () => {
        faceBoxes.forEach((face, image) => positionImageOnFace(image, face));
    });

    carousel.classList.add('is-enhanced');
    if (slides.length < 2) return;
    if (!controls || !previousButton || !nextButton || !toggleButton || !toggleLabel || indicators.length !== slides.length) {
        throw new Error('Carousel Majelis membutuhkan kontrol dan indikator untuk setiap anggota.');
    }

    const configuredInterval = Number(carousel.dataset.autoplayInterval);
    if (!Number.isFinite(configuredInterval) || configuredInterval < 1000) {
        throw new Error('Interval pergantian carousel Majelis tidak valid.');
    }

    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let activeIndex = 0;
    let userPaused = motionPreference.matches;
    let pointerInside = false;
    let focusInside = false;
    let autoplayTimer = 0;

    const updateToggle = () => {
        const label = userPaused ? 'Lanjutkan pergantian otomatis' : 'Jeda pergantian otomatis';
        toggleButton.setAttribute('aria-label', label);
        toggleLabel.textContent = userPaused ? 'Putar' : 'Jeda';
    };

    const updateSlide = (nextIndex) => {
        activeIndex = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, index) => {
            slide.hidden = index !== activeIndex;
            slide.classList.remove('is-entering');
        });
        indicators.forEach((indicator, index) => {
            indicator.setAttribute('aria-pressed', String(index === activeIndex));
        });

        requestAnimationFrame(() => slides[activeIndex].classList.add('is-entering'));
    };

    const syncAutoplay = () => {
        window.clearInterval(autoplayTimer);
        autoplayTimer = 0;
        if (!userPaused && !pointerInside && !focusInside && !document.hidden) {
            autoplayTimer = window.setInterval(() => updateSlide(activeIndex + 1), configuredInterval);
        }
    };

    controls.hidden = false;
    updateSlide(activeIndex);
    updateToggle();

    previousButton.addEventListener('click', () => {
        updateSlide(activeIndex - 1);
        syncAutoplay();
    });
    nextButton.addEventListener('click', () => {
        updateSlide(activeIndex + 1);
        syncAutoplay();
    });
    indicators.forEach((indicator) => {
        indicator.addEventListener('click', () => {
            updateSlide(Number(indicator.dataset.carouselIndicator));
            syncAutoplay();
        });
    });
    toggleButton.addEventListener('click', () => {
        userPaused = !userPaused;
        updateToggle();
        syncAutoplay();
    });

    carousel.addEventListener('pointerenter', () => {
        pointerInside = true;
        syncAutoplay();
    });
    carousel.addEventListener('pointerleave', () => {
        pointerInside = false;
        syncAutoplay();
    });
    carousel.addEventListener('focusin', () => {
        focusInside = true;
        syncAutoplay();
    });
    carousel.addEventListener('focusout', () => {
        window.setTimeout(() => {
            focusInside = carousel.contains(document.activeElement);
            syncAutoplay();
        }, 0);
    });
    document.addEventListener('visibilitychange', syncAutoplay);
    motionPreference.addEventListener('change', (event) => {
        if (event.matches) {
            userPaused = true;
            updateToggle();
        }
        syncAutoplay();
    });

    syncAutoplay();
});
