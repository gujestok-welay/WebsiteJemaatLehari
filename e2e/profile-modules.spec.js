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
  const shouldReload = await button.evaluate((element) => element.form.hasAttribute('data-profile-reload'));
  const reload = shouldReload ? page.waitForEvent('load') : null;
  await button.click();
  await expect(page.locator('.news-alert-success, [data-profile-form-message].is-success').first()).toBeVisible({ timeout: 15000 });
  await expect(page).toHaveURL(/admin\/profil\/kelola\//);
  if (reload) await reload;
}

async function openEditor(page, module) {
  await page.goto(`/admin/profil/kelola/${module}`);
  await expect(page.locator('[data-profile-editor-page]')).toBeVisible();
}

test.beforeEach(async ({ page }) => {
  await openProfile(page);
});

test('tombol Kelola membuka halaman khusus setiap modul dan menyediakan jalan kembali', async ({ page }) => {
  const moduleLinks = [
    { label: 'Kelola wilayah', module: 'territory' },
    { label: 'Kelola data jemaat', module: 'census' },
    { label: 'Kelola pelayanan', module: 'worship' },
    { label: 'Kelola peta', module: 'map' },
    { label: 'Edit konten Profil', module: 'content' },
    { label: 'Edit data jemaat', module: 'data' },
    { label: 'Kelola kartu Majelis', module: 'leadership' },
  ];

  for (const { label, module } of moduleLinks) {
    const link = page.getByRole('link', { name: label });
    await expect(link).toHaveAttribute('href', new RegExp(`/admin/profil/kelola/${module}$`));
    await link.click();
    await expect(page.locator(`#profile-${module}-editor`)).toBeVisible();
    await expect(page.locator('[data-rm]')).toHaveCount(0);
    await page.getByRole('link', { name: 'Kembali ke Profil' }).click();
    await expect(page.getByRole('heading', { name: 'Profil Jemaat', level: 1 })).toBeVisible();
  }
});

test('halaman editor menggunakan gulir halaman penuh pada desktop dan ponsel', async ({ page }) => {
  for (const width of [1280, 390]) {
    await page.setViewportSize({ width, height: 844 });

    for (const module of ['territory', 'census', 'map', 'content', 'data', 'leadership']) {
      await openEditor(page, module);
      const body = page.locator(`#profile-${module}-editor .profile-modal-body`);
      await expect(body).toBeVisible();
      await expect(page.locator('[data-rm]')).toHaveCount(0);
      const scrollStyle = await body.evaluate((element) => getComputedStyle(element).maxHeight);
      expect(scrollStyle).toBe('none');
    }
  }
});

test('konten editor berada lebih dekat ke navbar pada desktop dan ponsel', async ({ page }) => {
  for (const [width, maximumGap] of [[1280, 36], [390, 26]]) {
    await page.setViewportSize({ width, height: 844 });
    await openEditor(page, 'territory');

    const gap = await page.locator('.profile-manage-page').evaluate((content) => {
      const topbar = document.querySelector('.admin-topbar');
      return content.getBoundingClientRect().top - topbar.getBoundingClientRect().bottom;
    });

    expect(gap, `jarak navbar ke konten pada viewport ${width}px`).toBeLessThanOrEqual(maximumGap);
  }
});

test('editor mengisi lebar konten dan tidak dibungkus kartu', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 900 });
  await openEditor(page, 'content');

  const layout = await page.locator('#profile-content-editor').evaluate((surface) => {
    const frame = surface.querySelector('.profile-editor-frame');
    const panel = surface.querySelector('.profile-editor-panel');
    const surfaceWidth = surface.getBoundingClientRect().width;

    return {
      widthDifference: Math.abs(surfaceWidth - frame.getBoundingClientRect().width),
      borderWidth: getComputedStyle(panel).borderWidth,
      backgroundColor: getComputedStyle(panel).backgroundColor,
      borderRadius: getComputedStyle(panel).borderRadius,
    };
  });

  expect(layout.widthDifference).toBeLessThanOrEqual(1);
  expect(layout.borderWidth).toBe('0px');
  expect(layout.backgroundColor).toBe('rgba(0, 0, 0, 0)');
  expect(layout.borderRadius).toBe('0px');
});

test('editor Wilayah memakai langkah terpisah yang bisa dipilih langsung tanpa kehilangan isian', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');
  const steps = editor.locator('[data-profile-step-target]');
  const panels = editor.locator('[data-profile-step-panel]');

  await expect(editor.getByRole('navigation', { name: 'Langkah pengisian' })).toBeVisible();
  await expect(steps).toHaveCount(6);
  await expect(panels).toHaveCount(6);
  await expect(panels.nth(0)).toBeVisible();
  await expect(panels.nth(1)).toBeHidden();

  await editor.locator('[name="territory[title]"]').fill('Wilayah sementara');
  await steps.nth(3).click();

  await expect(steps.nth(3)).toHaveAttribute('aria-current', 'step');
  await expect(editor.locator('[data-profile-step-count]')).toHaveText('Bagian 4 dari 6');
  await expect(panels.nth(3)).toBeVisible();
  await expect(panels.nth(0)).toBeHidden();
  await steps.nth(0).click();
  await expect(editor.locator('[name="territory[title]"]')).toHaveValue('Wilayah sementara');
});

test('editor Konten Profil memakai langkah yang sama dan dapat kembali ke bagian lain', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  const steps = editor.locator('[data-profile-step-target]');
  const panels = editor.locator('[data-profile-step-panel]');

  await expect(editor.getByRole('navigation', { name: 'Langkah pengisian' })).toBeVisible();
  await expect(steps).toHaveCount(await panels.count());
  await expect(panels.first()).toBeVisible();
  await expect(panels.nth(1)).toBeHidden();
  await expect(editor.locator('#profile-content-settings')).toBeHidden();

  const lastStep = steps.last();
  await lastStep.click();
  await expect(lastStep).toHaveAttribute('aria-current', 'step');
  await expect(panels.last()).toBeVisible();
  await expect(panels.first()).toBeHidden();

  await steps.first().click();
  await expect(panels.first()).toBeVisible();
  await expect(editor.locator('#profile-content-hero [name="content[hero][description]"]')).toBeVisible();
  await expect(editor.locator('#profile-content-settings [name="content[hero][title]"]')).toBeHidden();
  await lastStep.click();
  await expect(editor.locator('#profile-content-settings')).toBeHidden();
});

