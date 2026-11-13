const { test, expect } = require('@playwright/test');

test.use({ viewport: { width: 375, height: 812 } });

test('daftar berita tetap utuh dan pencarian tersusun rapi di layar ponsel', async ({ page }) => {
  const stylesheetUrl = new URL('/assets/css/admin.css', test.info().project.use.baseURL);
  await page.goto(stylesheetUrl.href);
  await page.setContent(`
    <link rel="stylesheet" href="/assets/css/admin.css">
    <main class="admin-content">
      <section class="news-panel" aria-label="Daftar berita">
        <div class="news-toolbar">
          <p class="news-result-count">1 berita</p>
          <form class="news-search" role="search">
            <input type="search" placeholder="Cari judul berita" aria-label="Cari judul berita">
            <select aria-label="Filter status berita"><option>Semua status</option></select>
            <button class="button" type="submit">Cari</button>
          </form>
        </div>
        <div class="news-table-wrap">
          <table class="news-table">
            <thead><tr><th>Judul</th><th>Status</th><th>Ringkasan</th><th class="news-updated-column">Pembaruan</th><th>Tindakan</th></tr></thead>
            <tbody><tr>
              <td class="news-title-cell"><strong>Berita uji responsif ponsel</strong><span>/berita-uji-responsif-ponsel</span></td>
              <td><span class="news-status news-status-draft">Draft</span></td>
              <td class="news-summary">Ringkasan panjang untuk memastikan isi berita tetap muat di layar ponsel tanpa terpotong.</td>
              <td class="news-updated-column">2026-11-07 05:00:00</td>
              <td><div class="news-actions"><button class="news-action-link" type="button">Ubah</button></div></td>
            </tr></tbody>
          </table>
        </div>
      </section>
    </main>
  `);
  await expect(page.locator('.news-panel')).toBeVisible();

  for (const width of [375, 320]) {
    await page.setViewportSize({ width, height: 812 });
    const layout = await page.evaluate(() => {
      const panel = document.querySelector('.news-panel').getBoundingClientRect();
      const table = document.querySelector('.news-table').getBoundingClientRect();
      const input = document.querySelector('.news-search input').getBoundingClientRect();
      const status = document.querySelector('.news-search select').getBoundingClientRect();
      const submit = document.querySelector('.news-search button').getBoundingClientRect();

      return {
        documentWidth: document.documentElement.scrollWidth,
        viewportWidth: document.documentElement.clientWidth,
        panelRight: panel.right,
        tableRight: table.right,
        inputTop: input.top,
        statusTop: status.top,
        submitTop: submit.top,
      };
    });

    expect(layout.documentWidth).toBeLessThanOrEqual(layout.viewportWidth);
    expect(layout.tableRight).toBeLessThanOrEqual(layout.panelRight);
    expect(layout.submitTop).toBe(layout.inputTop);
    expect(layout.statusTop).toBeGreaterThan(layout.inputTop);
  }
});
