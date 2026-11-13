const { test, expect } = require('@playwright/test');
const fs = require('node:fs/promises');
const path = require('node:path');
const { adminEmail, adminPassword } = require('./test-credentials');
let cleanupActivityTitle = null;

test.afterEach(async ({ page }) => {
  if (cleanupActivityTitle && !/\/admin\/galeri\/edit\/\d+$/.test(page.url())) {
    const activityRow = page.getByRole('row').filter({ hasText: cleanupActivityTitle });
    if (await activityRow.count()) {
      await activityRow.getByRole('link', { name: 'Edit' }).click();
    }
  }

  const filenames = await page.locator('.gallery-admin-current-media img, .gallery-admin-photo-list img')
    .evaluateAll((images) => images.map((image) => new URL(image.src).pathname.split('/').pop()));

  for (const filename of filenames) {
    if (filename && /^[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)$/i.test(filename)) {
      await fs.rm(path.join(__dirname, '../writable/uploads/galeri', filename), { force: true });
    }
  }

  if (cleanupActivityTitle) {
    await page.goto('/admin/galeri');
    const activityRow = page.getByRole('row').filter({ hasText: cleanupActivityTitle });
    const archiveButton = activityRow.getByRole('button', { name: 'Arsipkan' });
    if (await archiveButton.count()) {
      page.once('dialog', (dialog) => dialog.accept());
      await archiveButton.click();
    }
  }
  cleanupActivityTitle = null;
});

async function loginAsAdmin(page) {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);
}

test('admin dapat membuka daftar dan formulir kegiatan', async ({ page }) => {
  await loginAsAdmin(page);

  await page.goto('/admin/galeri');
  await expect(page.getByRole('heading', { name: 'Galeri kegiatan', level: 1 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Belum ada kegiatan' })).toBeVisible();
  await page.getByRole('link', { name: 'Tambah kegiatan', exact: true }).click();

  await expect(page.getByRole('heading', { name: 'Tambah kegiatan', level: 1 })).toBeVisible();
  await expect(page.getByLabel('Nama kegiatan')).toBeVisible();
  await expect(page.getByLabel(/Poster atau flayer utama/)).toBeVisible();
  await expect(page.getByText('Disarankan rasio vertikal 2:3 (contoh: 1200 × 1800 px). Rasio lain tetap dapat diunggah.'))
    .toBeVisible();
  await expect(page.getByLabel(/Keterangan/)).toBeVisible();

  for (const width of [320, 390, 768, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    const pageWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => document.documentElement.clientWidth);
    expect(pageWidth, `formulir admin galeri tidak overflow pada ${width}px`).toBeLessThanOrEqual(viewportWidth);
  }
});

test('aksi pulihkan galeri menampilkan ikon dan label yang utuh', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/galeri/create');

  const title = `Uji foto bertahap pulihkan ${Date.now()}`;
  await page.getByLabel('Nama kegiatan').fill(title);
  await page.locator('input[name="poster"]').setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg'));
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();

  const activityRow = page.getByRole('row').filter({ hasText: title });
  page.once('dialog', (dialog) => dialog.accept());
  await activityRow.getByRole('button', { name: 'Arsipkan' }).click();
  const restoreButton = activityRow.getByRole('button', { name: 'Pulihkan', exact: true });
  for (const width of [1440, 390]) {
    await page.setViewportSize({ width, height: 900 });
    await expect(restoreButton).toBeVisible();
    await expect(restoreButton.locator('svg[aria-hidden="true"]')).toBeVisible();
    await expect(restoreButton.locator('span')).toHaveText('Pulihkan');

    const buttonBounds = await restoreButton.boundingBox();
    const labelBounds = await restoreButton.locator('span').boundingBox();
    expect(buttonBounds).not.toBeNull();
    expect(labelBounds).not.toBeNull();
    expect(labelBounds.x).toBeGreaterThanOrEqual(buttonBounds.x);
    expect(labelBounds.x + labelBounds.width).toBeLessThanOrEqual(buttonBounds.x + buttonBounds.width);
  }

  await restoreButton.click();
  await expect(page.getByText('Kegiatan berhasil dipulihkan.')).toBeVisible();
  await page.goto('/admin/galeri?status=Draft');
  await expect(page.getByRole('row').filter({ hasText: title }).getByRole('link', { name: 'Edit' })).toBeVisible();
});

test('form admin Galeri mudah dibaca dan menyesuaikan proporsi kolom per layar', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/galeri/create');

  const fieldLabel = page.locator('.gallery-admin-form-main > label > span:first-child').first();
  const helperText = page.locator('.gallery-admin-file small').first();
  expect(Number.parseFloat(await fieldLabel.evaluate((element) => getComputedStyle(element).fontSize)))
    .toBeGreaterThanOrEqual(16);
  expect(Number.parseFloat(await helperText.evaluate((element) => getComputedStyle(element).fontSize)))
    .toBeGreaterThanOrEqual(14);

  for (const [width, expectedColumns] of [[390, 1], [768, 2], [1440, 2]]) {
    await page.setViewportSize({ width, height: 900 });
    const form = page.locator('.gallery-admin-form');
    const columns = await form.evaluate((element) =>
      getComputedStyle(element).gridTemplateColumns.split(' ').length
    );
    expect(columns, `formulir Galeri memakai ${expectedColumns} kolom pada ${width}px`)
      .toBe(expectedColumns);

    const pageWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => document.documentElement.clientWidth);
    expect(pageWidth, `formulir admin galeri tidak overflow pada ${width}px`).toBeLessThanOrEqual(viewportWidth);
  }
});

