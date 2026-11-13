const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

function contrastRatio(foreground, background) {
  const luminance = (color) => {
    const channels = color.match(/\d+/g).slice(0, 3).map((value) => Number(value) / 255);
    const linear = channels.map((value) => value <= 0.04045
      ? value / 12.92
      : ((value + 0.055) / 1.055) ** 2.4);
    return linear[0] * 0.2126 + linear[1] * 0.7152 + linear[2] * 0.0722;
  };
  const values = [luminance(foreground), luminance(background)].sort((a, b) => b - a);
  return (values[0] + 0.05) / (values[1] + 0.05);
}

test('isi setiap kartu ringkasan dashboard rata tengah pada desktop dan ponsel', async ({ page }) => {
  await page.goto('admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  const metrics = page.locator('.metrics[aria-label="Ringkasan konten"] .metric');
  for (const width of [390, 1280]) {
    await page.setViewportSize({ width, height: 900 });
    await expect(metrics).toHaveCount(3);
    const alignment = await metrics.evaluateAll((items) =>
      items.map((metric) => {
        const card = metric.getBoundingClientRect();
        const center = (element) => {
          const range = document.createRange();
          range.selectNodeContents(element);
          const text = range.getBoundingClientRect();
          return (text.left + text.right) / 2;
        };
        const cardCenter = (card.left + card.right) / 2;

        return {
          label: Math.abs(center(metric.querySelector('.metric-label')) - cardCenter),
          value: Math.abs(center(metric.querySelector('.metric-value')) - cardCenter),
        };
      })
    );

    for (const [index, item] of alignment.entries()) {
      expect(item.label, `label kartu ${index + 1} rata tengah pada ${width}px`).toBeLessThanOrEqual(1);
      expect(item.value, `angka kartu ${index + 1} rata tengah pada ${width}px`).toBeLessThanOrEqual(1);
    }
  }
});

test('toggle tema di halaman login berukuran ringkas di pojok kanan atas', async ({ page }) => {
  await page.goto('admin/login');

  const toggle = page.locator('.login-theme-control [data-theme-toggle]');
  for (const viewport of [{ width: 1280, height: 900 }, { width: 390, height: 844 }]) {
    await page.setViewportSize(viewport);
    const bounds = await toggle.boundingBox();
    const track = await toggle.locator('.theme-switch-track').boundingBox();

    expect(bounds.x + bounds.width).toBeGreaterThan(viewport.width - 20);
    expect(bounds.y).toBeLessThan(20);
    expect(bounds.width).toBeLessThanOrEqual(52);
    expect(bounds.height).toBeGreaterThanOrEqual(44);
    expect(track.width).toBeLessThanOrEqual(40);
    expect(track.height).toBeLessThanOrEqual(24);
  }

  await toggle.click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
});

test('toggle tema publik tersimpan dan berlaku di halaman admin', async ({ page }) => {
  await page.goto('');

  const publicTheme = page.getByRole('switch', { name: 'Aktifkan tema gelap' });
  await expect(publicTheme).toHaveAttribute('aria-checked', 'false');
  await expect(page.locator('select.theme-select')).toHaveCount(0);
  await publicTheme.click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await expect(page.getByRole('switch', { name: 'Aktifkan tema terang' })).toHaveAttribute('aria-checked', 'true');
  await expect.poll(() => page.locator('body').evaluate((body) => getComputedStyle(body).backgroundColor))
    .not.toBe('rgb(255, 254, 250)');
  const publicColors = await page.evaluate(() => ({
    background: getComputedStyle(document.querySelector('.home-shell')).backgroundColor,
    heading: getComputedStyle(document.querySelector('.home-title')).color,
    body: getComputedStyle(document.querySelector('.home-intro')).color,
  }));
  expect(publicColors.heading).toBe('rgb(228, 201, 239)');
  expect(contrastRatio(publicColors.heading, publicColors.background)).toBeGreaterThanOrEqual(4.5);
  expect(contrastRatio(publicColors.body, publicColors.background)).toBeGreaterThanOrEqual(4.5);

  await page.getByRole('switch', { name: 'Aktifkan tema terang' }).click();
  await expect(page.locator('body')).toHaveCSS('background-color', 'rgb(255, 254, 250)');
  await expect(page.locator('.home-title')).toHaveCSS('color', 'rgb(72, 41, 80)');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  await page.goto('admin/login');
  await expect(page.getByRole('switch', { name: 'Aktifkan tema terang' })).toHaveAttribute('aria-checked', 'true');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await expect(page.locator('.login-content')).toHaveCSS('background-color', 'rgb(33, 29, 37)');
  await expect(page.locator('.login-public-link')).toHaveCSS('color', 'rgb(214, 181, 229)');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);
  await expect(page.getByRole('switch', { name: 'Aktifkan tema terang' })).toHaveAttribute('aria-checked', 'true');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
});

