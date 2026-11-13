const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

const publicCss = fs.readFileSync(
  path.join(__dirname, '..', 'public', 'assets', 'css', 'publik.css'),
  'utf8',
);
const publicScript = fs.readFileSync(
  path.join(__dirname, '..', 'public', 'assets', 'js', 'publik.js'),
  'utf8',
);

async function renderCarouselFixture(page, supportsFaceDetection) {
  await page.setViewportSize({ width: 1366, height: 900 });
  await page.setContent('<!doctype html><html><head></head><body></body></html>');
  await page.addStyleTag({ content: publicCss });
  await page.evaluate((faceDetectionEnabled) => {
    window.FaceDetector = faceDetectionEnabled
      ? class {
        async detect() {
          return [{ boundingBox: { x: 150, y: 90, width: 100, height: 120 } }];
        }
      }
      : undefined;

    const carousel = document.createElement('div');
    carousel.className = 'leadership-carousel';
    carousel.dataset.leadershipCarousel = '';
    carousel.innerHTML = `
      <div class="leadership-grid">
        <article class="leader-card" data-carousel-slide>
          <img class="leader-card-image" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='600'%3E%3Crect width='400' height='600' fill='purple'/%3E%3C/svg%3E" alt="">
          <div class="leader-card-body"><h3>Nama Majelis</h3><p>Ketua</p></div>
        </article>
        <article class="leader-card" data-carousel-slide>
          <img class="leader-card-image" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='600'%3E%3Crect width='400' height='600' fill='purple'/%3E%3C/svg%3E" alt="">
          <div class="leader-card-body"><h3>Nama Majelis Kedua</h3><p>Wakil Ketua</p></div>
        </article>
      </div>
      <div data-carousel-controls hidden>
        <button data-carousel-previous></button><button data-carousel-next></button>
        <div><button data-carousel-indicator="0"></button><button data-carousel-indicator="1"></button></div>
      </div>`;
    document.body.append(carousel);
  }, supportsFaceDetection);
  await page.addScriptTag({ content: publicScript });
}

test('kartu Majelis memenuhi lebar desktop dan memusatkan foto pada wajah', async ({ page }) => {
  await renderCarouselFixture(page, true);

  const card = page.locator('.leader-card.is-entering');
  const image = card.locator('.leader-card-image');
  await expect(image).toHaveAttribute('data-face-tracking', 'detected');
  await expect(image).toHaveCSS('object-fit', 'cover');
  await expect(card).toHaveCSS('max-width', '345px');
  await expect(card).toHaveCSS('height', '380px');
  await expect(image).toHaveCSS('object-position', '50% 0%');
});

test('foto Majelis tetap memenuhi kartu tanpa dukungan deteksi wajah', async ({ page }) => {
  await renderCarouselFixture(page, false);

  const card = page.locator('.leader-card.is-entering');
  const image = card.locator('.leader-card-image');
  await expect(image).toHaveAttribute('data-face-tracking', 'fallback');
  await expect(image).toHaveCSS('object-fit', 'cover');
  await expect(image).toHaveCSS('object-position', '50% 18%');
});

test('carousel Majelis tetap bisa dinavigasi tanpa tombol jeda atau putar otomatis', async ({ page }) => {
  await renderCarouselFixture(page, false);

  const slides = page.locator('[data-carousel-slide]');
  await expect(page.locator('[data-carousel-toggle]')).toHaveCount(0);
  await expect(slides.nth(0)).toBeVisible();
  await expect(slides.nth(1)).toBeHidden();

  await page.waitForTimeout(1100);
  await expect(slides.nth(0)).toBeVisible();
  await expect(slides.nth(1)).toBeHidden();

  await page.locator('[data-carousel-next]').click();
  await expect(slides.nth(0)).toBeHidden();
  await expect(slides.nth(1)).toBeVisible();
});