test('dropzone Galeri menerima foto yang diseret dan dapat menghapus pilihan', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/galeri/create');

  const dropzones = page.locator('[data-gallery-upload-dropzone]');
  await expect(dropzones).toHaveCount(2);
  const photoDropzone = page.locator('[data-gallery-upload-dropzone="photos"]');
  await photoDropzone.evaluate((element) => {
    const files = new DataTransfer();
    files.items.add(new File(['foto dokumentasi'], 'foto-seret.jpg', { type: 'image/jpeg' }));
    element.dispatchEvent(new DragEvent('dragenter', { bubbles: true, cancelable: true, dataTransfer: files }));
    element.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: files }));
  });

  await expect(page.locator('[data-gallery-upload-files="photos"]')).toContainText('foto-seret.jpg');
  await expect(page.locator('[data-photo-selection-status]'))
    .toHaveText('1 foto baru dipilih. Total setelah disimpan: 1/30 foto.');

  await page.getByRole('button', { name: 'Hapus foto-seret.jpg dari pilihan' }).click();
  await expect(page.locator('[data-gallery-upload-files="photos"]')).toBeEmpty();
  await expect(page.locator('[data-photo-selection-status]'))
    .toHaveText('0 foto baru dipilih. Total setelah disimpan: 0/30 foto.');
});