test('pilihan tema tetap mudah dijangkau di navigasi ponsel', async ({ page }) => {
  await page.setViewportSize({ width: 320, height: 740 });
  await page.goto('');
  await page.getByRole('button', { name: 'Buka navigasi utama' }).click();

  const theme = page.getByRole('switch', { name: 'Aktifkan tema gelap' });
  await expect(theme).toBeVisible();
  await theme.click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth))
    .toBeLessThanOrEqual(320);

  const updatedTheme = page.locator('[data-theme-toggle]');
  await updatedTheme.focus();
  await updatedTheme.press('Space');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
});

test('halaman publik memakai warna teks dan permukaan gelap di seluruh rute utama', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  for (const route of ['', 'profil', 'berita', 'galeri', 'jadwal-ibadah']) {
    await page.goto(route);
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

    const palette = await page.evaluate(() => ({
      paper: getComputedStyle(document.documentElement).getPropertyValue('--paper').trim(),
      ink: getComputedStyle(document.documentElement).getPropertyValue('--ink').trim(),
    }));
    expect(palette.paper, `latar tema gelap di rute ${route || 'beranda'}`).toBe('#211d25');
    expect(palette.ink, `teks tema gelap di rute ${route || 'beranda'}`).toBe('#f4edf5');
  }
});

test('tema Sesuai perangkat mengikuti perubahan preferensi sistem', async ({ page }) => {
  await page.emulateMedia({ colorScheme: 'dark' });
  await page.goto('');

  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await expect(page.getByRole('switch', { name: 'Aktifkan tema terang' })).toHaveAttribute('aria-checked', 'true');

  await page.emulateMedia({ colorScheme: 'light' });
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
  await expect(page.getByRole('switch', { name: 'Aktifkan tema gelap' })).toHaveAttribute('aria-checked', 'false');
});

test('toggle tema admin berada di bagian terbawah menu, bukan navbar', async ({ page }) => {
  await page.setViewportSize({ width: 1366, height: 900 });
  await page.goto('admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  const sidebar = page.locator('#adminSidebar');
  await sidebar.hover();
  await expect(page.locator('body')).toHaveClass(/sidebar-expanded/);
  await expect(page.locator('#adminSidebar > :last-child')).toHaveClass(/sidebar-footer/);
  await expect(page.locator('.admin-topbar [data-theme-toggle]')).toHaveCount(0);

  const themeToggle = sidebar.locator('.sidebar-footer [data-theme-toggle]');
  await expect(themeToggle).toBeVisible();
  await themeToggle.click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
});

test('panel admin mempertahankan tema gelap pada halaman modul', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();
  await page.goto('admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);

  await page.goto('admin/profil');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await expect(page.getByRole('switch', { name: 'Aktifkan tema terang' })).toHaveAttribute('aria-checked', 'true');
  await expect(page.locator('body')).toHaveCSS('background-color', 'rgb(33, 29, 37)');

  await page.goto('admin/berita/create');
  const titleField = page.locator('.admin-content input:not([type="hidden"])').first();
  await expect(titleField).toHaveCSS('background-color', 'rgb(48, 41, 54)');
  await expect(titleField).toHaveCSS('color', 'rgb(244, 237, 245)');
});

test('label CTA emas memenuhi kontras WCAG AA pada tema gelap', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  const colors = await page.locator('.btn-gold').evaluate((button) => ({
    foreground: getComputedStyle(button).color,
    background: getComputedStyle(button).backgroundColor,
  }));
  expect(contrastRatio(colors.foreground, colors.background)).toBeGreaterThanOrEqual(4.5);
});

test('teks footer memenuhi kontras WCAG AA pada tema gelap', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  const colors = await page.locator('.site-footer').evaluate((footer) => {
    const background = getComputedStyle(footer).backgroundColor;
    return Array.from(footer.querySelectorAll(
      '.footer-tagline, .footer-heading, .footer-links a, .footer-contact-note, .footer-social-link, .footer-bottom, .footer-bottom a',
    )).map((element) => ({
      foreground: getComputedStyle(element).color,
      background,
    }));
  });

  expect(colors.length).toBeGreaterThan(0);
  for (const pair of colors) {
    expect(contrastRatio(pair.foreground, pair.background)).toBeGreaterThanOrEqual(4.5);
  }
});

