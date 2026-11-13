const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

async function openProfile(page) {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);
  await page.goto('/admin/profil');
  await expect(page.getByRole('heading', { name: 'Profil Jemaat', level: 1 })).toBeVisible();
}

async function submitProfileFormAndWaitForReload(page, button) {
  await button.click();
  await page.waitForLoadState('networkidle', { timeout: 15000 });
}

test.beforeEach(async ({ page }) => {
  await openProfile(page);
});

test('tombol kelola membuka dan menutup semua formulir modul Profil & Majelis', async ({ page }) => {
  const moduleButtons = [
    { button: 'Kelola wilayah', modal: '#profile-territory-modal' },
    { button: 'Kelola data jemaat', modal: '#profile-census-modal' },
    { button: 'Kelola peta', modal: '#profile-map-modal' },
    { button: 'Edit konten Profil', modal: '#profile-content-modal' },
    { button: 'Edit data jemaat', modal: '#profile-data-modal' },
    { button: 'Kelola kartu Majelis', modal: '#profile-leadership-modal' },
  ];

  for (const { button, modal: selector } of moduleButtons) {
    await page.getByRole('button', { name: button }).click();
    const modal = page.locator(selector);
    await expect(modal).toBeVisible();
    await modal.getByRole('button', { name: 'Tutup' }).first().click();
    await expect(modal).toBeHidden();
  }
});

test('wilayah dapat menambah dan menghapus musim lalu menyimpan perubahan', async ({ page }) => {
  await page.getByRole('button', { name: 'Kelola wilayah' }).click();
  const modal = page.locator('#profile-territory-modal');
  await expect(modal).toBeVisible();

  const seasons = modal.locator('fieldset[data-content-list]').filter({ hasText: 'Daftar musim' });
  const initialSeasonCount = await seasons.locator('[data-content-item]').count();

  await modal.locator('details').last().locator('summary').click();
  await modal.getByRole('button', { name: 'Tambah musim' }).click();
  const newSeasonItem = seasons.locator('[data-content-item]').last();
  await newSeasonItem.locator('[name$="[period]"]').fill('E2E test');
  await newSeasonItem.locator('[name$="[title]"]').fill('Musim Playwright');
  await newSeasonItem.locator('[name$="[description]"]').fill('Keterangan dummy untuk tes otomatis.');
  await expect(seasons.locator('[data-content-item]')).toHaveCount(initialSeasonCount + 1);

  const itemToRemove = seasons.locator('[data-content-item]').first();
  await itemToRemove.getByRole('button', { name: 'Hapus item' }).click();
  await expect(seasons.locator('[data-content-item]')).toHaveCount(initialSeasonCount);

  await modal.locator('[name="territory[title]"]').fill('Wilayah khusus Playwright');
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan wilayah' }));

  await expect(page.locator('[data-profile-value="territoryStatus"]')).toHaveText('Tersimpan');
  await page.getByRole('button', { name: 'Kelola wilayah' }).click();
  await expect(page.locator('#profile-territory-modal [name="territory[title]"]')).toHaveValue('Wilayah khusus Playwright');
  const refreshedLastSeason = page.locator('#profile-territory-modal fieldset[data-content-list]').filter({ hasText: 'Daftar musim' }).locator('[data-content-item]').last();
  await expect(refreshedLastSeason.locator('[name$="[title]"]')).toHaveValue('Musim Playwright');
});

test('data KK dan jiwa dapat dihitung, dinavigasi, dan disimpan', async ({ page }) => {
  await page.getByRole('button', { name: 'Kelola data jemaat' }).click();
  const modal = page.locator('#profile-census-modal');
  await expect(modal).toBeVisible();

  await modal.locator('[name="census[household_count]"]').fill('210');
  await modal.getByRole('button', { name: 'Kelompok usia' }).click();
  await modal.locator('[name="census[age_rows][0][male]"]').fill('20');
  await expect(modal.locator('[data-profile-census-total]')).toHaveText('632');


  await expect(modal.locator('[data-census-step-count]')).toHaveText('Bagian 2 dari 3');
  await modal.getByRole('button', { name: 'Berikutnya' }).click();
  await expect(modal.locator('[data-census-step-count]')).toHaveText('Bagian 3 dari 3');
  await modal.getByRole('button', { name: 'Sebelumnya' }).click();
  await expect(modal.locator('[data-census-step-count]')).toHaveText('Bagian 2 dari 3');

  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan data jemaat' }));
  await expect(page.locator('[data-profile-value="householdCount"]')).toHaveText('210');

  await page.getByRole('button', { name: 'Kelola data jemaat' }).click();
  await expect(page.locator('#profile-census-modal [name="census[household_count]"]')).toHaveValue('210');
  await expect(page.locator('#profile-census-modal [data-profile-census-total]')).toHaveText('632');
});