test('editor Konten Profil membedakan langkah yang pernah dibuka dari yang selesai', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  const steps = editor.locator('[data-profile-step-target]');

  await steps.last().click();

  await expect(steps.last()).toHaveAttribute('aria-current', 'step');
  await expect(editor.locator('[data-profile-step-nav] .is-complete')).toHaveCount(0);
  await expect(steps.last().locator('xpath=..')).toHaveClass(/is-visited/);
  await expect(steps.first().locator('xpath=..')).toHaveClass(/is-visited/);
  await expect(steps.nth(1).locator('xpath=..')).not.toHaveClass(/is-visited/);

  await steps.first().click();
  await expect(steps.first().locator('xpath=..')).toHaveClass(/is-visited/);
});

test('editor Konten Profil memisahkan isi dari pengaturan judul dan label', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  const settings = editor.locator('#profile-content-settings');

  await expect(editor.locator('#profile-content-settings-nav')).toHaveCount(0);
  await expect(settings).toBeHidden();
  await expect(settings.locator('[name="content[hero][title]"]')).toHaveCount(1);
  await expect(settings.locator('[name="content[hero][badge]"]')).toHaveCount(1);
  await expect(settings.locator('[name="content[stats][0][label]"]')).toHaveCount(1);
  await expect(settings.locator('[name="content[leadership][members_title]"]')).toHaveCount(1);
  await expect(editor.locator('#profile-content-hero [name="content[hero][title]"]')).toHaveCount(0);
  await expect(editor.locator('#profile-content-hero [name="content[hero][description]"]')).toHaveCount(1);
  await expect(editor.locator('#profile-content-stats [name="content[stats][0][label]"]')).toHaveCount(0);
  await expect(editor.locator('#profile-content-stats [name="content[stats][0][value]"]')).toHaveCount(1);
  await expect(editor.locator('#profile-content-leadership [name="content[leadership][members_empty]"]')).toHaveCount(1);
  await expect(editor.locator('#profile-content-leadership [name="content[leadership][members_title]"]')).toHaveCount(0);
});

test('editor Konten Profil memberi label kategori pada setiap ringkasan angka', async ({ page }) => {
  await openEditor(page, 'content');

  await expect(page.locator('#profile-content-stats fieldset.profile-content-object legend')).toHaveText([
    'Jiwa',
    'Kepala Keluarga',
    'Wilayah Pelayanan',
    'Luas Wilayah',
  ]);
});

test('editor Konten Profil menyimpan pengaturan teknis tanpa menampilkannya', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');

  for (const [field, value] of [
    ['page_title', 'Profil'],
    ['meta_description', 'Pratinjau website informasi Jemaat Leahari. Sebagian informasi masih menunggu konfirmasi resmi.'],
    ['badge', 'Profil Jemaat'],
  ]) {
    const name = `content[hero][${field}]`;
    await expect(editor.locator(`input[type="hidden"][name="${name}"]`)).toHaveValue(value);
    await expect(editor.locator(`[name="${name}"]:not([type="hidden"])`)).toHaveCount(0);
    await expect(editor.locator(`#profile-content-hero-${field}`)).toHaveCount(0);
    await expect(editor.locator(`#profile-content-hero-${field}-help`)).toHaveCount(0);
  }

  for (const [name, value] of [
    ['content[hero][stats_aria_label]', 'Ringkasan profil Jemaat'],
    ['content[hero][map_link_label]', 'Google Maps'],
    ['content[hero][links][0][target]', 'wilayah'],
    ['content[stats][0][icon]', 'bi-people'],
  ]) {
    await expect(editor.locator(`input[type="hidden"][name="${name}"]`)).toHaveValue(value);
    await expect(editor.locator(`[name="${name}"]:not([type="hidden"])`)).toHaveCount(0);
  }
});

test('pengaturan teknis Konten Profil disembunyikan tanpa menghapus nilai dari formulir', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  const settings = editor.locator('#profile-content-settings');

  await expect(editor.locator('#profile-content-settings-nav')).toHaveCount(0);
  await expect(settings).toBeHidden();
  await expect(settings.locator('[name="content[hero][title]"]')).toHaveCount(1);
  await expect(settings.locator('[name="content[stats][0][label]"]')).toHaveCount(1);
  await expect(settings.locator('[name="content[leadership][members_title]"]')).toHaveCount(1);
});

test('editor Konten Profil menjelaskan pengaturan yang tersedia dan mengelompokkannya', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  const settings = editor.locator('#profile-content-settings');

  await expect(editor.locator('#profile-content-settings-nav')).toHaveCount(0);
  await expect(settings).toBeHidden();
  await expect(settings.locator('[name="content[hero][page_title]"]')).toBeHidden();

  const statsLabel = settings.locator('[name="content[hero][stats_aria_label]"]');
  await expect(statsLabel).toHaveAttribute('type', 'hidden');

  await editor.getByRole('button', { name: 'Majelis dan kontak' }).click();
  const membersVisibility = editor.locator('[name="content[leadership][show_members]"][type="checkbox"]');
  const visibilityHintId = await membersVisibility.getAttribute('aria-describedby');
  await expect(editor.locator(`#${visibilityHintId}`)).toContainText('tampil pada halaman Profil');
});

