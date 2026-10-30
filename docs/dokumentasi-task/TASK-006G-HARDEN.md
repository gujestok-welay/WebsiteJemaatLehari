# TASK-006G — Perbaikan tiga temuan audit UI/UX

Tanggal pengujian: 26 Oktober 2026. Acuan: AGENTS.md, PRODUCT.md, PRD berstatus draft untuk validasi, dan TASK-006F-AUDIT.md. Skill Impeccable `harden` v4.3.1 berhasil memuat konteks.

Perubahan implementasi tahap ini hanya pada `public/assets/css/publik.css`; perubahan lain yang sudah ada di workspace tidak menjadi bagian task ini. Konten, foto, font Cormorant Garamond/DM Sans, palet ungu-emas, serta desain editorial dipertahankan. DESIGN.md, PHP, JavaScript, backend, autentikasi, database, migration, dan vendor Bootstrap tidak diedit pada tahap ini.

## Perbaikan

- **P1 / F-01:** selector `:focus-visible` komponen navbar, tombol Menu, `.btn`, dan `.form-select` mendapat specificity yang mengatasi reset Bootstrap. Outline solid 3px, offset 5px, tanpa bergantung pada shadow atau gerakan. Warna ungu untuk permukaan terang, ungu gelap di hero Beranda, dan emas di footer tetap mengikuti permukaannya. Aturan fokus berlaku juga pada reduced-motion.
- **P2 / F-02:** jaminan minimum 44×44 CSS px pada merek, Menu, link navbar, dan link footer berlaku di semua halaman. Aturan wrapping dan penataan footer/mobile yang sebelumnya dibatasi `.page-home` dipindahkan ke selector bersama.
- **P3 / F-03:** kolom tengah grid jadwal menggunakan lebar intrinsik (`auto`) agar angka dan WIT mendapat ruang pada 768px. Container query `38rem` menyusun jadwal menjadi satu kolom saat ruang relatif terhadap ukuran teks tidak cukup. Tidak ada `nowrap` yang memaksa overflow. Susunan mobile berbasis media query tetap tersedia.

## Metode dan bukti

Browser Chrome headless melalui CDP, server lokal `http://localhost:8080`. Empat rute diuji pada 375, 768, dan 1440 CSS px; tiap kombinasi diuji dalam kondisi normal, `prefers-reduced-motion: reduce`, dan teks 200% (root font-size 32px). Ini simulasi pembesaran teks di browser, bukan pengujian perangkat fisik atau semua engine browser.

Harness lokal: `.taste-skill/task-006g-check.mjs`. Hasil akhir: `.taste-skill/task-006g/checks.json` dan `summary.json`; screenshot halaman serta fokus berada di folder yang sama. Artefak lokal diabaikan Git. Screenshot diperiksa untuk fokus CTA/select, tablet, pembesaran teks, dan sampel halaman lain. Pemeriksaan metrik mencakup seluruh kombinasi.

Putaran visual pertama menemukan kolom nama/lokasi jadwal terlalu sempit pada teks 200%; container query ditambahkan untuk menanganinya. Perekaman screenshot pada putaran konfirmasi sempat mengalami timeout CDP. Pengukuran seluruh skenario kemudian diulang dengan `--metrics-only`, tanpa mengubah UI lagi, untuk memperoleh laporan akhir lengkap.

Harness interaksi disesuaikan dengan URL lokal yang mengandung `index.php`, perilaku select native (ArrowDown lalu Escape), dan penantian navigasi CTA hingga halaman tujuan benar-benar dimuat. Dua pemeriksaan CTA yang semula diperiksa terlalu dini diulang menggunakan Tab/Enter melalui `.taste-skill/task-006g-cta.mjs`; keduanya PASS. Ringkasan akhir dihasilkan oleh `.taste-skill/task-006g-summary.mjs`, exit 0.

## Hasil browser aktual

Setiap PASS pada tabel mencakup kondisi normal, reduced-motion, dan teks 200%.

| Halaman | 375px | 768px | 1440px |
| --- | --- | --- | --- |
| Beranda | PASS | PASS | PASS |
| Profil | PASS | PASS | PASS |
| Berita | PASS | PASS | PASS |
| Jadwal Ibadah | PASS | PASS | PASS |

- **PASS — P1:** 399 pengamatan fokus keyboard, termasuk navbar, Menu, CTA, select, skip link, summary, dan footer. Outline solid 3px / offset 5px; kontras terhadap permukaan sekitar minimal **6,7:1** (ambang pengujian 3:1). Tidak ditemukan outline terpotong oleh ancestor. Fokus tetap terlihat dan transisi nonaktif dalam reduced-motion.
- **PASS — P2:** seluruh target navbar/footer yang terlihat berukuran minimal 44×44 CSS px pada 36 kondisi. Navbar mobile juga diukur setelah dibuka.
- **PASS — P3:** angka dan WIT sebaris pada seluruh ukuran normal, termasuk tiga jadwal pada 768px. Teks 200% di tablet beralih ke susunan satu kolom; tidak ditemukan overflow waktu atau teks terpotong. Wrapping tetap diizinkan saat ruang sempit.
- **PASS — interaksi:** Tab dan skip link bekerja; Menu dapat dibuka/ditutup memakai Enter di keempat halaman; CTA menuju halaman jadwal; summary berita terbuka melalui Enter. Select dapat diubah lewat keyboard, filter Minggu/Keluarga/Semua menghasilkan **2/1/3** jadwal dengan status yang sesuai.
- **PASS — regresi:** tidak ditemukan overflow horizontal, clipping teks, elemen konten melampaui viewport, error JavaScript, atau respons aset HTTP ≥400 pada 36 kondisi. Foto termuat; banner dummy dan `noindex, nofollow` tetap ada.
- **PASS — tanpa JavaScript:** navigasi terlihat di empat halaman pada 375px; tiga jadwal tetap tersedia dan filter disembunyikan; tidak ada overflow.

## Pemeriksaan sumber

- **PASS:** Stylelint (`npm exec --yes --package=stylelint -- stylelint public/assets/css/publik.css --config .taste-skill/task-006g-stylelint.json`), exit 0. Konfigurasi lokal memeriksa sintaks, blok kosong, hex warna, properti duplikat/tidak dikenal, shorthand yang saling menimpa, pseudo-class/pseudo-element, dan unit.
- **PASS:** lint CSS proyek (`node .codex/skills/design-taste-frontend/scripts/lint.mjs public/assets/css/publik.css`), **0 FAIL / 0 WARN**.
- Detector Impeccable dijalankan sekali dengan `detect --json public/assets/css/publik.css`. Detector melaporkan warning font Cormorant Garamond/DM Sans dan advisory ukuran tipografi terhadap DESIGN.md yang tertinggal; ini drift yang sudah dicatat TASK-006F. Bukan alasan mengganti font atau memperbarui DESIGN.md dalam task ini.
- `php -l` tidak dijalankan karena tidak ada PHP yang diubah pada tahap ini. PHPUnit, pengujian database, autentikasi, dan admin tidak dijalankan; pengujian task ini memakai browser publik.

- **PASS:** `git diff --check`. Git hanya memberi peringatan normalisasi LF→CRLF untuk `app/Views/publik/beranda.php` yang sudah berubah sebelum task ini, tanpa error whitespace.

Tidak ada FAIL tersisa untuk tiga temuan audit pada lingkungan yang diuji. Drift DESIGN.md tetap terbuka sesuai batasan pengguna. Pengujian lintas engine browser, perangkat sentuh fisik, dan UAT warga jemaat belum dilakukan; hasil di atas bukan klaim kepatuhan aksesibilitas penuh.