test('peta dapat memperbarui keterangan dan menolak file bukan gambar', async ({ page }) => {
  await page.getByRole('button', { name: 'Kelola peta' }).click();
  const modal = page.locator('#profile-map-modal');
  await expect(modal).toBeVisible();

  const title = `Peta Playwright ${Date.now()}`;
  await modal.getByLabel('Judul bagian peta').fill(title);
  await modal.getByLabel('Alt text').fill('Peta dummy untuk pengujian otomatis');
  await modal.getByRole('button', { name: 'Simpan peta' }).click();
  await expect(modal).toBeHidden();
  await page.reload();

  await page.getByRole('button', { name: 'Kelola peta' }).click();
  const reopenedModal = page.locator('#profile-map-modal');
  await expect(reopenedModal.getByLabel('Judul bagian peta')).toHaveValue(title);
  await expect(reopenedModal.locator('[data-profile-map-modal-image]')).toHaveAttribute('alt', 'Peta dummy untuk pengujian otomatis');
  await reopenedModal.locator('[name="service_map"]').setInputFiles({
    name: 'bukan-gambar.txt',
    mimeType: 'text/plain',
    buffer: Buffer.from('file tes yang bukan gambar'),
  });
  await expect(reopenedModal.locator('[data-profile-file-error]')).toContainText('Format peta tidak valid');
});

test('teks profil publik dapat diedit dan tersimpan', async ({ page }) => {
  await page.getByRole('button', { name: 'Edit konten Profil' }).click();
  const modal = page.locator('#profile-content-modal');
  await expect(modal).toBeVisible();

  const title = `Profil Playwright ${Date.now()}`;
  await modal.locator('[name="content[hero][title]"]').fill(title);
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan konten Profil' }));

  await page.getByRole('button', { name: 'Edit konten Profil' }).click();
  await expect(page.locator('#profile-content-modal [name="content[hero][title]"]')).toHaveValue(title);
});

test('ringkasan konten Profil tampil seperti pilihan dan tetap membuka bagiannya', async ({ page }) => {
  await page.getByRole('button', { name: 'Edit konten Profil' }).click();
  const section = page.locator('#profile-content-modal #profile-content-hero');
  const summary = section.locator('summary');

  const styles = await summary.evaluate((element) => {
    const summaryStyle = getComputedStyle(element);
    const indicatorStyle = getComputedStyle(element, '::after');
    return {
      borderStyle: summaryStyle.borderStyle,
      borderRadius: summaryStyle.borderRadius,
      indicatorContent: indicatorStyle.content,
    };
  });

  expect(styles.borderStyle).toBe('solid');
  expect(styles.borderRadius).not.toBe('0px');
  expect(styles.indicatorContent).not.toBe('none');

  await summary.click();
  await expect(section).not.toHaveAttribute('open', '');
  await summary.click();
  await expect(section).toHaveAttribute('open', '');
});

test('ringkasan Periode dan Kartu Majelis menampilkan chevron kanan tanpa keterangan tambahan', async ({ page }) => {
  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  const modal = page.locator('#profile-leadership-modal');
  const period = modal.locator('#periode-panel');
  const members = modal.locator('#anggota-panel');
  const periodSummary = period.locator('summary');
  const memberSummary = members.locator('summary');
  const periodIndicator = await periodSummary.evaluate((element) => getComputedStyle(element, '::after'));
  const memberIndicator = await memberSummary.evaluate((element) => getComputedStyle(element, '::after'));

  expect(periodIndicator.content).not.toBe('none');
  expect(periodIndicator.right).not.toBe('auto');
  expect(memberIndicator.content).not.toBe('none');
  expect(memberIndicator.right).not.toBe('auto');
  await expect(periodSummary).toHaveText('Periode Majelis');
  await expect(memberSummary).toHaveText('Kartu Majelis');

  await page.setViewportSize({ width: 390, height: 844 });
  const mobileIndicator = await periodSummary.evaluate((element) => getComputedStyle(element, '::after'));
  expect(mobileIndicator.right).not.toBe('auto');

  const closedTransform = memberIndicator.transform;
  await memberSummary.click();
  await expect(members).toHaveAttribute('open', '');
  await expect.poll(() => memberSummary.evaluate((element) => getComputedStyle(element, '::after').transform))
    .not.toBe(closedTransform);
});