test('editor Konten Profil hanya menyimpan perubahan tanpa menyediakan pratinjau', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  const descriptionField = editor.locator('#profile-content-hero [name="content[hero][description]"]');
  const titleField = editor.locator('#profile-content-settings [name="content[hero][title]"]');
  const originalDescription = await descriptionField.inputValue();
  const originalTitle = await titleField.inputValue();
  await expect(editor.getByRole('button', { name: 'Pratinjau perubahan' })).toHaveCount(0);
  await expect(editor.locator('[name="content[hero][preview_notice_lead]"]')).toHaveCount(0);
  await expect(editor.locator('[name="content[hero][preview_notice_body]"]')).toHaveCount(0);

  const removedPreviewEndpoint = await page.request.post('admin/profil/preview-page-content');
  expect(removedPreviewEndpoint.status()).toBe(404);

  const updatedDescription = `${originalDescription} Perubahan tersimpan.`;
  await descriptionField.fill(updatedDescription);
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan konten Profil' }));
  await expect(page.locator('#profile-content-editor #profile-content-hero [name="content[hero][description]"]')).toHaveValue(updatedDescription);
  await expect(page.locator('#profile-content-editor #profile-content-settings [name="content[hero][title]"]')).toHaveValue(originalTitle);

  await page.goto('/profil');
  await expect(page.locator('.profil-hero h1')).toHaveText(originalTitle);
  await expect(page.locator('.profil-hero p')).toHaveText(updatedDescription);
  await expect(page.locator('.profile-edit-preview-notice, .profile-public-notice')).toHaveCount(0);

  await openEditor(page, 'content');
  const restoredEditor = page.locator('#profile-content-editor');
  await restoredEditor.locator('#profile-content-hero [name="content[hero][description]"]').fill(originalDescription);
  await submitProfileFormAndWaitForReload(page, page.locator('#profile-content-editor').getByRole('button', { name: 'Simpan konten Profil' }));
});

test('navigasi meminta konfirmasi saat editor memiliki perubahan belum disimpan', async ({ page }) => {
  await openEditor(page, 'territory');
  const field = page.locator('#profile-territory-editor [name="territory[title]"]');
  await field.fill('Perubahan sementara');

  page.once('dialog', (dialog) => dialog.dismiss());
  await page.getByRole('link', { name: 'Kembali ke Profil' }).click();
  await expect(page).toHaveURL(/admin\/profil\/kelola\/territory/);
  await expect(field).toHaveValue('Perubahan sementara');

  page.once('dialog', (dialog) => dialog.accept());
  await page.getByRole('link', { name: 'Kembali ke Profil' }).click();
  await expect(page).toHaveURL(/admin\/profil$/);
  await expect(page.getByRole('heading', { name: 'Profil Jemaat', level: 1 })).toBeVisible();
});

test('kartu modul memakai permukaan tenang agar teks mudah dibaca', async ({ page }) => {
  const cards = page.locator('.profile-module-territory, .profile-module-census, .profile-module-worship');

  await expect(cards).toHaveCount(3);

  for (const card of await cards.all()) {
    const styles = await card.evaluate((element) => {
      const heading = element.querySelector('.profile-module-heading h2');
      return {
        backgroundImage: getComputedStyle(element).backgroundImage,
        headingShadow: getComputedStyle(heading).textShadow,
      };
    });

    expect(styles.backgroundImage).toBe('none');
    expect(styles.headingShadow).toBe('none');
  }
});

test('halaman Profil memakai warna Jemaat untuk menegaskan judul dan kelompok modul', async ({ page }) => {
  const profileColors = await page.evaluate(() => {
    const colorOf = (selector, property) => getComputedStyle(document.querySelector(selector))[property];
    const moduleColors = [
      '.profile-module-territory',
      '.profile-module-census',
      '.profile-module-worship',
    ].map((selector) => colorOf(selector, 'backgroundColor'));

    return {
      headingBackground: colorOf('.profile-page-heading', 'backgroundColor'),
      headingColor: colorOf('.profile-page-heading h1', 'color'),
      moduleColors,
      legacyBackground: colorOf('.profile-module-legacy', 'backgroundColor'),
    };
  });

  expect(profileColors.headingBackground).toBe('rgb(46, 26, 69)');
  expect(profileColors.headingColor).toBe('rgb(255, 255, 255)');
  expect(new Set(profileColors.moduleColors).size).toBe(3);
  expect(profileColors.moduleColors).not.toContain('rgb(255, 255, 255)');
  expect(profileColors.legacyBackground).not.toBe('rgb(255, 255, 255)');
});

test('peta pelayanan tetap terbaca penuh pada layar ponsel', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });

  const mapLayout = await page.locator('.profile-module-map').evaluate((card) => {
    const cardWidth = card.getBoundingClientRect().width;
    const imageBounds = card.querySelector('.profile-map-thumb').getBoundingClientRect();

    return {
      cardWidth,
      imageWidth: imageBounds.width,
      imageRatio: imageBounds.width / imageBounds.height,
    };
  });

  expect(mapLayout.imageWidth).toBeGreaterThan(mapLayout.cardWidth * 0.8);
  expect(mapLayout.imageRatio).toBeGreaterThan(1.4);
});

test('kartu Profil memberi umpan balik pada hover dan fokus serta menghormati reduced motion', async ({ page }) => {
  const territoryCard = page.locator('.profile-module-territory');

  await territoryCard.hover();
  await expect.poll(() => territoryCard.evaluate((card) => getComputedStyle(card).transform)).not.toBe('none');

  await territoryCard.getByRole('link', { name: 'Kelola wilayah' }).focus();
  await expect.poll(() => territoryCard.evaluate((card) => getComputedStyle(card).transform)).not.toBe('none');

  const mapImage = page.locator('.profile-map-thumb img');
  await page.locator('.profile-module-map').hover();
  await expect.poll(() => mapImage.evaluate((image) => getComputedStyle(image).transform)).not.toBe('none');

  await page.emulateMedia({ reducedMotion: 'reduce' });
  const reducedTransition = await territoryCard.evaluate((card) => getComputedStyle(card).transitionDuration);
  expect(parseFloat(reducedTransition)).toBeLessThan(0.001);
  await expect.poll(() => territoryCard.evaluate((card) => getComputedStyle(card).transform)).toBe('none');
  await expect.poll(() => mapImage.evaluate((image) => getComputedStyle(image).transform)).toBe('none');
});

