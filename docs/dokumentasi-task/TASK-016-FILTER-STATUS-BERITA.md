# TASK-016 — Filter status berita langsung

Tanggal: 9 November 2026.

## Tujuan

Menampilkan berita sesuai status segera setelah status dipilih, tanpa memuat ulang seluruh halaman admin.

## Perubahan

- Perubahan status mengirim formulir pencarian melalui navigasi parsial admin, termasuk kata kunci pencarian yang sedang diisi.
- URL dan riwayat browser tetap mengikuti filter aktif.
- Fokus keyboard dikembalikan ke pilihan status setelah daftar diperbarui.
- Perilaku formulir tanpa JavaScript tetap tersedia.

## File terdampak

| File | Perubahan |
| --- | --- |
| `public/assets/js/admin.js` | Menangani perubahan status melalui respons parsial dan mempertahankan fokus kontrol. |
| `e2e/news-status-filter.spec.js` | Menambahkan tes regresi pemilihan status tanpa muat ulang halaman. |
| `docs/dokumentasi-task/TASK-016-FILTER-STATUS-BERITA.md` | Mencatat lingkup dan hasil verifikasi task. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes regresi sebelum implementasi | FAIL sesuai harapan — perubahan status belum mengirim permintaan filter. |
| Tes regresi setelah implementasi | PASS — respons parsial memperbarui hasil tanpa memuat ulang dokumen. |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 190 tes, 1.170 assertion. |
| Playwright seluruh suite | 25 lulus, 39 gagal. Tes filter status lulus; kegagalan berada pada tes Profil, halaman publik, Jadwal, dan tema di luar perubahan task ini. |
| Pemeriksaan sintaks JavaScript dan `git diff --check` | PASS. |