test('memilih foto dokumentasi bertahap menambahkan file, bukan mengganti pilihan sebelumnya', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/galeri/create');

  const title = `Uji foto bertahap ${Date.now()}`;
  cleanupActivityTitle = title;
  await page.getByLabel('Nama kegiatan').fill(title);
  await page.locator('select[name="status"]').selectOption('Terbit');
  await page.locator('input[name="poster"]').setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg'));

  const photoInput = page.locator('input[name="photos[]"]');
  await photoInput.setInputFiles(path.join(__dirname, '../public/assets/images/gereja/Gambar-suasanaIbadah.jpeg'));
  await photoInput.setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita1.jpeg'));

  await expect.poll(() => photoInput.evaluate((input) => [...input.files].map((file) => file.name))).toEqual([
    'Gambar-suasanaIbadah.jpeg',
    'berita1.jpeg',
  ]);
  await expect(page.locator('[data-photo-selection-status]')).toHaveText('2 foto baru dipilih. Total setelah disimpan: 2/30 foto.');
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);

  const activityRow = page.getByRole('row').filter({ hasText: title   });

  await page.goto('galeri');
  await expect(page.getByRole('link', {
    name: `Lihat dokumentasi: ${title}`,
    exact: true,
  })).toBeVisible();
  await page.goto('/admin/galeri');

  const editLink = activityRow.getByRole('link', { name: 'Edit' });
  const archiveButton = activityRow.getByRole('button', { name: 'Arsipkan' });
  await expect(editLink).toBeVisible();
  await expect(editLink.locator('svg[aria-hidden="true"]')).toBeVisible();
  await expect(archiveButton).toBeVisible();
  await expect(archiveButton.locator('svg[aria-hidden="true"]')).toBeVisible();
  await editLink.click();
  await expect(page.locator('.gallery-admin-photo-list > label')).toHaveCount(2);

  await page.locator('input[name="photos[]"]').setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita3.jpeg'));
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);
  const finalActivityRow = page.getByRole('row').filter({ hasText: title });
  await finalActivityRow.getByRole('link', { name: 'Edit' }).click();
  await expect(page.locator('.gallery-admin-photo-list > label')).toHaveCount(3);

  await page.goto('/admin/galeri');
  await page.setViewportSize({ width: 1024, height: 900 });
  const rowActions = finalActivityRow.locator('.gallery-admin-actions > *');
  const actionTops = await rowActions.evaluateAll((items) =>
    items.map((item) => item.getBoundingClientRect().top)
  );
  expect(Math.abs(actionTops[0] - actionTops[1]), 'aksi tabel sejajar pada layar desktop').toBeLessThan(1);
  for (const label of await finalActivityRow.locator('.gallery-admin-cell-label').all()) {
    await expect(label).toBeHidden();
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await expect(page.getByRole('table')).toHaveCount(1);
  await expect(finalActivityRow.locator('.gallery-admin-cell-label')).toHaveCount(5);
  for (const label of await finalActivityRow.locator('.gallery-admin-cell-label').all()) {
    await expect(label).toBeVisible();
  }
  const mobileEditLink = finalActivityRow.getByRole('link', { name: 'Edit' });
  const mobileArchiveButton = finalActivityRow.getByRole('button', { name: 'Arsipkan' });
  await expect(mobileEditLink).toBeVisible();
  await expect(mobileArchiveButton).toBeVisible();
  for (const action of [mobileEditLink, mobileArchiveButton]) {
    const actionBounds = await action.boundingBox();
    const labelBounds = await action.locator('span').boundingBox();
    expect(actionBounds).not.toBeNull();
    expect(labelBounds).not.toBeNull();
    expect(labelBounds.x).toBeGreaterThanOrEqual(actionBounds.x);
    expect(labelBounds.x + labelBounds.width).toBeLessThanOrEqual(actionBounds.x + actionBounds.width);
  }
  const tableBounds = await page.locator('.gallery-admin-table-wrap').evaluate((element) => ({
    width: element.clientWidth,
    contentWidth: element.scrollWidth,
    rowWidth: element.querySelector('tbody tr').getBoundingClientRect().width,
  }));
  expect(tableBounds.contentWidth, 'daftar tidak membutuhkan gulir horizontal di ponsel').toBeLessThanOrEqual(tableBounds.width);
  expect(tableBounds.rowWidth, 'baris kartu tetap di dalam panel').toBeLessThanOrEqual(tableBounds.width);
  const pageScroll = await page.evaluate(() => {
    document.documentElement.style.scrollBehavior = 'auto';
    window.scrollTo({ left: 500, top: 0 });
    return window.scrollX;
  });
  expect(pageScroll, 'halaman tidak ikut bergulir horizontal ketika tabel digulir').toBe(0);

  await finalActivityRow.getByRole('link', { name: 'Edit' }).click();
  await page.locator('select[name="status"]').selectOption('Draft');
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);
});

