const { test, expect } = require('@playwright/test');
const fs = require('node:fs/promises');
const path = require('node:path');
const { adminEmail, adminPassword } = require('./test-credentials');

test('modal berita menerima gambar kover dengan drag-drop dan dapat menghapus pilihannya', async ({ page }) => {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  await page.getByRole('link', { name: 'Tulis berita' }).click();
  const dropzone = page.locator('[data-news-cover-dropzone]');
  await expect(dropzone).toBeVisible();
  for (const width of [390, 1280]) {
    await page.setViewportSize({ width, height: 900 });
    const layout = await dropzone.evaluate((element) => {
      const zone = element.getBoundingClientRect();
      const field = element.closest('.rm-news-cover-field').getBoundingClientRect();
      return {
        zoneWidth: zone.width,
        fieldWidth: field.width,
        documentWidth: document.documentElement.scrollWidth,
        viewportWidth: document.documentElement.clientWidth,
      };
    });
    expect(layout.zoneWidth, `dropzone memenuhi bidang pada ${width}px`).toBe(layout.fieldWidth);
    expect(layout.documentWidth, `modal tidak overflow pada ${width}px`).toBeLessThanOrEqual(layout.viewportWidth);
  }

  const imageBase64 = (await fs.readFile(path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg')))
    .toString('base64');
  await dropzone.evaluate((element, base64) => {
    const files = new DataTransfer();
    const bytes = Uint8Array.from(atob(base64), (character) => character.charCodeAt(0));
    files.items.add(new File([bytes], 'kover-berita.jpg', { type: 'image/jpeg' }));
    element.dispatchEvent(new DragEvent('dragenter', { bubbles: true, cancelable: true, dataTransfer: files }));
    element.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: files }));
  }, imageBase64);

  const preview = page.locator('[data-news-cover-preview]');
  await expect(preview).toContainText('kover-berita.jpg');
  await expect(preview.locator('img')).toBeVisible();
  await expect.poll(() => preview.locator('img').evaluate((image) => image.naturalWidth)).toBeGreaterThan(0);
  await page.getByRole('button', { name: 'Hapus gambar kover' }).click();
  await expect(preview).toBeHidden();
  expect(await page.locator('#news-create-cover-image').evaluate((input) => input.files.length)).toBe(0);
});
