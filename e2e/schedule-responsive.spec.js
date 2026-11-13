const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

test.use({ viewport: { width: 375, height: 812 } });

test('filter status mempertahankan fokus pada tab status yang dipilih', async ({ page }) => {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  await page.goto('/admin/jadwal');
  await page.locator('.schedule-days a').first().click();
  await expect(page).toHaveURL(/hari=/);
  await expect(page.locator('[data-schedule-controls]')).not.toHaveAttribute('aria-busy', 'true');
  await page.locator('.schedule-status-tabs').scrollIntoViewIfNeeded();
  const scrollYBeforeStatusFilter = await page.evaluate(() => window.scrollY);

  await page.locator('.schedule-status-tabs a[href*="cancelled=0"]').click();
  await expect(page).toHaveURL(/cancelled=0.*hari=/);
  await expect(page.locator('.schedule-status-tabs a[aria-current="page"]')).toBeFocused();
  await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(scrollYBeforeStatusFilter);
});

test('halaman jadwal tidak meluap secara horizontal pada layar ponsel', async ({ page }) => {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  await page.goto('/admin/jadwal');
  await expect(page.getByRole('heading', { name: 'Jadwal Ibadah', level: 1 })).toBeVisible();
  await page.getByRole('link', { name: 'Tambah jadwal', exact: true }).click();
  const modal = page.locator('#schedule-create-modal');
  await modal.getByLabel('Jenis / nama ibadah').fill(`Ibadah Mobile ${Date.now()}`);
  await modal.getByLabel('Tanggal').fill('2030-01-02');
  await modal.getByLabel('Waktu WIT').fill('09:30');
  await modal.getByLabel('Lokasi').fill('Lokasi pengujian');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    modal.getByRole('button', { name: 'Simpan Jadwal' }).click(),
  ]);

  const documentWidth = await page.evaluate(() => ({
    clientWidth: document.documentElement.clientWidth,
    scrollWidth: document.documentElement.scrollWidth,
  }));
  expect(documentWidth.scrollWidth).toBeLessThanOrEqual(documentWidth.clientWidth);
});