test('poster dan foto pada detail kegiatan tampil ringkas di desktop dan tetap responsif', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/galeri/create');

  const title = `Uji ukuran dokumentasi ${Date.now()}`;
  cleanupActivityTitle = title;
  await page.getByLabel('Nama kegiatan').fill(title);
  await page.locator('select[name="status"]').selectOption('Terbit');
  await page.locator('input[name="poster"]').setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg'));
  const photoInput = page.locator('input[name="photos[]"]');
  await photoInput.setInputFiles(path.join(__dirname, '../public/assets/images/gereja/Gambar-suasanaIbadah.jpeg'));
  await photoInput.setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita1.jpeg'));
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto('/galeri');
  await page.getByRole('link', { name: `Lihat dokumentasi: ${title}`, exact: true }).click();
  const photos = page.locator('.gallery-detail-grid img');
  await expect(photos).toHaveCount(2);
  await expect.poll(() => photos.evaluateAll((images) => images.every((image) => image.naturalWidth > 0))).toBe(true);
  await expect(page.locator('.gallery-detail-poster')).toHaveCount(0);

  const desktop = await page.evaluate(() => {
    const images = [...document.querySelectorAll('.gallery-detail-image img')];
    return {
      columns: getComputedStyle(document.querySelector('.gallery-detail-grid')).gridTemplateColumns.split(' ').length,
      images: images.map((image) => {
        const bounds = image.getBoundingClientRect();
        return {
          naturalRatio: image.naturalWidth / image.naturalHeight,
          renderedRatio: bounds.width / bounds.height,
          width: bounds.width,
          height: bounds.height,
          fit: getComputedStyle(image).objectFit,
        };
      }),
    };
  });
  expect(desktop.columns, 'foto dokumentasi memakai dua kolom yang lebih lapang pada desktop').toBe(2);
  for (const image of desktop.images) {
    expect(image.renderedRatio, 'rasio foto asli dipertahankan').toBeCloseTo(image.naturalRatio, 2);
    expect(image.width, 'foto dokumentasi tidak tampil terlalu kecil').toBeGreaterThanOrEqual(280);
    expect(image.height, 'foto dokumentasi tetap memiliki batas tinggi').toBeLessThanOrEqual(544);
    expect(image.fit, 'foto tampil utuh tanpa dipotong').toBe('contain');
  }

  await page.setViewportSize({ width: 390, height: 844 });
  const mobile = await page.evaluate(() => {
    const images = [...document.querySelectorAll('.gallery-detail-image img')];
    return {
      documentWidth: document.documentElement.scrollWidth,
      viewportWidth: document.documentElement.clientWidth,
      images: images.map((image) => {
        const bounds = image.getBoundingClientRect();
        return {
          naturalRatio: image.naturalWidth / image.naturalHeight,
          renderedRatio: bounds.width / bounds.height,
        };
      }),
    };
  });
  await expect(page.locator('.gallery-detail-poster')).toHaveCount(0);
  expect(mobile.documentWidth).toBeLessThanOrEqual(mobile.viewportWidth);
  for (const image of mobile.images) {
    expect(image.renderedRatio, 'rasio foto asli tetap dipertahankan di ponsel').toBeCloseTo(image.naturalRatio, 2);
  }
  await page.locator('.gallery-detail-image-open').nth(1).dispatchEvent('click');
  const viewer = page.getByRole('dialog', { name: 'Dokumentasi foto' });
  await expect(viewer).toBeVisible();
  await expect(viewer.getByText('2 / 2')).toBeVisible();
  await expect(viewer.locator('img')).toHaveAttribute('src', await photos.nth(1).getAttribute('src'));
  const viewerSurface = await viewer.evaluate((dialog) => ({
    background: getComputedStyle(dialog).backgroundColor,
    backdropBackground: getComputedStyle(dialog, '::backdrop').backgroundColor,
    backdropFilter: getComputedStyle(dialog, '::backdrop').backdropFilter,
  }));
  expect(viewerSurface.background, 'permukaan viewer transparan').toBe('rgba(0, 0, 0, 0)');
  expect(viewerSurface.backdropBackground, 'lapisan belakang bukan ungu pekat').not.toBe('rgba(20, 14, 24, 0.88)');
  expect(viewerSurface.backdropFilter, 'halaman di belakang viewer diburamkan').toContain('blur(');
  const imageMotion = await viewer.locator('[data-photo-viewer-image]').evaluate((image) => {
    const style = getComputedStyle(image);
    return {
      name: style.animationName,
      duration: style.animationDuration,
    };
  });
  expect(imageMotion.name, 'foto memiliki animasi masuk yang halus').not.toBe('none');
  expect(Number.parseFloat(imageMotion.duration), 'animasi preview tidak terasa lambat').toBeLessThanOrEqual(0.5);

  await viewer.getByRole('button', { name: 'Foto selanjutnya' }).click();
  await expect(viewer.getByText('1 / 2')).toBeVisible();
  await viewer.getByRole('button', { name: 'Foto sebelumnya' }).click();
  await expect(viewer.getByText('2 / 2')).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(viewer).not.toBeVisible();

  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.locator('.gallery-detail-image-open').nth(1).dispatchEvent('click');
  const reducedMotionName = await viewer.locator('[data-photo-viewer-image]').evaluate((image) => getComputedStyle(image).animationName);
  expect(reducedMotionName, 'animasi preview dinonaktifkan bila pengguna mengurangi gerakan').toBe('none');
});

