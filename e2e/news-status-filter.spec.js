const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

const newsTitles = [];

test.afterEach(async ({ page }) => {
  if (newsTitles.length === 0) return;

  await page.goto('/admin/berita');
  for (const title of newsTitles) {
    const archiveButton = page.getByRole('button', { name: `Arsipkan berita: ${title}` });
    if (await archiveButton.count()) {
      page.once('dialog', (dialog) => dialog.accept());
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        archiveButton.click(),
      ]);
    }
  }
  newsTitles.length = 0;
});

async function createNews(page, title, status) {
  newsTitles.push(title);
  await page.goto('/admin/berita');
  await page.getByRole('link', { name: 'Tambah berita' }).click();
  const modal = page.locator('#news-create-modal');
  await expect(modal).toBeVisible();
  await page.getByLabel('Judul', { exact: true }).fill(title);
  await page.getByLabel('Ringkasan').fill(`Ringkasan ${title}`);
  await page.getByLabel('Isi berita').fill(`Isi ${title}`);
  await page.getByLabel('Status publikasi').selectOption(status);
  await page.getByRole('button', { name: 'Simpan Berita' }).click();
  await expect(page).toHaveURL(/admin\/berita$/);
}

test('memilih status berita langsung memperbarui daftar tanpa memuat ulang halaman', async ({ page }) => {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  const draftTitle = `Uji filter status Draft ${Date.now()}`;
  const publishedTitle = `Uji filter status Terbit ${Date.now()}`;
  await createNews(page, draftTitle, 'Draft');
  await createNews(page, publishedTitle, 'Terbit');
  await page.waitForLoadState('load');

  let pageLoads = 0;
  page.on('load', () => pageLoads += 1);

  const responsePromise = page.waitForResponse((response) => {
    const url = new URL(response.url());
    return url.pathname.endsWith('/admin/berita') && url.searchParams.get('status') === 'Terbit';
  }, { timeout: 3000 });

  await page.getByLabel('Filter status berita').selectOption('Terbit');
  const response = await responsePromise;

  expect(response.request().headers()['x-partial']).toBe('1');
  await expect(page).toHaveURL(/status=Terbit/);
  await expect(page.getByLabel('Filter status berita')).toHaveValue('Terbit');
  const publishedRow = page.getByRole('row').filter({ hasText: publishedTitle });
  await expect(publishedRow).toBeVisible();
  await expect(publishedRow.locator('.news-status')).toHaveText('Terbit');
  await expect(page.getByRole('row').filter({ hasText: draftTitle })).toHaveCount(0);
  const displayedStatuses = await page.locator('.news-table tbody .news-status').allTextContents();
  expect(displayedStatuses.every((status) => status === 'Terbit')).toBe(true);
  expect(pageLoads).toBe(0);
});