test('kartu peribadahan membuka editor jenis pelayanan tersendiri', async ({ page }) => {
  const worshipCard = page.locator('.profile-module-worship');

  await expect(worshipCard).toContainText('jenis pelayanan');
  await expect(worshipCard.getByRole('link', { name: 'Buka Jadwal Ibadah' })).toHaveCount(0);
  await worshipCard.getByRole('link', { name: 'Kelola pelayanan' }).click();

  const editor = page.locator('#profile-worship-editor');
  await expect(editor).toBeVisible();
  await expect(editor.getByLabel('Nama pelayanan').first()).toBeVisible();
  await expect(editor.getByLabel('Tempat').first()).toBeVisible();
  await expect(editor.getByLabel('Keterangan').first()).toBeVisible();
  await expect(editor.locator('form')).toHaveAttribute('action', /admin\/profil\/save-page-content$/);
});

test('tindakan Profil dan Majelis tersusun setelah penjelasannya', async ({ page }) => {
  const managementCard = page.locator('.profile-module-legacy');

  await expect(managementCard).toHaveCSS('flex-direction', 'column');
  await expect(managementCard.locator('.profile-module-actions a')).toHaveCount(3);
});

test('form wilayah menavigasi langkah dengan tombol Sebelumnya dan Berikutnya', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');
  const panels = editor.locator('[data-profile-step-panel]');

  await expect(editor).toBeVisible();
  await expect(panels).toHaveCount(6);
  await expect(editor.locator('[data-profile-step-count]')).toHaveText('Bagian 1 dari 6');
  await expect(editor.locator('[data-profile-step-previous]')).toBeDisabled();

  await editor.locator('[data-profile-step-next]').click();
  await expect(editor.locator('[data-profile-step-count]')).toHaveText('Bagian 2 dari 6');
  await expect(panels.nth(1)).toBeVisible();
  await expect(panels.first()).toBeHidden();

  await editor.locator('[data-profile-step-previous]').click();
  await expect(editor.locator('[data-profile-step-count]')).toHaveText('Bagian 1 dari 6');
  await page.setViewportSize({ width: 390, height: 844 });
  for (let step = 0; step < 5; step++) {
    await editor.locator('[data-profile-step-next]').click();
  }
  await expect(editor.locator('[data-profile-step-count]')).toHaveText('Bagian 6 dari 6');
  const activeStepVisible = await editor.locator('[data-profile-step-nav]').evaluate((navigation) => {
    const activeStep = navigation.querySelector('[aria-current="step"]');
    const navigationBounds = navigation.getBoundingClientRect();
    const activeStepBounds = activeStep.getBoundingClientRect();
    return activeStepBounds.left >= navigationBounds.left && activeStepBounds.right <= navigationBounds.right;
  });
  expect(activeStepVisible).toBe(true);
});

test('pengaturan tampilan wilayah terpisah dan pratinjau ikut berubah', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');
  const boundaryIcon = editor.locator('[name="territory[boundaries][0][icon]"]');
  const firstBoundaryName = editor.locator('[name="territory[boundaries][0][name]"]');

  await expect(editor.locator('#profile-territory-step-0 [name="territory[boundary_prefix]"], #profile-territory-step-0 [name="territory[sector_prefix]"], #profile-territory-step-0 [name="territory[unit_suffix]"], #profile-territory-step-0 [name="territory[area_share_suffix]"]')).toHaveCount(0);
  await editor.locator('[data-profile-step-target]').nth(1).click();
  await expect(firstBoundaryName).toBeHidden();
  await editor.locator('[data-territory-item-list="boundaries"] [data-territory-item-toggle]').first().click();
  await expect(firstBoundaryName).toBeVisible();
  await expect(editor.locator('select[name$="[direction]"]')).toHaveCount(4);
  await expect(editor.getByLabel('Arah penunjuk')).toHaveCount(0);
  await editor.locator('[name="territory[boundaries][0][direction]"]').selectOption('Timur');
  await expect(boundaryIcon).toHaveValue('bi-arrow-right-circle');

  await editor.locator('[data-profile-step-target]').last().click();
  await expect(editor.locator('[data-territory-preview="boundary"]')).toHaveText('Sebelah Timur — Jemaat GPM Soya');
  await expect(editor.locator('[data-territory-preview="sector"]')).toHaveText('Sektor Koinonia · 3 unit · 34.15 Ha · 64% dari total wilayah');

  await editor.locator('[name="territory[boundary_prefix]"]').fill('Arah');
  await editor.locator('[name="territory[sector_prefix]"]').fill('Sektor');
  await editor.locator('[data-profile-step-target]').nth(1).click();
  await firstBoundaryName.fill('Wilayah contoh');
  await editor.locator('[data-profile-step-target]').last().click();
  await expect(editor.locator('[data-territory-preview="boundary"]')).toHaveText('Arah Timur — Wilayah contoh');
  await expect(editor.locator('[data-territory-preview="sector"]')).toHaveText('Sektor Koinonia · 3 unit · 34.15 Ha · 64% dari total wilayah');

  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan wilayah' }));
  await expect(page.locator('#profile-territory-editor [name="territory[boundaries][0][icon]"]')).toHaveValue('bi-arrow-right-circle');
  await expect(page.locator('#profile-territory-editor [name="territory[boundary_prefix]"]')).toHaveValue('Arah');
});

