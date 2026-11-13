const { test, expect } = require('@playwright/test');

const publicPages = [
  { path: '', name: 'Beranda' },
  { path: 'profil', name: 'Profil Jemaat' },
  { path: 'berita', name: 'Berita' },
  { path: 'galeri', name: 'Galeri Kegiatan' },
  { path: 'jadwal-ibadah', name: 'Jadwal Ibadah' },
];

for (const { path, name } of publicPages) {
  test(`halaman ${name} dapat dibuka`, async ({ page }) => {
    const response = await page.goto(path);

    expect(response.ok()).toBe(true);
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await expect(page).toHaveTitle(/Jemaat Leahari/);
  });
}

test('navigasi publik menyediakan akses masuk pengelola berbentuk ikon tanpa teks', async ({ page }) => {
  const entry = page.getByRole('link', { name: 'Masuk ke panel pengelola' });

  for (const { path } of publicPages) {
    await page.goto(path);
    await expect(entry).toHaveAttribute('href', /\/admin$/);
    await expect(entry.locator('i[aria-hidden="true"]')).toHaveCount(1);
    await expect(entry).toHaveText('');
  }
});

test('hero berita memakai foto papan nama dan bayangan ungu pada teks utama', async ({ page }) => {
  await page.goto('berita');

  const heroStyles = await page.locator('.berita-hero').evaluate((hero) => {
    const heading = getComputedStyle(hero.querySelector('h1'));
    const description = getComputedStyle(hero.querySelector('p'));

    return {
      backgroundImage: getComputedStyle(hero).backgroundImage,
      backgroundSize: getComputedStyle(hero).backgroundSize,
      textOverlayOpacity: Number(
        getComputedStyle(hero).backgroundImage.match(/rgba\(72, 41, 80, ([\d.]+)\) 75%/)?.[1]
      ),
      headingShadow: heading.textShadow,
      descriptionShadow: description.textShadow,
    };
  });

  expect(heroStyles.backgroundImage).toContain('Papan-nama.png');
  expect(heroStyles.backgroundSize).toContain('cover');
  expect(heroStyles.textOverlayOpacity).toBeGreaterThanOrEqual(0.9);
  expect(heroStyles.headingShadow).toMatch(/rgba?\(72,\s*41,\s*80/);
  expect(heroStyles.descriptionShadow).toMatch(/rgba?\(72,\s*41,\s*80/);
});

test('hero berita memberi hierarki judul yang kuat tanpa overflow di desktop dan ponsel', async ({ page }) => {
  await page.setViewportSize({ width: 1366, height: 900 });
  await page.goto('berita');

  const desktop = await page.locator('.berita-hero').evaluate((hero) => ({
    height: hero.getBoundingClientRect().height,
    headingSize: parseFloat(getComputedStyle(hero.querySelector('h1')).fontSize),
  }));
  expect(desktop.height).toBeGreaterThanOrEqual(400);
  expect(desktop.headingSize).toBeGreaterThanOrEqual(80);

  await page.setViewportSize({ width: 390, height: 844 });
  const mobile = await page.locator('.berita-hero').evaluate((hero) => ({
    height: hero.getBoundingClientRect().height,
    headingSize: parseFloat(getComputedStyle(hero.querySelector('h1')).fontSize),
    viewportWidth: document.documentElement.clientWidth,
    documentWidth: document.documentElement.scrollWidth,
  }));
  expect(mobile.height).toBeGreaterThanOrEqual(320);
  expect(mobile.headingSize).toBeGreaterThanOrEqual(48);
  expect(mobile.documentWidth).toBeLessThanOrEqual(mobile.viewportWidth);
});

test('garis navigasi berpindah dengan transisi halus lalu kembali ke menu aktif', async ({ page }) => {
  await page.setViewportSize({ width: 1366, height: 900 });
  await page.goto('');

  const activeLink = page.locator('.navbar .nav-link.active');
  const hoveredLink = page.locator('.navbar .nav-link[href*="berita"]');
  const underlineTransform = (link) => link.evaluate((element) => getComputedStyle(element, '::after').transform);

  await expect.poll(() => underlineTransform(activeLink)).toBe('matrix(1, 0, 0, 1, 0, 0)');
  await hoveredLink.hover();
  const hoverTransition = await hoveredLink.evaluate((link) => {
    const style = getComputedStyle(link, '::after');
    return {
      duration: parseFloat(style.transitionDuration) * 1000,
      easing: style.transitionTimingFunction,
    };
  });
  expect(hoverTransition.duration).toBeGreaterThanOrEqual(200);
  expect(hoverTransition.easing).toBe('cubic-bezier(0.16, 1, 0.3, 1)');
  await expect.poll(() => underlineTransform(activeLink)).toBe('matrix(0, 0, 0, 1, 0, 0)');
  await expect.poll(() => underlineTransform(hoveredLink)).toBe('matrix(1, 0, 0, 1, 0, 0)');

  await page.mouse.move(0, 0);
  const exitDuration = await hoveredLink.evaluate((link) =>
    parseFloat(getComputedStyle(link, '::after').transitionDuration) * 1000
  );
  expect(exitDuration).toBeLessThan(hoverTransition.duration);
  await expect.poll(() => underlineTransform(activeLink)).toBe('matrix(1, 0, 0, 1, 0, 0)');
});

test('pratinjau galeri beranda membuka koleksi kegiatan', async ({ page }) => {
  await page.goto('');

  await expect(page.getByRole('heading', { name: 'Galeri kegiatan' })).toBeVisible();
  await expect(page.getByText('Mengenal jemaat')).toHaveCount(0);
  const galleryLink = page.getByRole('link', { name: 'Jelajahi galeri' });
  await expect(galleryLink).toHaveAttribute('href', /galeri$/);
  for (const width of [320, 390, 768, 1024, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    const section = await page.locator('.home-gallery').boundingBox();
    const link = await galleryLink.boundingBox();
    const documentWidth = await page.evaluate(() => ({
      clientWidth: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
    }));

    expect(section?.x, `pratinjau galeri tidak terpotong di kiri pada ${width}px`).toBeGreaterThanOrEqual(0);
    expect(section?.x + section?.width, `pratinjau galeri tidak terpotong di kanan pada ${width}px`).toBeLessThanOrEqual(width);
    expect(link?.height, `tautan galeri punya area sentuh memadai pada ${width}px`).toBeGreaterThanOrEqual(44);
    expect(documentWidth.scrollWidth, `beranda tidak overflow pada ${width}px`).toBeLessThanOrEqual(documentWidth.clientWidth);
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.getByRole('link', { name: 'Jelajahi galeri' }).click();
  await expect(page).toHaveURL(/galeri$/);
  await expect(page.getByRole('heading', { level: 1, name: 'Galeri kegiatan' })).toBeVisible();
});

test('galeri kosong tetap mudah dibaca pada ponsel dan layar lebar', async ({ page }) => {
  await page.goto('galeri');

  await expect(page.getByRole('heading', { name: 'Belum ada dokumentasi kegiatan' })).toBeVisible();
  for (const width of [320, 390, 768, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    const pageWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => document.documentElement.clientWidth);
    expect(pageWidth, `galeri tidak overflow pada ${width}px`).toBeLessThanOrEqual(viewportWidth);
  }
});

test('header galeri memakai foto Majelis sebagai latar', async ({ page }) => {
  await page.goto('galeri');

  const heading = page.locator('.gallery-page-heading');
  const backgroundImage = await heading.evaluate((element) => getComputedStyle(element).backgroundImage);
  expect(backgroundImage).toContain('90deg');
  expect(backgroundImage).toContain('rgba(72, 41, 80, 0.94)');
  expect(backgroundImage).toContain('rgba(72, 41, 80, 0.62)');
  expect(backgroundImage).toContain('Gambar-MajelisJemaat.jpeg');
  for (const text of [heading.locator('h1'), heading.locator('p')]) {
    const textShadow = await text.evaluate((element) => getComputedStyle(element).textShadow);
    expect(textShadow).toBe('none');
  }

  await page.locator('html').evaluate((html) => {
    html.dataset.theme = 'dark';
  });
  await expect(heading).toHaveCSS('background-image', /Gambar-MajelisJemaat\.jpeg/);
});

test('kartu galeri memakai proporsi seragam dan membungkus poster di semua layar', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto('galeri');
  await page.locator('.gallery-collection-inner').evaluate((inner) => {
    const grid = document.createElement('div');
    grid.className = 'gallery-grid';

    for (const [label, width, height] of [
      ['Portrait', 600, 1200],
      ['Square', 600, 600],
      ['Landscape', 1200, 600],
    ]) {
      const card = document.createElement('article');
      card.className = 'gallery-card';
      card.innerHTML = `
        <a class="gallery-card-link" href="#" aria-label="Lihat dokumentasi: ${label}">
          <span class="gallery-card-image-wrap">
            <img class="gallery-card-image-backdrop" alt="" aria-hidden="true">
            <img class="gallery-card-image" alt="${label}">
            <span class="gallery-card-image-fallback" hidden>Poster kegiatan tidak dapat dimuat</span>
          </span>
          <span class="gallery-card-copy"><strong>${label}</strong></span>
        </a>`;
      const image = card.querySelector('.gallery-card-image');
      const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}"><rect width="100%" height="100%" fill="purple"/></svg>`;
      const source = `data:image/svg+xml,${encodeURIComponent(svg)}`;
      card.querySelector('.gallery-card-image-backdrop').src = source;
      image.src = source;
      grid.append(card);
    }

    inner.replaceChildren(grid);
  });

  const cards = page.locator('.gallery-card');
  await expect.poll(() => cards.locator('.gallery-card-image').evaluateAll((images) =>
    images.map((image) => image.naturalWidth)
  )).toEqual([600, 600, 1200]);

  const measureCards = () => cards.evaluateAll((items) => items.map((card) => {
    const frame = card.querySelector('.gallery-card-image-wrap').getBoundingClientRect();
    const image = card.querySelector('.gallery-card-image').getBoundingClientRect();
    const backdrop = card.querySelector('.gallery-card-image-backdrop');
    const backdropBounds = backdrop.getBoundingClientRect();
    const backdropStyle = getComputedStyle(backdrop);
    return {
      cardWidth: card.getBoundingClientRect().width,
      frameWidth: frame.width,
      frameHeight: frame.height,
      imageWidth: image.width,
      imageHeight: image.height,
      imageRatio: card.querySelector('.gallery-card-image').naturalWidth
        / card.querySelector('.gallery-card-image').naturalHeight,
      imageFit: getComputedStyle(card.querySelector('.gallery-card-image')).objectFit,
      imageBackgroundColor: getComputedStyle(card.querySelector('.gallery-card-image')).backgroundColor,
      backdropWidth: backdropBounds.width,
      backdropHeight: backdropBounds.height,
      backdropCentered: Math.abs(
        (backdropBounds.left + backdropBounds.right) / 2 - (frame.left + frame.right) / 2
      ) <= 1 && Math.abs(
        (backdropBounds.top + backdropBounds.bottom) / 2 - (frame.top + frame.bottom) / 2
      ) <= 1,
      backdropFit: backdropStyle.objectFit,
      backdropFilter: backdropStyle.filter,
      backdropHiddenFromAssistiveTechnology: backdrop.getAttribute('aria-hidden') === 'true'
        && backdrop.alt === '',
    };
  }));
  const expectedRatios = [0.5, 1, 2];

  for (const [width, expectedColumns] of [[1440, 3], [700, 2], [390, 1]]) {
    await page.setViewportSize({ width, height: 1000 });
    const columns = await page.locator('.gallery-grid').evaluate((grid) =>
      getComputedStyle(grid).gridTemplateColumns.split(' ').length
    );
    expect(columns, `galeri memakai ${expectedColumns} kolom pada ${width}px`).toBe(expectedColumns);

    const measurements = await measureCards();
    expect(Math.max(...measurements.map((item) => item.cardWidth))
      - Math.min(...measurements.map((item) => item.cardWidth))).toBeLessThanOrEqual(1);
    for (const [index, item] of measurements.entries()) {
      expect(item.frameWidth / item.frameHeight).toBeCloseTo(0.8, 2);
      expect(item.imageWidth).toBeCloseTo(item.frameWidth, 1);
      expect(item.imageHeight).toBeCloseTo(item.frameHeight, 1);
      expect(item.imageRatio).toBeCloseTo(expectedRatios[index], 2);
      expect(item.imageFit).toBe('contain');
      expect(item.imageBackgroundColor).toBe('rgba(0, 0, 0, 0)');
      expect(item.backdropWidth).toBeGreaterThanOrEqual(item.frameWidth);
      expect(item.backdropHeight).toBeGreaterThanOrEqual(item.frameHeight);
      expect(item.backdropCentered).toBe(true);
      expect(item.backdropFit).toBe('cover');
      expect(item.backdropFilter).toMatch(/blur\(/);
      expect(item.backdropHiddenFromAssistiveTechnology).toBe(true);
    }

    expect(await page.evaluate(() => document.documentElement.scrollWidth))
      .toBeLessThanOrEqual(await page.evaluate(() => document.documentElement.clientWidth));
  }

  const failedImage = cards.first().locator('.gallery-card-image');
  await cards.first().locator('.gallery-card-image-backdrop').evaluate((image) => {
    image.src = '/missing-gallery-backdrop.png';
  });
  await expect(cards.first().locator('.gallery-card-image-backdrop')).toHaveCount(0);
  await failedImage.evaluate((image) => {
    image.src = '/missing-gallery-poster.png';
  });
  await expect(cards.first().locator('.gallery-card-image-fallback')).toBeVisible();
  await expect(failedImage).toHaveCount(0);

  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.locator('.gallery-grid').evaluate((grid) => {
    grid.querySelectorAll('.gallery-card').forEach((card, index) => {
      if (index > 0) card.remove();
    });
  });
  const singleCardAlignment = await page.locator('.gallery-grid').evaluate((grid) => {
    const gridBounds = grid.getBoundingClientRect();
    const cardBounds = grid.querySelector('.gallery-card').getBoundingClientRect();
    return Math.abs(
      (cardBounds.left + cardBounds.right) / 2 - (gridBounds.left + gridBounds.right) / 2
    );
  });
  expect(singleCardAlignment, 'satu kartu berada di tengah area galeri').toBeLessThanOrEqual(1);
});

test('menu Profil menavigasikan pengunjung ke bagian-bagian halaman profil', async ({ page }) => {
  await page.goto('berita');

  const profileMenu = page.locator('#profil-menu');
  const profileToggle = page.getByRole('button', { name: 'Profil' });
  await expect(profileToggle).toBeVisible();
  await profileToggle.click();

  const destinations = [
    ['Peta Pelayanan', '#peta-pelayanan'],
    ['Majelis Jemaat', '#majelis-jemaat'],
    ['Gambaran Umum Wilayah Pelayanan', '#wilayah'],
    ['Jumlah Kepala Keluarga dan Jiwa', '#jemaat'],
    ['Pelayanan Peribadahan', '#ibadah'],
  ];

  await expect(profileMenu.getByRole('link')).toHaveCount(destinations.length);
  for (const [label, href] of destinations) {
    await expect(profileMenu.getByRole('link', { name: label, exact: true })).toHaveAttribute('href', expect.stringContaining(href));
  }

  await profileMenu.getByRole('link', { name: 'Majelis Jemaat', exact: true }).click();
  await expect(page).toHaveURL(/profil#majelis-jemaat$/);
  await expect(page.locator('#majelis-jemaat')).toBeVisible();
});

test('dropdown Profil terbuka saat pointer diarahkan dan tertutup saat pointer keluar', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto('berita');

  const profileMenu = page.locator('#profil-menu');
  await expect(profileMenu).toBeHidden();

  await page.locator('#profil-menu-toggle').hover();
  await expect(profileMenu).toBeVisible();
  await expect(page.locator('#profil-menu-toggle')).toHaveAttribute('aria-expanded', 'true');

  const firstProfileLink = profileMenu.getByRole('link').first();
  await firstProfileLink.hover();
  await expect(firstProfileLink).toBeVisible();

  await page.mouse.move(0, 0);
  await expect(profileMenu).toBeHidden();
  await expect(page.locator('#profil-menu-toggle')).toHaveAttribute('aria-expanded', 'false');
});

test('tombol Profil tidak menampilkan caret dropdown Bootstrap selain chevron SVG', async ({ page }) => {
  await page.goto('berita');

  const caretDisplay = await page.locator('#profil-menu-toggle').evaluate((toggle) =>
    getComputedStyle(toggle, '::after').display
  );

  expect(caretDisplay).toBe('none');
});

test('menu Profil dapat dinavigasikan dengan keyboard', async ({ page }) => {
  await page.goto('berita');

  const profileLink = page.locator('#profil-menu-toggle');
  await profileLink.focus();
  await profileLink.press('Enter');
  await expect(page).toHaveURL(/\/profil$/);
});

test('tautan bagian Profil tetap tersedia tanpa JavaScript', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();

  await page.goto('berita');

  const mapLink = page.locator('#profil-menu a[href*="#peta-pelayanan"]');
  await expect(mapLink).toBeVisible();
  await mapLink.click();
  await expect(page).toHaveURL(/profil#peta-pelayanan$/);

  await context.close();
});

test('tautan Profil dari navigasi seluler membuka halaman profil', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('berita');
  await page.getByRole('button', { name: 'Buka navigasi utama' }).click();

  const profileLink = page.locator('#profil-menu-toggle');
  await expect(profileLink).toBeVisible();
  await profileLink.click();

  await expect(page).toHaveURL(/\/profil$/);
});

test('kartu ibadah beranda tetap terlihat pada lebar layar yang didukung', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('');

  for (const width of [320, 360, 390, 430, 768, 1440]) {
    await page.setViewportSize({ width, height: 900 });

    const card = await page.locator('.hero-panel-card').boundingBox();

    expect(card, `kartu ibadah tersedia pada lebar ${width}px`).not.toBeNull();
    expect(card.x, `kartu ibadah tidak terpotong di kiri pada ${width}px`).toBeGreaterThanOrEqual(0);
    expect(card.x + card.width, `kartu ibadah tidak terpotong di kanan pada ${width}px`).toBeLessThanOrEqual(width);
    expect(card.y + card.height, `kartu ibadah terlihat dalam layar awal pada ${width}px`).toBeLessThanOrEqual(900);
  }
});

test('tautan berita beranda memiliki area sentuh memadai di ponsel', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('');

  for (const width of [320, 360, 390, 430]) {
    await page.setViewportSize({ width, height: 900 });

    const link = await page.locator('.home-actions .home-inline-link').boundingBox();

    expect(link, `tautan berita tersedia pada lebar ${width}px`).not.toBeNull();
    expect(link.height, `area sentuh tautan berita minimal 44px pada ${width}px`).toBeGreaterThanOrEqual(44);
  }
});

test('jadwal ibadah beranda terlihat pada layar awal ponsel', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('');

  const card = await page.locator('.hero-panel-card').boundingBox();

  expect(card).not.toBeNull();
  expect(card.y + card.height).toBeLessThanOrEqual(844);
});
