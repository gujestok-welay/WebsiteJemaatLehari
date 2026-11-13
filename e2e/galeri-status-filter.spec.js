const { test, expect } = require('@playwright/test');
const fs = require('node:fs/promises');
const path = require('node:path');
const { adminEmail, adminPassword } = require('./test-credentials');

const activityTitles = [];

test.afterEach(async ({ page }) => {
  if (activityTitles.length === 0) return;

  await page.goto('/admin/galeri');
  for (const title of activityTitles) {
    const row = page.getByRole('row').filter({ hasText: title });
    const editLink = row.getByRole('link', { name: 'Edit' });
    let filenames = [];
    if (await editLink.count()) {
      await editLink.click();
      filenames = await page.locator('.gallery-admin-current-media img, .gallery-admin-photo-list img')
        .evaluateAll((images) => images.map((image) => new URL(image.src).pathname.split('/').pop()));
      await page.goto('/admin/galeri');
    }

    const activityRow = page.getByRole('row').filter({ hasText: title });
    const archiveButton = activityRow.getByRole('button', { name: 'Arsipkan' });
    if (await archiveButton.count()) {
      page.once('dialog', (dialog) => dialog.accept());
      await archiveButton.click();
      await expect(activityRow.locator('.gallery-admin-status')).toHaveText('Arsip');
    }

    for (const filename of filenames) {
      if (filename && /^[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)$/i.test(filename)) {
        await fs.rm(path.join(__dirname, '../writable/uploads/galeri', filename), { force: true });
      }
    }
  }
  activityTitles.length = 0;
});

async function loginAsAdmin(page) {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);
}

async function createActivity(page, title, status) {
  activityTitles.push(title);
  await page.goto('/admin/galeri/create');
  await page.getByLabel('Nama kegiatan').fill(title);
  await page.locator('select[name="status"]').selectOption(status);
  await page.locator('input[name="poster"]').setInputFiles(
    path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg')
  );
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);
}

test('filter status galeri langsung memperbarui daftar tanpa tombol atau reload halaman', async ({ page }) => {
  await loginAsAdmin(page);

  const draftTitle = `Uji filter status Draft ${Date.now()}`;
  const publishedTitle = `Uji filter status Terbit ${Date.now()}`;
  await createActivity(page, draftTitle, 'Draft');
  await createActivity(page, publishedTitle, 'Terbit');
  await page.goto('/admin/galeri');

  let pageLoads = 0;
  page.on('load', () => pageLoads += 1);
  await expect(page.getByRole('button', { name: 'Terapkan' })).toHaveCount(0);

  const responsePromise = page.waitForResponse((response) => {
    const url = new URL(response.url());
    return url.pathname.endsWith('/admin/galeri') && url.searchParams.get('status') === 'Terbit';
  });
  await page.getByLabel('Status publikasi').selectOption('Terbit');
  const response = await responsePromise;

  expect(response.request().headers()['x-partial']).toBe('1');
  await expect(page).toHaveURL(/status=Terbit/);
  await expect(page.getByLabel('Status publikasi')).toHaveValue('Terbit');
  await expect(page.getByRole('row').filter({ hasText: publishedTitle })).toBeVisible();
  await expect(page.getByRole('row').filter({ hasText: draftTitle })).toHaveCount(0);
  expect(pageLoads).toBe(0);
});