test('daftar wilayah diringkas dan tiap item dapat dibuka untuk diedit', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');

  await editor.locator('[data-profile-step-target]').nth(1).click();
  const boundary = editor.locator('[data-territory-item-list="boundaries"] [data-content-item]').first();
  await expect(boundary.locator('[data-territory-item-summary]')).toBeVisible();
  await expect(boundary.locator('[data-territory-item-editor]')).toBeHidden();
  await boundary.getByRole('button', { name: 'Ubah' }).click();
  await expect(boundary.locator('[data-territory-item-editor]')).toBeVisible();
  await expect(boundary.getByRole('button', { name: 'Selesai mengubah' })).toBeVisible();
  await boundary.locator('[name$="[direction]"]').selectOption('Timur');
  await boundary.locator('[name$="[name]"]').fill('Jemaat uji');
  await expect(boundary.locator('[data-territory-item-summary]')).toHaveText('Timur — Jemaat uji');

  await editor.locator('[data-profile-step-target]').nth(2).click();
  const sector = editor.locator('[data-territory-item-list="sectors"] [data-content-item]').first();
  await expect(sector.locator('[data-territory-item-summary]')).toBeVisible();
  await sector.getByRole('button', { name: 'Ubah' }).click();
  await sector.locator('[name$="[name]"]').fill('Sektor uji');
  await expect(sector.locator('[data-territory-item-summary]')).toHaveText('Sektor uji');

  await editor.locator('[data-profile-step-target]').nth(3).click();
  const destination = editor.locator('[data-territory-item-list="orbitations"] [data-content-item]').first();
  await expect(destination.locator('[data-territory-item-summary]')).toBeVisible();
  await destination.getByRole('button', { name: 'Ubah' }).click();
  await destination.locator('[name$="[label]"]').fill('Lokasi uji');
  await destination.locator('[name$="[value]"]').fill('250 M');
  await expect(destination.locator('[data-territory-item-summary]')).toHaveText('Lokasi uji — 250 M');

  await editor.locator('[data-profile-step-target]').nth(4).click();
  const season = editor.locator('[data-territory-item-list="seasons"] [data-content-item]').first();
  await expect(season.locator('[data-territory-item-summary]')).toBeVisible();
  await season.getByRole('button', { name: 'Ubah' }).click();
  await season.locator('[name$="[period]"]').fill('Periode uji');
  await season.locator('[name$="[title]"]').fill('Musim uji');
  await expect(season.locator('[data-territory-item-summary]')).toHaveText('Periode uji · Musim uji');

  for (const [stepIndex, listName] of [[1, 'boundaries'], [2, 'sectors'], [3, 'orbitations'], [4, 'seasons']]) {
    await editor.locator('[data-profile-step-target]').nth(stepIndex).click();
    const list = editor.locator(`[data-territory-item-list="${listName}"]`);
    const initialCount = await list.locator('[data-content-item]').count();
    await list.locator('[data-add-item]').click();
    const newItem = list.locator('[data-content-item]').last();
    await expect(list.locator('[data-content-item]')).toHaveCount(initialCount + 1);
    await expect(newItem.locator('[data-territory-item-summary]')).toBeVisible();
    await expect(newItem.locator('[data-territory-item-editor]')).toBeVisible();
    await newItem.getByRole('button', { name: 'Hapus item' }).click();
    await expect(list.locator('[data-content-item]')).toHaveCount(initialCount);
  }
});

test('tombol Ubah pada daftar batas wilayah sejajar', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');
  await editor.locator('[data-profile-step-target]').nth(1).click();

  const editButtons = editor.locator('[data-territory-item-list="boundaries"] [data-territory-item-toggle]');
  for (const width of [1280, 390, 320]) {
    await page.setViewportSize({ width, height: 844 });
    const leftEdges = await editButtons.evaluateAll((buttons) =>
      buttons.map((button) => button.getBoundingClientRect().left)
    );
    expect(Math.max(...leftEdges) - Math.min(...leftEdges)).toBeLessThanOrEqual(1);
  }
});

test('form wilayah menjelaskan bahwa informasi yang disimpan tampil di halaman Profil', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');
  const panels = editor.locator('[data-profile-step-panel]');

  await expect(editor.locator('#profile-territory-description')).toContainText('halaman Profil Jemaat');
  await expect(editor.locator('#profile-territory-description')).toContainText('sudah diperiksa');
  await expect(panels.first().locator('.profile-territory-accordion-body > p')).toContainText('dipastikan benar');
});

test('wilayah dapat menambah dan menghapus musim lalu menyimpan perubahan', async ({ page }) => {
  await openEditor(page, 'territory');
  const editor = page.locator('#profile-territory-editor');
  await expect(editor).toBeVisible();

  const seasons = editor.locator('fieldset[data-content-list]').filter({ hasText: 'Daftar musim' });
  const initialSeasonCount = await seasons.locator('[data-content-item]').count();

  await editor.locator('[data-profile-step-target]').nth(4).click();
  await editor.getByRole('button', { name: 'Tambah musim' }).click();
  const newSeasonItem = seasons.locator('[data-content-item]').last();
  await newSeasonItem.locator('[name$="[period]"]').fill('E2E test');
  await newSeasonItem.locator('[name$="[title]"]').fill('Musim Playwright');
  await newSeasonItem.locator('[name$="[description]"]').fill('Keterangan dummy untuk tes otomatis.');
  await expect(seasons.locator('[data-content-item]')).toHaveCount(initialSeasonCount + 1);

  const itemToRemove = seasons.locator('[data-content-item]').first();
  await itemToRemove.getByRole('button', { name: 'Hapus item' }).click();
  await expect(seasons.locator('[data-content-item]')).toHaveCount(initialSeasonCount);

  await editor.locator('[data-profile-step-target]').first().click();
  await editor.locator('[name="territory[title]"]').fill('Wilayah khusus Playwright');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan wilayah' }));

  await expect(page).toHaveURL(/admin\/profil\/kelola\/territory/);
  await page.reload();
  await expect(page.locator('#profile-territory-editor [name="territory[title]"]')).toHaveValue('Wilayah khusus Playwright');
  const refreshedLastSeason = page.locator('#profile-territory-editor fieldset[data-content-list]').filter({ hasText: 'Daftar musim' }).locator('[data-content-item]').last();
  await expect(refreshedLastSeason.locator('[name$="[title]"]')).toHaveValue('Musim Playwright');
});

