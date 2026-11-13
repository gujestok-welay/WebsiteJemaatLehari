const { test, expect } = require('@playwright/test');

const publicPages = [
  { path: '', name: 'Beranda' },
  { path: 'profil', name: 'Profil Jemaat' },
  { path: 'berita', name: 'Berita' },
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
