# TASK-012 — Perbaikan overflow horizontal Profil & Majelis

Tanggal: 5 November 2026.

## Tujuan

Menghilangkan scroll horizontal pada halaman admin Profil & Majelis di layar kecil tanpa menghilangkan scroll lokal yang dibutuhkan tabel periode dan anggota.

## Temuan

Pada viewport 390 px, lebar dokumen mencapai 617 px. Akar masalahnya adalah label header tabel yang memakai `.visually-hidden` dan `position: absolute`; tanpa containing block pada sel header, posisi label ikut memperlebar halaman meskipun tabel berada di dalam pembungkus `overflow-x: auto`.

## Perubahan

Sel header tabel profil diberi `position: relative` agar label tersembunyi tetap terikat pada selnya. Formulir, tabel, label aksesibel, konten, dan perilaku scroll lokal tidak diubah.

## File terdampak

| File | Perubahan |
| --- | --- |
| `public/assets/css/admin.css` | Membatasi containing block label tersembunyi pada header tabel Profil & Majelis. |
| `docs/dokumentasi-task/TASK-012-PROFIL-MOBILE-OVERFLOW.md` | Catatan task dan hasil pemeriksaan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Uji browser sebelum perubahan, viewport 390 px | FAIL sesuai harapan: dokumen 617 px untuk viewport 390 px. |
| Uji browser setelah perubahan, viewport 390/768/1440 px | PASS: tidak ada overflow horizontal; tabel tetap dapat digulir lokal di viewport 390 px. |
| Navigasi bagian Profil → Anggota Majelis dengan klik | PASS: tautan berpindah ke `#anggota-panel`. |
| `vendor\bin\phpunit --no-coverage` | PASS — 122 tes, 608 assertion. |
| `git diff --check -- public/assets/css/admin.css` | PASS. |
| Impeccable detector pada `public\assets\css\admin.css` | Berjalan; 49 advisory terkait warna/radius pada stylesheet, tanpa temuan pada aturan baru. |

## Catatan

`AdminPartialNavigationTest` saat dijalankan sendiri menghasilkan 2 error dan 3 failure pada render view Berita, Jadwal, Profil, dan Statistik. Kegagalan tersebut tidak terkait perubahan CSS ini; seluruh suite lulus dengan perintah di atas. Tidak ada perubahan database atau konten.