test('setiap pelayanan tampil sebagai kartu berbingkai termasuk setelah ditambahkan', async ({ page }) => {
  await openEditor(page, 'worship');
  const editor = page.locator('#profile-worship-editor');
  const list = editor.locator('[data-content-list]').filter({ hasText: 'Daftar pelayanan' });
  const items = list.locator('[data-content-item]');
  const initialCount = await items.count();

  expect(initialCount).toBeGreaterThan(0);
  await expect(items.first()).toHaveCSS('border-top-width', '1px');
  await expect(items.first()).toHaveCSS('border-radius', '16px');
  await expect(items.first()).toHaveCSS('background-color', 'rgb(255, 255, 255)');
  await expect(list.locator('.profile-content-list-items')).toHaveCSS('row-gap', '12px');

  for (const width of [1280, 924, 390]) {
    await page.setViewportSize({ width, height: 844 });
    const layout = await editor.locator('.profile-form-grid').evaluate((grid) => ({
      gridWidth: grid.getBoundingClientRect().width,
      listWidth: grid.querySelector(':scope > [data-content-list]').getBoundingClientRect().width,
    }));
    expect(layout.listWidth).toBeGreaterThanOrEqual(layout.gridWidth - 2);
  }

  await editor.getByRole('button', { name: 'Tambah pelayanan' }).click();
  await expect(items).toHaveCount(initialCount + 1);
  await expect(items.last()).toHaveCSS('border-top-width', '1px');
  await expect(items.last()).toHaveCSS('border-radius', '16px');
});

test('data KK dan jiwa dapat dihitung, dinavigasi, dan disimpan', async ({ page }) => {
  await openEditor(page, 'census');
  const editor = page.locator('#profile-census-editor');
  await expect(editor).toBeVisible();
  await expect(editor.locator('#census-step-main [name="census[household_suffix]"], #census-step-main [name="census[population_suffix]"]')).toHaveCount(0);

  await editor.locator('[name="census[household_count]"]').fill('210');
  await editor.getByRole('button', { name: 'Kelompok usia' }).click();
  const ageList = editor.locator('[data-census-age-list]');
  await expect(ageList.locator('[data-age-label-input]').first()).toBeHidden();
  await ageList.locator('[data-age-label-toggle]').first().click();
  await expect(ageList.locator('[data-age-label-input]').first()).toBeVisible();
  await ageList.locator('[data-age-label-input]').first().fill('0-4');
  await expect(ageList.locator('[data-age-label-display]').first()).toHaveText('Usia 0-4');
  await ageList.getByRole('button', { name: 'Tambah rentang usia' }).click();
  const newAgeRange = ageList.locator('[data-content-item]').last();
  await newAgeRange.locator('[data-age-label-input]').fill('86+');
  await expect(newAgeRange.locator('[data-age-label-display]')).toHaveText('Usia 86+');
  await newAgeRange.locator('[name$="[male]"]').fill('2');
  await newAgeRange.locator('[name$="[female]"]').fill('3');
  await editor.locator('[name="census[age_rows][0][male]"]').fill('20');
  await expect(editor.locator('[data-profile-census-total]')).toHaveText('637');

  await expect(editor.locator('[data-census-step-count]')).toHaveText('Bagian 2 dari 3');
  await page.setViewportSize({ width: 390, height: 844 });
  await editor.getByRole('button', { name: 'Berikutnya' }).click();
  await expect(editor.locator('[data-census-step-count]')).toHaveText('Bagian 3 dari 3');
  const activeStepVisible = await editor.locator('[data-census-step-nav]').evaluate((navigation) => {
    const activeStep = navigation.querySelector('[aria-current="step"]');
    const navigationBounds = navigation.getBoundingClientRect();
    const activeStepBounds = activeStep.getBoundingClientRect();
    return activeStepBounds.left >= navigationBounds.left && activeStepBounds.right <= navigationBounds.right;
  });
  expect(activeStepVisible).toBe(true);
  await expect(editor.getByRole('button', { name: 'Tampilan publik' })).toBeVisible();
  await expect(editor.locator('[data-census-public-preview]')).toHaveText('210 KK dan 637 jiwa.');
  await editor.getByRole('button', { name: 'Sebelumnya' }).click();
  await expect(editor.locator('[data-census-step-count]')).toHaveText('Bagian 2 dari 3');

  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan data jemaat' }));
  await expect(page).toHaveURL(/admin\/profil\/kelola\/census/);
  await page.reload();

  await expect(page.locator('#profile-census-editor [name="census[household_count]"]')).toHaveValue('210');
  await expect(page.locator('#profile-census-editor [data-profile-census-total]')).toHaveText('637');
  await expect(page.locator('#profile-census-editor [name="census[age_rows][0][label]"]')).toHaveValue('0-4');
  await expect(page.locator('#profile-census-editor [name="census[age_rows][9][label]"]')).toHaveValue('86+');
});

test('peta dapat memperbarui keterangan dan menolak file bukan gambar', async ({ page }) => {
  await openEditor(page, 'map');
  const editor = page.locator('#profile-map-editor');
  await expect(editor).toBeVisible();

  const title = `Peta Playwright ${Date.now()}`;
  await editor.getByLabel('Judul bagian peta').fill(title);
  await editor.getByLabel('Alt text').fill('Peta dummy untuk pengujian otomatis');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan peta' }));
  await expect(page).toHaveURL(/admin\/profil\/kelola\/map/);
  await page.reload();

  const reloadedEditor = page.locator('#profile-map-editor');
  await expect(reloadedEditor.getByLabel('Judul bagian peta')).toHaveValue(title);
  await expect(reloadedEditor.locator('[data-profile-map-preview]')).toHaveAttribute('alt', 'Peta dummy untuk pengujian otomatis');
  await reloadedEditor.locator('[name="service_map"]').setInputFiles({
    name: 'bukan-gambar.txt',
    mimeType: 'text/plain',
    buffer: Buffer.from('file tes yang bukan gambar'),
  });
  await expect(reloadedEditor.locator('[data-profile-file-error]')).toContainText('Format peta tidak valid');
});

test('isi profil publik dapat diedit dan tersimpan', async ({ page }) => {
  await openEditor(page, 'content');
  const editor = page.locator('#profile-content-editor');
  await expect(editor).toBeVisible();

  const description = `Deskripsi Playwright ${Date.now()}`;
  await editor.locator('#profile-content-hero [name="content[hero][description]"]').fill(description);
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan konten Profil' }));

  await expect(page).toHaveURL(/admin\/profil\/kelola\/content/);
  await expect(page.locator('#profile-content-editor #profile-content-hero [name="content[hero][description]"]')).toHaveValue(description);
});