test('hero berita menjaga kontras AA tanpa menutupi foto pada tema gelap', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();
  await page.goto('berita');

  const colors = await page.locator('.berita-page .berita-hero').evaluate((hero) => {
    const background = getComputedStyle(hero).backgroundColor;
    const backgroundImage = getComputedStyle(hero).backgroundImage;
    const overlayStops = Array.from(backgroundImage.matchAll(
      /rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)/g,
    ), (match) => ({
      channels: match.slice(1, 4).map(Number),
      opacity: Number(match[4]),
    }));
    return {
      text: Array.from(hero.querySelectorAll('h1, p')).map((element) => ({
        foreground: getComputedStyle(element).color,
        background,
      })),
      overlayStops,
    };
  });

  expect(colors.text.length).toBeGreaterThan(0);
  for (const pair of colors.text) {
    expect(contrastRatio(pair.foreground, pair.background)).toBeGreaterThanOrEqual(4.5);
  }

  const opacities = colors.overlayStops.map(({ channels, opacity }) => {
    const lightestBackdrop = channels.map((channel) => Math.round(channel * opacity + 255 * (1 - opacity)));
    expect(contrastRatio('rgb(244, 237, 245)', `rgb(${lightestBackdrop.join(', ')})`))
      .toBeGreaterThanOrEqual(4.5);
    return opacity;
  });
  expect(opacities.length).toBeGreaterThan(0);
  expect(Math.min(...opacities)).toBeGreaterThanOrEqual(0.7);
  expect(Math.max(...opacities)).toBeLessThanOrEqual(0.8);
});

test('badge profil dan jadwal memenuhi kontras WCAG AA pada tema gelap', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  for (const [route, selector] of [
    ['profil', '.profil-page .badge-soft'],
    ['jadwal-ibadah', '.jadwal-page .badge-soft, .jadwal-page .badge-hari'],
  ]) {
    await page.goto(route);
    const colors = await page.locator(selector).evaluateAll((badges) => badges.map((badge) => ({
      foreground: getComputedStyle(badge).color,
      background: getComputedStyle(badge).backgroundColor,
    })));

    expect(colors.length, `badge pada halaman ${route}`).toBeGreaterThan(0);
    for (const pair of colors) {
      expect(contrastRatio(pair.foreground, pair.background)).toBeGreaterThanOrEqual(4.5);
    }
  }
});

test('wordmark navbar memakai treatment yang terbaca pada tema gelap', async ({ page }) => {
  await page.goto('');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  await expect(page.locator('.navbar-brand-wordmark')).toHaveCSS('filter', 'brightness(0) invert(1)');
});

test('kartu galeri memakai permukaan terangkat yang terlihat pada tema gelap', async ({ page }) => {
  await page.goto('galeri');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();

  await page.locator('body').evaluate((body) => {
    for (const className of ['gallery-card', 'home-gallery-card']) {
      const card = document.createElement('article');
      card.className = className;
      card.dataset.testDarkGalleryCard = className;
      card.innerHTML = '<a class="gallery-card-link" href="#"><span class="gallery-card-copy"><strong>Pratinjau galeri</strong></span></a>';
      body.append(card);
    }
  });

  for (const className of ['gallery-card', 'home-gallery-card']) {
    const card = page.locator(`[data-test-dark-gallery-card="${className}"]`);
    await expect(card).toBeVisible();
    await expect(card).toHaveCSS('background-color', 'rgb(48, 41, 54)');
  }
});

test('tanggal detail galeri memenuhi kontras WCAG AA pada tema gelap', async ({ page }) => {
  await page.goto('galeri');
  await page.getByRole('switch', { name: 'Aktifkan tema gelap' }).click();
  await page.locator('body').evaluate((body) => {
    const heading = document.createElement('header');
    heading.className = 'gallery-detail-heading';
    heading.innerHTML = '<div class="gallery-detail-heading-inner"><time datetime="2026-01-01">Uji tanggal</time></div>';
    body.append(heading);
  });

  const colors = await page.locator('.gallery-detail-heading time').evaluate((time) => ({
    foreground: getComputedStyle(time).color,
    background: getComputedStyle(time.parentElement.parentElement).backgroundColor,
  }));
  expect(contrastRatio(colors.foreground, colors.background)).toBeGreaterThanOrEqual(4.5);
});