test('data profil jemaat dapat diubah dan disimpan', async ({ page }) => {
  await page.getByRole('button', { name: 'Edit data jemaat' }).click();
  const modal = page.locator('#profile-data-modal');
  await expect(modal).toBeVisible();

  const history = `Sejarah dummy Playwright ${Date.now()}`;
  await modal.getByLabel('Sejarah').fill(history);
  await modal.getByLabel('Visi dan misi').fill('Visi dan misi untuk pengujian lokal.');
  await modal.getByLabel('Alamat').fill('Alamat contoh khusus pengujian lokal.');
  await modal.getByLabel('Kontak resmi').fill('Kontak dummy Playwright');
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan data jemaat' }));

  await page.getByRole('button', { name: 'Edit data jemaat' }).click();
  await expect(page.locator('#profile-data-modal [name="history"]')).toHaveValue(history);
});

test('periode dan kartu Majelis dapat ditambah, diaktifkan, diedit, dan dihapus', async ({ page }) => {
  let firstPeriod = `E2E Periode ${Date.now()}`;
  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  let modal = page.locator('#profile-leadership-modal');
  const periodRow = (name) => modal.getByRole('row').filter({
    has: page.getByRole('cell', { name, exact: true }),
  });
  await modal.locator('[name="period_name"]').fill(firstPeriod);
  await modal.locator('[name="start_date"]').fill('2020-01-01');
  await modal.locator('[name="is_active"]').selectOption('0');
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan periode' }));

  const secondPeriod = `${firstPeriod} Aktif`;
  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  modal = page.locator('#profile-leadership-modal');
  await modal.locator('[name="period_name"]').fill(secondPeriod);
  await modal.locator('[name="start_date"]').fill('2021-01-01');
  await modal.locator('[name="is_active"]').selectOption('1');
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan periode' }));

  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  modal = page.locator('#profile-leadership-modal');
  let firstPeriodRow = periodRow(firstPeriod);
  await firstPeriodRow.getByRole('button', { name: 'Edit' }).click();
  firstPeriod = `${firstPeriod} diperbarui`;
  await modal.locator('[name="period_name"]').fill(firstPeriod);
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan perubahan periode' }));

  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  modal = page.locator('#profile-leadership-modal');
  firstPeriodRow = periodRow(firstPeriod);
  await submitProfileFormAndWaitForReload(page, firstPeriodRow.getByRole('button', { name: 'Aktifkan' }));

  const memberName = `Anggota E2E ${Date.now()}`;
  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  modal = page.locator('#profile-leadership-modal');
  await modal.locator('#anggota-panel summary').click();
  await modal.locator('[name="name"]').fill(memberName);
  await modal.locator('[name="position"]').fill('Jabatan pengujian');
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan anggota' }));

  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  modal = page.locator('#profile-leadership-modal');
  await modal.locator('#anggota-panel summary').click();
  let memberRow = modal.getByRole('row').filter({ hasText: memberName });
  await expect(memberRow).toBeVisible();
  await memberRow.getByRole('button', { name: 'Edit' }).click();
  await expect(modal.locator('[name="name"]')).toHaveValue(memberName);
  await expect(modal.getByRole('button', { name: 'Simpan perubahan anggota' }).locator('svg')).toBeVisible();
  await expect(modal.locator('#member_name')).toBeFocused();
  await modal.locator('#member-form').getByRole('button', { name: 'Batal mengubah' }).click();
  await expect(modal.locator('[name="name"]')).toHaveValue('');
  await memberRow.getByRole('button', { name: 'Edit' }).click();
  await modal.locator('[name="name"]').fill(`${memberName} diperbarui`);
  await submitProfileFormAndWaitForReload(page, modal.getByRole('button', { name: 'Simpan perubahan anggota' }));

  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  modal = page.locator('#profile-leadership-modal');
  await modal.locator('#anggota-panel summary').click();
  memberRow = modal.getByRole('row').filter({ hasText: `${memberName} diperbarui` });
  await expect(memberRow).toBeVisible();
  page.once('dialog', (dialog) => dialog.accept());
  await submitProfileFormAndWaitForReload(page, memberRow.getByRole('button', { name: 'Hapus' }));

  await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
  await page.locator('#profile-leadership-modal #anggota-panel summary').click();
  await expect(page.locator('#profile-leadership-modal')).toContainText('Belum ada anggota pada periode ini.');
});

