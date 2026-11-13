const { test, expect } = require('@playwright/test');

test('public schedule omits category buttons while keeping day and search controls', async ({ page }) => {
  await page.goto('/jadwal-ibadah');

  await expect(page.locator('.kat-chip')).toHaveCount(0);
  await expect(page.locator('#kat-filter')).toHaveCount(0);
  await expect(page.locator('.day-pill')).toHaveCount(8);
  await expect(page.getByRole('searchbox', { name: 'Cari ibadah' })).toBeVisible();
});

test('public day filters stay on one row and scroll on tablet widths', async ({ page }) => {
  await page.goto('/jadwal-ibadah');

  const tabs = page.locator('.day-tabs');
  for (const width of [900, 768]) {
    await page.setViewportSize({ width, height: 900 });

    const layout = await tabs.evaluate((element) => {
      const tops = [...element.children].map((button) => Math.round(button.getBoundingClientRect().top));

      return {
        rowCount: new Set(tops).size,
        clientWidth: element.clientWidth,
        scrollWidth: element.scrollWidth,
      };
    });

    expect(layout.rowCount).toBe(1);
    expect(layout.scrollWidth).toBeGreaterThan(layout.clientWidth);
  }
});
