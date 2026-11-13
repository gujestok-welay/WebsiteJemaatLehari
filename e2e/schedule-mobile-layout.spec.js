const { test, expect } = require('@playwright/test');

test('Jadwal menyesuaikan layar ponsel tanpa halaman melebar', async ({ page }) => {
  await page.goto('/assets/css/admin.css');
  await page.setContent(`
    <link rel="stylesheet" href="/assets/css/admin.css">
    <main class="admin-content">
      <section class="schedule-page">
        <div class="schedule-panel">
          <div class="schedule-toolbar">
            <nav class="schedule-status-tabs">
              <a class="is-active" href="#">Semua <span>3</span></a>
              <a href="#">Aktif <span>2</span></a>
              <a href="#">Dibatalkan <span>1</span></a>
              <a href="#">Arsip <span>1</span></a>
            </nav>
            <form class="schedule-search">
              <input type="search" aria-label="Cari jadwal" placeholder="Cari jadwal...">
            </form>
          </div>
          <div class="schedule-table-wrap">
            <table class="schedule-table">
              <thead><tr><th>Ibadah</th><th>Tanggal &amp; waktu</th><th>Lokasi</th><th>Pelayan</th><th>Status</th><th>Tindakan</th></tr></thead>
              <tbody><tr>
                <td class="schedule-service-name" data-label="Ibadah">Ibadah Minggu</td>
                <td class="schedule-date" data-label="Tanggal &amp; waktu"><span>6 November 2026</span><span>09:00 WIT</span></td>
                <td data-label="Lokasi">Gedung Gereja</td>
                <td data-label="Pelayan">Pdt. Ny. J. K. Tutupaty</td>
                <td data-label="Status"><span class="schedule-status is-active">Aktif</span></td>
                <td data-label="Tindakan"><div class="schedule-row-actions"><a class="schedule-action-link" href="#">Ubah</a><button class="schedule-action-link" type="button">Batalkan</button></div></td>
              </tr></tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  `);

  for (const width of [880, 768, 390, 320]) {
    await page.setViewportSize({ width, height: 844 });
    const layout = await page.evaluate(() => {
      const table = document.querySelector('.schedule-table');
      const wrap = document.querySelector('.schedule-table-wrap');
      const input = document.querySelector('.schedule-search input').getBoundingClientRect();
      const action = document.querySelector('.schedule-action-link').getBoundingClientRect();
      const firstCellLabel = getComputedStyle(document.querySelector('.schedule-table td'), '::before').content;

      return {
        viewport: window.innerWidth,
        documentWidth: document.documentElement.scrollWidth,
        tableDisplay: getComputedStyle(table).display,
        tableWidth: table.getBoundingClientRect().width,
        wrapWidth: wrap.clientWidth,
        searchInputHeight: input.height,
        searchButtonCount: document.querySelectorAll('.schedule-search button[type="submit"]').length,
        actionHeight: action.height,
        firstCellLabel,
      };
    });

    expect(layout.documentWidth).toBeLessThanOrEqual(layout.viewport);
    expect(layout.tableDisplay).toBe('block');
    expect(layout.tableWidth).toBeLessThanOrEqual(layout.wrapWidth);
    expect(layout.searchInputHeight).toBeGreaterThanOrEqual(42);
    expect(layout.searchButtonCount).toBe(0);
    expect(layout.actionHeight).toBeGreaterThanOrEqual(44);
    expect(layout.firstCellLabel).toBe('"Ibadah"');
  }

  for (const width of [900, 1280]) {
    await page.setViewportSize({ width, height: 900 });
    const tableDisplay = await page.locator('.schedule-table').evaluate((table) => getComputedStyle(table).display);
    expect(tableDisplay).toBe('table');
  }
});