test.describe('tampilan mobile Pengelolaan Majelis', () => {
  test.use({ viewport: { width: 491, height: 760 } });

  test('panel dapat digulir dan digunakan pada layar ponsel', async ({ page }) => {
    await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();

    let modal = page.locator('#profile-leadership-modal');
    const body = modal.locator('.offcanvas-body');
    await expect(modal).toBeVisible();
    await expect(modal).toHaveClass(/offcanvas-bottom/);
    await expect(modal).not.toHaveAttribute('aria-hidden', 'true');

    const scrollState = await body.evaluate((element) => {
      element.scrollTop = element.scrollHeight;
      return {
        scrollTop: element.scrollTop,
        scrollHeight: element.scrollHeight,
        clientHeight: element.clientHeight,
      };
    });
    expect(scrollState.scrollHeight).toBeGreaterThan(scrollState.clientHeight);
    expect(scrollState.scrollTop).toBeGreaterThan(0);

    const periodName = `E2E Mobile ${Date.now()}`;
    await modal.locator('[name="period_name"]').fill(periodName);
    await modal.locator('[name="start_date"]').fill('2026-01-01');
    await modal.locator('[name="is_active"]').selectOption('1');
    await submitProfileFormAndWaitForReload(page, modal.locator('#periode-panel [data-profile-submit]'));

    await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
    modal = page.locator('#profile-leadership-modal');
    await modal.locator('#anggota-panel summary').click();

    const memberName = `Anggota Mobile ${Date.now()}`;
    const memberForm = modal.locator('#member-form');
    await memberForm.locator('[name="name"]').fill(memberName);
    await memberForm.locator('[name="position"]').fill('Jabatan pengujian mobile');
    await submitProfileFormAndWaitForReload(page, memberForm.getByRole('button', { name: 'Simpan anggota' }));

    await page.getByRole('button', { name: 'Kelola kartu Majelis' }).click();
    modal = page.locator('#profile-leadership-modal');
    await modal.locator('#anggota-panel summary').click();
    const memberRow = modal.getByRole('row').filter({
      has: page.getByRole('cell', { name: memberName, exact: true }),
    });
    await expect(memberRow).toBeVisible();
    await expect(memberRow).toContainText('Jabatan pengujian mobile');

    page.once('dialog', (dialog) => dialog.accept());
    await submitProfileFormAndWaitForReload(page, memberRow.getByRole('button', { name: 'Hapus' }));
  });
});

test('pelayanan peribadahan membuka jadwal dan mendukung alur CRUD jadwal', async ({ page }) => {
  await page.getByRole('link', { name: 'Buka Jadwal Ibadah' }).click();
  await expect(page).toHaveURL(/admin\/jadwal$/);
  await expect(page.getByRole('heading', { name: 'Jadwal Ibadah', level: 1 })).toBeVisible();

  await page.getByRole('link', { name: 'Tambah jadwal', exact: true }).click();
  const modal = page.locator('#schedule-create-modal');
  await expect(modal).toBeVisible();

  const serviceName = `Ibadah E2E ${Date.now()}`;
  await modal.getByLabel('Jenis / nama ibadah').fill(serviceName);
  await modal.getByLabel('Tanggal').fill('2030-01-02');
  await modal.getByLabel('Waktu WIT').fill('09:30');
  await modal.getByLabel('Lokasi').fill('Lokasi khusus pengujian');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    modal.getByRole('button', { name: 'Simpan Jadwal' }).click(),
  ]);

  let scheduleRow = page.getByRole('row').filter({ hasText: serviceName });
  await expect(scheduleRow).toBeVisible();
  await expect(scheduleRow).toContainText('Belum ditentukan');
  await scheduleRow.getByRole('link', { name: 'Ubah' }).click();
  await page.locator('#lokasi').fill('Lokasi E2E diperbarui');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    page.getByRole('button', { name: 'Simpan perubahan' }).click(),
  ]);
  scheduleRow = page.getByRole('row').filter({ hasText: serviceName });
  await expect(scheduleRow).toContainText('Lokasi E2E diperbarui');

  page.once('dialog', (dialog) => dialog.accept());
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    scheduleRow.getByRole('button', { name: 'Batalkan' }).click(),
  ]);
  scheduleRow = page.getByRole('row').filter({ hasText: serviceName });
  await expect(scheduleRow).toContainText('Dibatalkan');

  page.once('dialog', (dialog) => dialog.accept());
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    scheduleRow.getByRole('button', { name: 'Aktifkan' }).click(),
  ]);
  scheduleRow = page.getByRole('row').filter({ hasText: serviceName });

  page.once('dialog', (dialog) => dialog.accept());
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    scheduleRow.getByRole('button', { name: 'Arsipkan' }).click(),
  ]);
  await page.locator('.schedule-status-tabs').getByRole('link', { name: /Arsip/ }).click();
  scheduleRow = page.getByRole('row').filter({ hasText: serviceName });
  await expect(scheduleRow).toContainText('Arsip');

  page.once('dialog', (dialog) => dialog.accept());
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    scheduleRow.getByRole('button', { name: 'Pulihkan' }).click(),
  ]);
  await expect(page.getByRole('row').filter({ hasText: serviceName })).toContainText('Aktif');
});