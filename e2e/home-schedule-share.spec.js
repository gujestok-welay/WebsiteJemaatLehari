const { test, expect } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./test-credentials');

const scheduleNames = [];

test.afterEach(async ({ page }) => {
  if (scheduleNames.length === 0) return;

  await page.goto('/admin/jadwal');
  for (const name of scheduleNames) {
    const row = page.getByRole('row').filter({ hasText: name });
    const archiveButton = row.getByRole('button', { name: `Arsipkan jadwal: ${name}` });
    if (await archiveButton.count()) {
      page.once('dialog', (dialog) => dialog.accept());
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        archiveButton.click(),
      ]);
      await expect(row).toHaveCount(0);
    }
  }
  scheduleNames.length = 0;
});

async function createUpcomingSchedule(page) {
  const name = `Ibadah E2E Bagikan ${Date.now()}`;
  scheduleNames.push(name);

  await page.goto('/admin/jadwal');
  await page.getByRole('link', { name: 'Tambah jadwal', exact: true }).click();
  const modal = page.locator('#schedule-create-modal');
  await expect(modal).toBeVisible();
  await modal.getByLabel('Jenis / nama ibadah').fill(name);
  await modal.getByLabel('Tanggal').fill('2030-01-02');
  await modal.getByLabel('Waktu WIT').fill('09:30');
  await modal.getByLabel('Lokasi').fill('Kapel Pengujian');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    modal.getByRole('button', { name: 'Simpan Jadwal' }).click(),
  ]);

  return name;
}

async function loginAsAdmin(page) {
  await page.goto('/admin/login');
  await page.getByLabel('Alamat Email').fill(adminEmail);
  await page.locator('#password').fill(adminPassword);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await expect(page).toHaveURL(/admin\/dashboard/);
}

test('jadwal beranda dapat dibagikan dengan data jadwal dan tautan halaman jadwal', async ({ page }) => {
  await page.addInitScript(() => {
    Object.defineProperty(navigator, 'share', {
      configurable: true,
      value: async (data) => {
        window.__sharedSchedule = data;
      },
    });
  });
  await loginAsAdmin(page);
  const name = await createUpcomingSchedule(page);

  await page.goto('');
  const schedule = page.locator('.hero-panel-card');
  await expect(schedule).toContainText(name);
  const shareButton = schedule.getByRole('button', { name: `Bagikan jadwal: ${name}` });
  await expect(shareButton).toBeVisible();
  await shareButton.click();

  await expect(schedule.getByRole('status')).toHaveText('Selesai membagikan jadwal.');
  await expect.poll(() => page.evaluate(() => window.__sharedSchedule)).toEqual({
    title: 'Jadwal ibadah Jemaat Leahari',
    text: `${name}\nRabu, 2 Januari 2030\n09.30 WIT · Kapel Pengujian`,
    url: 'http://127.0.0.1:8091/index.php/jadwal-ibadah',
  });

  for (const width of [320, 390, 1440]) {
    await page.mouse.move(0, 0);
    await page.setViewportSize({ width, height: 900 });
    const layout = await page.evaluate(() => {
      const card = document.querySelector('.hero-panel-card').getBoundingClientRect();
      const button = document.querySelector('[data-schedule-share]').getBoundingClientRect();
      return {
        cardLeft: card.left,
        cardRight: card.right,
        buttonHeight: button.height,
        pageWidth: document.documentElement.scrollWidth,
        viewportWidth: document.documentElement.clientWidth,
      };
    });

    expect(layout.cardLeft, `kartu jadwal tidak terpotong di kiri pada ${width}px`).toBeGreaterThanOrEqual(0);
    expect(layout.cardRight, `kartu jadwal tidak terpotong di kanan pada ${width}px`).toBeLessThanOrEqual(width);
    expect(layout.buttonHeight, `tombol bagikan memenuhi target sentuh pada ${width}px`).toBeGreaterThanOrEqual(44);
    expect(layout.pageWidth, `beranda tidak overflow pada ${width}px`).toBeLessThanOrEqual(layout.viewportWidth);
  }
});

test('jadwal beranda disalin saat berbagi perangkat tidak didukung', async ({ page }) => {
  await page.addInitScript(() => {
    Object.defineProperty(navigator, 'share', {
      configurable: true,
      value: undefined,
    });
    Object.defineProperty(navigator, 'clipboard', {
      configurable: true,
      value: {
        writeText: async (text) => {
          window.__copiedSchedule = text;
        },
      },
    });
  });
  await loginAsAdmin(page);
  const name = await createUpcomingSchedule(page);

  await page.goto('');
  const schedule = page.locator('.hero-panel-card');
  await expect(schedule).toContainText(name);
  await schedule.getByRole('button', { name: `Bagikan jadwal: ${name}` }).click();

  await expect(schedule.getByRole('status')).toHaveText('Jadwal disalin. Silakan tempel untuk membagikannya.');
  await expect.poll(() => page.evaluate(() => window.__copiedSchedule)).toBe(
    `${name}\nRabu, 2 Januari 2030\n09.30 WIT · Kapel Pengujian\nhttp://127.0.0.1:8091/index.php/jadwal-ibadah`,
  );
});
