'use strict';

// Progressive enhancement: the full schedule remains available without JavaScript.
const serviceSelect = document.querySelector('#jenis-ibadah');
if (serviceSelect) {
    const serviceRows = Array.from(document.querySelectorAll('[data-service]'));
    const status = document.querySelector('#hasil-jadwal');
    document.querySelector('.schedule-filter').hidden = false;
    serviceSelect.addEventListener('change', () => {
        let count = 0;
        serviceRows.forEach((row) => {
            row.hidden = serviceSelect.value !== 'semua' && row.dataset.service !== serviceSelect.value;
            if (!row.hidden) count += 1;
        });
        status.textContent = `${count} jadwal contoh ditampilkan.`;
    });
}
