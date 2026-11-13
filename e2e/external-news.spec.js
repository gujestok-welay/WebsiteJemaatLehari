const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

test('Media Center menyimpan tautan berita eksternal dengan ringkasan dan atribusi', async ({ page }) => {
  const title = `Liputan eksternal ${Date.now()}`;

  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  await page.goto('/admin/berita');
  await page.getByRole('link', { name: 'Tambah berita' }).click();
  const modal = page.locator('#news-create-modal');
  await expect(modal).toBeVisible();
  await page.setViewportSize({ width: 390, height: 844 });
  await expect(page.getByLabel('Tautan berita asli')).toBeHidden();
  await expect(page.getByRole('button', { name: 'Ambil judul dan sumber dari tautan' })).toBeHidden();
  await expect(page.getByLabel('Nama sumber')).toBeHidden();

  await page.getByLabel('Jenis berita').selectOption('external');
  await expect(page.locator('[data-news-content-field]')).toBeHidden();
  await expect(page.getByLabel('Tautan berita asli')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Ambil judul dan sumber dari tautan' })).toBeVisible();
  await expect(page.getByLabel('Nama sumber')).toBeVisible();

  await page.getByLabel('Jenis berita').selectOption('internal');
  await expect(page.getByLabel('Tautan berita asli')).toBeHidden();
  await expect(page.getByRole('button', { name: 'Ambil judul dan sumber dari tautan' })).toBeHidden();
  await expect(page.getByLabel('Nama sumber')).toBeHidden();
  await page.getByLabel('Jenis berita').selectOption('external');

  const layout = await modal.evaluate((element) => ({
    modalWidth: element.getBoundingClientRect().width,
    documentWidth: document.documentElement.scrollWidth,
    viewportWidth: document.documentElement.clientWidth,
  }));
  expect(layout.modalWidth).toBeLessThanOrEqual(layout.viewportWidth);
  expect(layout.documentWidth).toBeLessThanOrEqual(layout.viewportWidth);

  await page.getByLabel('Tautan berita asli').fill('http://127.0.0.1/admin');
  await page.getByRole('button', { name: 'Ambil judul dan sumber dari tautan' }).click();
  await expect(page.locator('[data-news-metadata-status]')).toContainText('domain publik');

  await page.route('**/admin/berita/metadata', (route) => route.fulfill({
    status: 200,
    contentType: 'application/json',
    body: JSON.stringify({
      title: 'Ketua MJ Leahari Ajak Anak GPM Kembangkan Talenta',
      source_name: 'Pelagandong',
      summary: 'Anak dan remaja diajak mengembangkan talenta untuk melayani gereja dan masyarakat.',
      image_url: 'https://images.example.org/cover-from-source.jpg',
      image_available: true,
    }),
  }));
  await page.route('https://images.example.org/cover-from-source.jpg', (route) => route.fulfill({
    status: 200,
    contentType: 'image/png',
    body: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC', 'base64'),
  }));
  await page.getByLabel('Tautan berita asli').fill(
    'https://pelagandong.com/2026/06/28/ketua-mj-leahari-ajak-anak-gpm-kembangkan-talenta-untuk-melayani/',
  );
  await page.getByRole('button', { name: 'Ambil judul dan sumber dari tautan' }).click();
  await expect(page.getByLabel('Ringkasan')).toHaveValue(
    'Anak dan remaja diajak mengembangkan talenta untuk melayani gereja dan masyarakat.',
  );
  await expect(page.locator('[data-news-source-image-preview]')).toBeVisible();
  await expect(page.locator('[data-news-source-image-preview] img')).toHaveAttribute(
    'src',
    'https://images.example.org/cover-from-source.jpg',
  );
  expect(await page.locator('[data-news-use-source-image]').evaluate((input) => input.required)).toBe(true);
  await expect(page.locator('[data-news-source-image-url]')).toHaveValue(
    'https://images.example.org/cover-from-source.jpg',
  );

  await page.getByLabel('Judul', { exact: true }).fill(title);
  await page.getByLabel('Nama sumber').fill('Pelagandong');
  await page.getByLabel('Ringkasan').fill('Ringkasan mandiri untuk berita dari Pelagandong.');
  await page.getByLabel('Status publikasi').selectOption('Terbit');
  await page.locator('#news-create-cover-image').setInputFiles({
    name: 'cover.png',
    mimeType: 'image/png',
    buffer: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC', 'base64'),
  });
  expect(await page.locator('[data-news-use-source-image]').evaluate((input) => input.required)).toBe(false);
  await page.getByRole('button', { name: 'Simpan Berita' }).click();
  await expect(page).toHaveURL(/admin\/berita$/);

  await page.goto('/berita');
  const card = page.locator('.berita-card').filter({ hasText: title });
  await expect(card).toContainText('Pelagandong');
  await card.locator('a').first().click();

  await expect(page.getByRole('heading', { name: title })).toBeVisible();
  await expect(page.locator('.news-detail-meta').getByText('Berita dari Pelagandong')).toBeVisible();
  await expect(page.locator('.news-detail-cover img')).toBeVisible();
  await expect(page.locator('.news-detail-cover img')).toHaveAttribute('src', /berita\/gambar\//);
  await expect(page.locator('.news-detail-summary')).toHaveText('Ringkasan mandiri untuk berita dari Pelagandong.');
  await expect(page.getByRole('link', { name: /Baca berita asli/ })).toHaveAttribute(
    'href',
    'https://pelagandong.com/2026/06/28/ketua-mj-leahari-ajak-anak-gpm-kembangkan-talenta-untuk-melayani/',
  );
  await expect(page.locator('.news-detail-copy')).toHaveCount(0);
});
