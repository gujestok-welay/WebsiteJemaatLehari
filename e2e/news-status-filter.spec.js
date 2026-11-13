const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

test('memilih status berita langsung memperbarui daftar tanpa memuat ulang halaman', async ({ page }) => {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  await page.goto('/admin/berita');
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
  await expect(page.locator('.news-empty h2')).toHaveText('Tidak ada berita yang cocok');
  expect(pageLoads).toBe(0);
});
