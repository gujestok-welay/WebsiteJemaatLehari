# TASK-011 — Sinkronisasi urutan dan tautan berita publik

Tanggal: 5 November 2026.

## Tujuan

Menampilkan berita yang baru diterbitkan pada kartu terbaru beranda dan menyiapkan tautan langsung dari kartu ke URL artikel berbasis slug.

## Temuan

Beranda hanya menampilkan dua berita teratas. Query publik mengurutkan berita menurut `created_at`, sehingga draft lama yang baru diterbitkan dapat berada di bawah dua berita lain walaupun `updated_at`-nya paling baru.

## Perubahan

- Query berita terbit mengurutkan berdasarkan `updated_at`, lalu `created_at` dan `id` sebagai urutan stabil.
- Tanggal kartu publik mengikuti `updated_at` dengan fallback ke `created_at`.
- Kartu berita memakai URL `/berita/{slug}`. Halaman/rute baca detail belum dibuat dan tetap menjadi pekerjaan lanjutan.
- Beranda dan halaman berita menggunakan query urutan terbaru yang sama.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Models/BeritaModel.php` | Urutan berita publik berdasarkan waktu pembaruan/penerbitan. |
| `app/Controllers/Home.php` | Mengirim slug, URL detail, dan tanggal pembaruan ke kartu. |
| `app/Views/components/berita-card.php` | Menggunakan tautan langsung artikel. |
| `tests/unit/PublicPagesTest.php` | Regresi untuk draft lama yang baru diterbitkan dan URL detail berbasis slug. |
| `docs/dokumentasi-task/TASK-011-SINKRONISASI-BERITA-PUBLIK.md` | Catatan task dan hasil pemeriksaan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes regresi sebelum implementasi | FAIL sesuai harapan: draft lama yang baru diterbitkan tidak muncul di beranda dan kartu belum memakai URL slug. |
| `php vendor/bin/phpunit --no-coverage` | PASS — 122 tes, 608 assertion. |
| Browser beranda dan berita | PASS — dua berita teratas berurutan sama dan URL kartu memakai `/berita/{slug}`. |
| Pemeriksaan sintaks PHP | PASS. |
| `git diff --check` | PASS. |