test('kegiatan mendukung 30 foto dan menampilkan semuanya dalam satu modal geser', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto('/admin/galeri/create');

  const title = `Uji foto bertahap batas 30 ${Date.now()}`;
  cleanupActivityTitle = title;
  await page.getByLabel('Nama kegiatan').fill(title);
  await page.locator('select[name="status"]').selectOption('Terbit');
  await page.locator('input[name="poster"]').setInputFiles(path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg'));

  const image = await fs.readFile(path.join(__dirname, '../public/assets/images/kegiatan/berita2.jpeg'));
  const photos = Array.from({ length: 30 }, (_, index) => ({
    name: `dokumentasi-${index + 1}.jpeg`,
    mimeType: 'image/jpeg',
    buffer: image,
  }));
  await page.locator('input[name="photos[]"]').setInputFiles(photos);
  await expect(page.locator('[data-photo-selection-status]'))
    .toHaveText('30 foto baru dipilih. Total setelah disimpan: 30/30 foto.');

  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);
  await page.goto('/galeri');
  await page.getByRole('link', { name: `Lihat dokumentasi: ${title}`, exact: true }).click();

  await expect(page.locator('.gallery-detail-grid')).toHaveCount(0);
  await page.getByRole('button', { name: 'Lihat 30 foto dokumentasi' }).click();
  const viewer = page.getByRole('dialog', { name: 'Dokumentasi foto' });
  await expect(viewer).toBeVisible();
  await expect(viewer.locator('img')).toHaveCount(1);
  await expect(viewer.getByText('1 / 30')).toBeVisible();
  await expect.poll(() => viewer.locator('img').evaluate((photo) => photo.naturalWidth)).toBeGreaterThan(0);

  await viewer.getByRole('button', { name: 'Foto selanjutnya' }).click();
  await expect(viewer.getByText('2 / 30')).toBeVisible();

  const stage = viewer.locator('[data-photo-viewer-stage]');
  await stage.dispatchEvent('pointerdown', {
    pointerId: 7,
    pointerType: 'touch',
    isPrimary: true,
    clientX: 300,
    clientY: 100,
    button: 0,
    buttons: 1,
  });
  await stage.dispatchEvent('pointerup', {
    pointerId: 7,
    pointerType: 'touch',
    isPrimary: true,
    clientX: 80,
    clientY: 100,
    button: 0,
    buttons: 0,
  });
  await expect(viewer.getByText('3 / 30')).toBeVisible();

  await page.goto('/admin/galeri');
  await page.getByRole('row').filter({ hasText: title }).getByRole('link', { name: 'Edit' }).click();
  await page.locator('select[name="status"]').selectOption('Draft');
  await page.getByRole('button', { name: 'Simpan kegiatan' }).click();
  await expect(page).toHaveURL(/admin\/galeri$/);
});
