const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

test.use({ viewport: { width: 375, height: 812 } });

async function loginAsAdmin(page) {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);
}

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

test('pencarian jadwal memfilter saat diketik tanpa memuat ulang halaman', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/jadwal');

  const serviceName = `Ibadah Pencarian ${Date.now()}`;
  await page.getByRole('link', { name: 'Tambah jadwal', exact: true }).click();
  const modal = page.locator('#schedule-create-modal');
  await modal.getByLabel('Jenis / nama ibadah').fill(serviceName);
  await modal.getByLabel('Tanggal').fill('2030-01-02');
  await modal.getByLabel('Waktu WIT').fill('09:30');
  await modal.getByLabel('Lokasi').fill('Lokasi pengujian');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    modal.getByRole('button', { name: 'Simpan Jadwal' }).click(),
  ]);
  await expect(page.locator('.schedule-service-name')).toHaveText(serviceName);

  let documentNavigations = 0;
  page.on('request', (request) => {
    if (request.isNavigationRequest() && request.frame() === page.mainFrame()) documentNavigations += 1;
  });

  const search = page.getByRole('searchbox', { name: 'Cari jenis ibadah, lokasi, atau pelayan' });
  await search.pressSequentially('Pencarian', { delay: 20 });

  await expect.poll(() => new URL(page.url()).searchParams.get('q')).toBe('Pencarian');
  await expect(page.locator('.schedule-service-name')).toHaveText(serviceName);
  expect(documentNavigations).toBe(0);

  await page.locator('#schedule-search-input').fill('jadwal-tidak-ditemukan');
  await expect.poll(() => new URL(page.url()).searchParams.get('q')).toBe('jadwal-tidak-ditemukan');
  await expect(page.locator('.schedule-empty h2')).toHaveText('Tidak ada jadwal yang cocok');
  await expect(page.locator('.schedule-result-count')).toHaveText('0 jadwal ditampilkan');
  expect(documentNavigations).toBe(0);
});

test('pencarian jadwal tidak menampilkan tombol Cari', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/jadwal');

  await expect(page.locator('.schedule-search button[type="submit"]')).toHaveCount(0);
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

test('filter jadwal tidak menampilkan tombol Cari dan label modul mudah dibaca', async ({ page }) => {
  await loginAsAdmin(page);
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto('/admin/jadwal');

  const searchInput = page.locator('.schedule-search input');
  await expect(page.locator('.schedule-search button[type="submit"]')).toHaveCount(0);
  expect(await searchInput.evaluate((element) => element.getBoundingClientRect().height))
    .toBeGreaterThanOrEqual(42);

  const hasScheduleRows = await page.locator('.schedule-table tbody tr').count() > 0;
  const desktopTypography = await page.evaluate(() => ({
    metricLabel: parseFloat(getComputedStyle(document.querySelector('.schedule-metrics .metric-label')).fontSize),
    metricNote: parseFloat(getComputedStyle(document.querySelector('.schedule-metric-note')).fontSize),
    weekdayLabel: parseFloat(getComputedStyle(document.querySelector('.schedule-day-name')).fontSize),
    weekdayUnit: parseFloat(getComputedStyle(document.querySelector('.schedule-day-unit')).fontSize),
    tableHeader: document.querySelector('.schedule-table th')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-table th')).fontSize)
      : null,
    date: document.querySelector('.schedule-date')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-date')).fontSize)
      : null,
    time: document.querySelector('.schedule-date span + span')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-date span + span')).fontSize)
      : null,
    action: document.querySelector('.schedule-action-label')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-action-label')).fontSize)
      : null,
    emptyMessage: document.querySelector('.schedule-empty p')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-empty p')).fontSize)
      : null,
    resultCount: parseFloat(getComputedStyle(document.querySelector('.schedule-result-count')).fontSize),
  }));
  expect(desktopTypography.metricLabel).toBeGreaterThanOrEqual(14);
  expect(desktopTypography.metricNote).toBeGreaterThanOrEqual(14);
  expect(desktopTypography.weekdayLabel).toBeGreaterThanOrEqual(13);
  expect(desktopTypography.weekdayUnit).toBeGreaterThanOrEqual(12);
  expect(desktopTypography.resultCount).toBeGreaterThanOrEqual(14);
  if (hasScheduleRows) {
    expect(desktopTypography.tableHeader).toBeGreaterThanOrEqual(13);
    expect(desktopTypography.date).toBeGreaterThanOrEqual(14);
    expect(desktopTypography.time).toBeGreaterThanOrEqual(13);
    expect(desktopTypography.action).toBeGreaterThanOrEqual(12);
  } else {
    expect(desktopTypography.emptyMessage).toBeGreaterThanOrEqual(14);
  }

  await page.setViewportSize({ width: 390, height: 844 });
  const mobileTypography = await page.evaluate(() => ({
    metricColumns: getComputedStyle(document.querySelector('.schedule-metrics')).gridTemplateColumns.split(' ').length,
    weekdayColumns: getComputedStyle(document.querySelector('.schedule-days')).gridTemplateColumns.split(' ').length,
    cellLabel: document.querySelector('.schedule-table td')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-table td'), '::before').fontSize)
      : null,
    status: document.querySelector('.schedule-status')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-status')).fontSize)
      : null,
    emptyMessage: document.querySelector('.schedule-empty p')
      ? parseFloat(getComputedStyle(document.querySelector('.schedule-empty p')).fontSize)
      : null,
  }));
  expect(mobileTypography.metricColumns).toBe(2);
  expect(mobileTypography.weekdayColumns).toBe(4);
  if (hasScheduleRows) {
    expect(mobileTypography.cellLabel).toBeGreaterThanOrEqual(13);
    expect(mobileTypography.status).toBeGreaterThanOrEqual(13);
  } else {
    expect(mobileTypography.emptyMessage).toBeGreaterThanOrEqual(14);
  }
});

test('form jadwal memakai label dan input yang nyaman dibaca di desktop maupun ponsel', async ({ page }) => {
  await loginAsAdmin(page);
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto('/admin/jadwal');
  await page.getByRole('link', { name: 'Tambah jadwal', exact: true }).click();
  const modal = page.locator('#schedule-create-modal');
  await expect(modal).toBeVisible();

  const formTypography = await modal.evaluate((element) => ({
    label: parseFloat(getComputedStyle(element.querySelector('.rm-field label')).fontSize),
    input: parseFloat(getComputedStyle(element.querySelector('.rm-field .form-control')).fontSize),
    intro: parseFloat(getComputedStyle(element.querySelector('.rm-description')).fontSize),
    columns: getComputedStyle(element.querySelector('.rm-form-grid')).gridTemplateColumns.split(' ').length,
  }));
  expect(formTypography.label).toBeGreaterThanOrEqual(16);
  expect(formTypography.input).toBeGreaterThanOrEqual(16);
  expect(formTypography.intro).toBeGreaterThanOrEqual(16);
  expect(formTypography.columns).toBe(2);

  await page.setViewportSize({ width: 390, height: 844 });
  const mobileForm = await modal.evaluate((element) => ({
    columns: getComputedStyle(element.querySelector('.rm-form-grid')).gridTemplateColumns.split(' ').length,
    modalWidth: element.scrollWidth,
    modalClientWidth: element.clientWidth,
  }));
  expect(mobileForm.columns).toBe(1);
  expect(mobileForm.modalWidth).toBeLessThanOrEqual(mobileForm.modalClientWidth);
});
