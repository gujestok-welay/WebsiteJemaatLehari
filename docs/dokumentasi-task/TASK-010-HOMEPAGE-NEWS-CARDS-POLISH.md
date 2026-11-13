# TASK-010 — Penyempurnaan ukuran kartu berita beranda

Tanggal: 5 November 2026.

## Tujuan

Membuat kartu berita beranda lebih ringkas dan mudah dipindai tanpa mengubah kartu pada halaman arsip berita.

## Perubahan

- Kartu beranda ditata dua kolom pada tablet dan desktop, serta satu kolom pada ponsel.
- Tinggi gambar dipadatkan menjadi 170 px; judul dibatasi dua baris dan ringkasan tiga baris.
- Padding dan ukuran teks disesuaikan khusus untuk kartu beranda.
- Tampilan kartu arsip berita tetap menggunakan ukuran yang sudah ada.

## File terdampak

| File | Perubahan |
| --- | --- |
| `public/assets/css/publik.css` | Tata letak dan ukuran kartu berita beranda yang responsif. |
| `docs/dokumentasi-task/TASK-010-HOMEPAGE-NEWS-CARDS-POLISH.md` | Catatan task dan hasil pemeriksaan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Browser beranda pada 1280, 768, dan 390 px | PASS — dua kolom pada 1280/768 px, satu kolom pada 390 px, tinggi kartu 376–381 px, tanpa overflow horizontal. |
| Browser halaman berita | PASS — tinggi gambar kartu arsip tetap 240 px. |
| `php vendor/bin/phpunit --no-coverage` | PASS — 120 tes, 599 assertion. |
| `git diff --check` | PASS. |