test('Konten Profil menampilkan judul langkah dan indikator langkah aktif', async ({ page }) => {
  await openEditor(page, 'content');
  const section = page.locator('#profile-content-editor #profile-content-hero');
  const heading = section.locator('.profile-step-panel-heading');
  const steps = page.locator('#profile-content-editor [data-profile-step-target]');

  const styles = await steps.first().evaluate((element) => {
    const style = getComputedStyle(element);
    return {
      backgroundColor: style.backgroundColor,
      borderColor: style.borderColor,
    };
  });

  await expect(heading).toHaveText('Judul dan pengantar');
  expect(styles.backgroundColor).not.toBe('rgba(0, 0, 0, 0)');
  expect(styles.borderColor).not.toBe('rgba(0, 0, 0, 0)');
  await steps.last().click();
  await expect(steps.last()).toHaveAttribute('aria-current', 'step');
  await expect(section).toBeHidden();
});

test('navigasi langkah Majelis jelas dan tetap terlihat pada layar ponsel', async ({ page }) => {
  await openEditor(page, 'leadership');
  const editor = page.locator('#profile-leadership-editor');
  const steps = editor.locator('[data-leadership-step-target]');
  await expect(steps).toHaveText(['1 Periode Majelis', '2 Data anggota', '3 Daftar anggota']);
  await expect(steps.first()).toHaveAttribute('aria-current', 'step');

  await page.setViewportSize({ width: 390, height: 844 });
  const navigationLayout = await editor.locator('[data-leadership-step-nav]').evaluate((navigation) => {
    const activeStep = navigation.querySelector('[aria-current="step"]');
    const navigationBounds = navigation.getBoundingClientRect();
    const activeStepBounds = activeStep.getBoundingClientRect();
    return {
      activeStepVisible: activeStepBounds.left >= navigationBounds.left && activeStepBounds.right <= navigationBounds.right,
      hasHorizontalOverflow: navigation.scrollWidth > navigation.clientWidth,
    };
  });
  expect(navigationLayout.activeStepVisible).toBe(true);
  expect(navigationLayout.hasHorizontalOverflow).toBe(false);

  await steps.last().click();
  await expect(steps.last()).toHaveAttribute('aria-current', 'step');
  await expect(editor.locator('[data-leadership-step-count]')).toHaveText('Bagian 3 dari 3');
  await expect(editor.locator('#anggota-panel')).toBeVisible();
});

test('pengelolaan Majelis menavigasi tiga langkah tanpa kehilangan isian anggota', async ({ page }) => {
  await openEditor(page, 'leadership');
  const editor = page.locator('#profile-leadership-editor');
  const steps = editor.locator('[data-leadership-step-target]');
  const panels = editor.locator('[data-leadership-step-panel]');

  await expect(editor.getByRole('navigation', { name: 'Bagian pengelolaan Majelis' })).toBeVisible();
  await expect(steps).toHaveCount(3);
  await expect(panels).toHaveCount(3);
  await expect(panels.nth(0)).toBeVisible();
  await expect(panels.nth(1)).toBeHidden();
  await expect(panels.nth(2)).toBeHidden();
  await expect(editor.locator('[data-leadership-step-count]')).toHaveText('Bagian 1 dari 3');

  await editor.locator('[name="period_name"]').fill(`Periode stepper ${Date.now()}`);
  await editor.locator('[name="start_date"]').fill('2026-01-01');
  await editor.locator('[name="is_active"]').selectOption('1');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan periode' }));

  await steps.nth(1).click();
  await expect(steps.nth(1)).toHaveAttribute('aria-current', 'step');
  await expect(panels.nth(1)).toBeVisible();
  await expect(panels.nth(0)).toBeHidden();
  await editor.locator('#member_name').fill('Nama sementara');

  await editor.locator('[data-leadership-step-next]').click();
  await expect(editor.locator('[data-leadership-step-count]')).toHaveText('Bagian 3 dari 3');
  await expect(panels.nth(2)).toBeVisible();
  await expect(panels.nth(1)).toBeHidden();

  await editor.locator('[data-leadership-step-previous]').click();
  await expect(editor.locator('#member_name')).toHaveValue('Nama sementara');
});

test('data profil jemaat dapat diubah dan disimpan', async ({ page }) => {
  await openEditor(page, 'data');
  const editor = page.locator('#profile-data-editor');
  await expect(editor).toBeVisible();

  const history = `Sejarah dummy Playwright ${Date.now()}`;
  await editor.getByLabel('Sejarah').fill(history);
  await editor.getByLabel('Visi dan misi').fill('Visi dan misi untuk pengujian lokal.');
  await editor.getByLabel('Alamat').fill('Alamat contoh khusus pengujian lokal.');
  await editor.getByLabel('Kontak resmi').fill('Kontak dummy Playwright');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan data jemaat' }));

  await expect(page.locator('#profile-data-editor [name="history"]')).toHaveValue(history);

  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto('/profil');
  const storySection = page.locator('#sejarah-visi-misi');
  await expect(storySection).toBeVisible();
  await expect(storySection.getByRole('heading', { name: 'Sejarah' })).toBeVisible();
  await expect(storySection.getByRole('heading', { name: 'Visi dan Misi' })).toBeVisible();

  const desktopColumns = await storySection.locator('.profile-story-grid').evaluate(
    (grid) => getComputedStyle(grid).gridTemplateColumns.split(' ').length
  );
  expect(desktopColumns).toBe(2);

  await page.setViewportSize({ width: 390, height: 844 });
  const mobileLayout = await storySection.evaluate((section) => ({
    columns: getComputedStyle(section.querySelector('.profile-story-grid')).gridTemplateColumns.split(' ').length,
    left: section.getBoundingClientRect().left,
    right: section.getBoundingClientRect().right,
    viewportWidth: document.documentElement.clientWidth,
    documentWidth: document.documentElement.scrollWidth,
  }));
  expect(mobileLayout.columns).toBe(1);
  expect(mobileLayout.left).toBeGreaterThanOrEqual(0);
  expect(mobileLayout.right).toBeLessThanOrEqual(mobileLayout.viewportWidth);
  expect(mobileLayout.documentWidth).toBeLessThanOrEqual(mobileLayout.viewportWidth);
});

test('periode dan kartu Majelis dapat ditambah, diaktifkan, diedit, dan dihapus', async ({ page }) => {
  let firstPeriod = `E2E Periode ${Date.now()}`;
  await openEditor(page, 'leadership');
  let editor = page.locator('#profile-leadership-editor');
  const periodRow = (name) => editor.getByRole('row').filter({
    has: page.getByRole('cell', { name, exact: true }),
  });
  await editor.locator('[name="period_name"]').fill(firstPeriod);
  await editor.locator('[name="start_date"]').fill('2020-01-01');
  await editor.locator('[name="is_active"]').selectOption('0');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan periode' }));

  const secondPeriod = `${firstPeriod} Aktif`;
  await openEditor(page, 'leadership');
  editor = page.locator('#profile-leadership-editor');
  await editor.locator('[name="period_name"]').fill(secondPeriod);
  await editor.locator('[name="start_date"]').fill('2021-01-01');
  await editor.locator('[name="is_active"]').selectOption('1');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan periode' }));

  await openEditor(page, 'leadership');
  editor = page.locator('#profile-leadership-editor');
  let firstPeriodRow = periodRow(firstPeriod);
  await firstPeriodRow.getByRole('button', { name: 'Edit' }).click();
  firstPeriod = `${firstPeriod} diperbarui`;
  await editor.locator('[name="period_name"]').fill(firstPeriod);
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan perubahan periode' }));

  await openEditor(page, 'leadership');
  editor = page.locator('#profile-leadership-editor');
  firstPeriodRow = periodRow(firstPeriod);
  await submitProfileFormAndWaitForReload(page, firstPeriodRow.getByRole('button', { name: 'Aktifkan' }));

  const memberName = `Anggota E2E ${Date.now()}`;
  await openEditor(page, 'leadership');
  editor = page.locator('#profile-leadership-editor');
  await editor.locator('[data-leadership-step-target="anggota-form-panel"]').click();
  await editor.locator('[name="name"]').fill(memberName);
  await editor.locator('[name="position"]').fill('Jabatan pengujian');
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan anggota' }));

  await openEditor(page, 'leadership');
  editor = page.locator('#profile-leadership-editor');
  await editor.locator('[data-leadership-step-target="anggota-panel"]').click();
  let memberRow = editor.getByRole('row').filter({ hasText: memberName });
  await expect(memberRow).toBeVisible();
  await memberRow.getByRole('button', { name: 'Edit' }).click();
  await expect(editor.locator('[name="name"]')).toHaveValue(memberName);
  await expect(editor.getByRole('button', { name: 'Simpan perubahan anggota' }).locator('svg')).toBeVisible();
  await expect(editor.locator('#member_name')).toBeFocused();
  await editor.locator('#member-form').getByRole('button', { name: 'Batal mengubah' }).click();
  await expect(editor.locator('[name="name"]')).toHaveValue('');
  await editor.locator('[data-leadership-step-target="anggota-panel"]').click();
  await memberRow.getByRole('button', { name: 'Edit' }).click();
  await editor.locator('[name="name"]').fill(`${memberName} diperbarui`);
  await submitProfileFormAndWaitForReload(page, editor.getByRole('button', { name: 'Simpan perubahan anggota' }));

  await openEditor(page, 'leadership');
  editor = page.locator('#profile-leadership-editor');
  await editor.locator('[data-leadership-step-target="anggota-panel"]').click();
  memberRow = editor.getByRole('row').filter({ hasText: `${memberName} diperbarui` });
  await expect(memberRow).toBeVisible();
  page.once('dialog', (dialog) => dialog.accept());
  await submitProfileFormAndWaitForReload(page, memberRow.getByRole('button', { name: 'Hapus' }));

  await openEditor(page, 'leadership');
  await page.locator('#profile-leadership-editor [data-leadership-step-target="anggota-panel"]').click();
  await expect(page.locator('#profile-leadership-editor')).toContainText('Belum ada anggota pada periode ini.');
});

test.describe('tampilan mobile Pengelolaan Majelis', () => {
  test.use({ viewport: { width: 491, height: 760 } });

  test('halaman dapat digulir dan digunakan pada layar ponsel', async ({ page }) => {
    await openEditor(page, 'leadership');

    let editor = page.locator('#profile-leadership-editor');
    await expect(editor).toBeVisible();
    await expect(page.locator('[data-rm]')).toHaveCount(0);

    const scrollState = await page.evaluate(() => {
      document.documentElement.style.scrollBehavior = 'auto';
      const element = document.scrollingElement;
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
    await editor.locator('[name="period_name"]').fill(periodName);
    await editor.locator('[name="start_date"]').fill('2026-01-01');
    await editor.locator('[name="is_active"]').selectOption('1');
    await submitProfileFormAndWaitForReload(page, editor.locator('#periode-panel [data-profile-submit]'));

    await openEditor(page, 'leadership');
    editor = page.locator('#profile-leadership-editor');
    await editor.locator('[data-leadership-step-target="anggota-form-panel"]').click();

    const memberName = `Anggota Mobile ${Date.now()}`;
    const memberForm = editor.locator('#member-form');
    await memberForm.locator('[name="name"]').fill(memberName);
    await memberForm.locator('[name="position"]').fill('Jabatan pengujian mobile');
    await submitProfileFormAndWaitForReload(page, memberForm.getByRole('button', { name: 'Simpan anggota' }));

    await openEditor(page, 'leadership');
    editor = page.locator('#profile-leadership-editor');
    await editor.locator('[data-leadership-step-target="anggota-panel"]').click();
    const memberRow = editor.getByRole('row').filter({
      has: page.getByRole('cell', { name: memberName, exact: true }),
    });
    await expect(memberRow).toBeVisible();
    await expect(memberRow).toContainText('Jabatan pengujian mobile');

    page.once('dialog', (dialog) => dialog.accept());
    await submitProfileFormAndWaitForReload(page, memberRow.getByRole('button', { name: 'Hapus' }));
  });
});

test('modul Jadwal Ibadah tetap mendukung alur CRUD jadwal', async ({ page }) => {
  await page.goto('/admin/jadwal');
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