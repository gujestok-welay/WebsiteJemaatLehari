# TASK-006F — Audit UI/UX halaman publik

Tanggal: 26 Oktober 2026. Target: implementasi lokal terkini di `http://localhost:8080`, setelah TASK-006E. Audit saja; tidak ada perbaikan kode aplikasi, perubahan DESIGN.md, backend, database, atau fitur admin.

## Keputusan

**Arah visual layak dipertahankan sebagai calon dasar DESIGN.md, tetapi belum siap dibakukan sebagai baseline final.** Selesaikan P1 fokus keyboard terlebih dahulu dan konsolidasikan aturan komponen bersama. Pasangan Cormorant Garamond 600 dan DM Sans, palet, serta komposisi editorial tidak memerlukan redesign berdasarkan bukti audit ini.

Integritas implementasi: **lulus untuk koherensi sistem visual, belum lulus untuk aksesibilitas interaksi**. Empat halaman memakai layout, stylesheet, token, navigasi, dan footer yang sama. Masalah utama adalah konflik specificity dengan Bootstrap, bukan pemilihan font. Persetujuan identitas mitra tetap terbuka menurut PRD DEC-09; audit ini bukan persetujuan mitra atau izin publikasi.

Temuan terverifikasi: **P1: 1 kelompok masalah sistemik; P2: 1; P3: 1**. Tidak ditemukan penghalang pemuatan halaman atau akses konten inti.

## Metode dan batasan

- Dibaca: `AGENTS.md`, `PRODUCT.md`, seluruh `docs/PRD.md` (draft untuk validasi), `docs/dokumentasi-task/TASK-006E-TYPOGRAPHY.md`, DESIGN.md sebagai konteks historis, controller publik, konten contoh, layout/partials/views publik, CSS, JavaScript, serta laporan inspector TASK-006E.
- Skill Impeccable `audit` digunakan. Launcher `context --target app/Views/publik` dicoba sekali: engine 0.1.5 belum terpasang dan direktori cache tidak dapat dibuat. Tidak menginstal engine atau mengubah konteks proyek. Audit manual mengikuti playbook `reference/audit.md`.
- Chrome headless melalui CDP pada 375×812, 768×1000, dan 1440×1000. Percobaan sandbox gagal pada `Page.enable`; pemeriksaan berhasil setelah izin menjalankan browser di luar sandbox.
- Satu putaran matriks dan satu putaran konfirmasi. Event Enter pada harness awal belum menyertakan virtual key/text yang diperlukan aktivasi native; harness dikoreksi lalu matriks dikonfirmasi. Hasil akhir menu/detail berasal dari putaran konfirmasi, bukan kegagalan harness awal.
- Seluruh 12 screenshot penuh dibuka dan ditinjau. Pemeriksaan DOM mencakup heading, font, ukuran target, warna terhadap latar solid, fokus aktual dengan Tab, skip link, aset/network, exception JavaScript, dan overflow. Ini pemeriksaan terarah, bukan sertifikasi WCAG.
- Panduan design-taste-frontend digunakan untuk menafsirkan detector lama. Lint sumber dijalankan; inspector visual TASK-006E dievaluasi dari `report.json`, **tidak dijalankan ulang dan tidak diklaim lulus**. Engine Impeccable/detector bawaannya tidak tersedia.
- Belum diuji: pembaca layar, perangkat fisik, browser selain Chrome, zoom browser 400%, seluruh lebar tambahan PRD, kondisi jaringan lambat, Core Web Vitals/CLS lab, artikel resmi panjang, dan UAT mitra. Pembesaran root font 200% hanya membuktikan tidak ada overflow horizontal pada kondisi tersebut; bukan uji zoom browser atau seluruh kemungkinan clipping.

## Audit Health Score (penilaian manual)

| Dimensi | Skor / 4 | Dasar |
| --- | --- | --- |
| Accessibility | 2 | Semantik, kontras teks dan skip link baik; fokus kontrol Bootstrap bermasalah |
| Performance | 3 | Font/aset lokal, foto 185.937 byte dan dimensi eksplisit; belum ada pengukuran lab |
| Responsive design | 3 | 12 tampilan dan pembesaran teks tanpa overflow; ukuran target bersama belum seragam |
| Theming | 3 | Warna dan tipografi memakai token; fokus select masih membawa biru Bootstrap |
| Implementation integrity | 3 | Sistem visual koheren; aturan bersama sebagian hanya diterapkan pada Beranda |
| **Total** | **14/20 — Good** | **Perbaiki dimensi yang lemah sebelum pembakuan** |

Skor ini perkiraan audit terbatas, bukan skor engine. Dark mode tidak dipersyaratkan dan ketiadaannya tidak dihitung sebagai defect.

## Matriks empat halaman

| Halaman | 375px | 768px | 1440px |
| --- | --- | --- | --- |
| Beranda | Terbaca; menu bekerja; P1 fokus, termasuk CTA | Komposisi dua kolom baik; P1 fokus | Hero/foto proporsional; P1 fokus |
| Profil | Terbaca; P1 fokus dan P2 target bersama | Dua kolom terbaca; P1 fokus | Hierarki jelas; P1 fokus |
| Berita | Baris menjadi satu kolom; P1/P2 | Arsip dua kolom terbaca; P1 fokus | Judul/ringkasan terpisah jelas; P1 fokus |
| Jadwal Ibadah | Filter/daftar bekerja; P1/P2 | P1 fokus; P3 WIT turun baris | Tiga kolom terbaca; P1 fokus |

Seluruh sel: tidak ada overflow horizontal, clipping berdasarkan pemindaian box ber-overflow hidden/clip, gambar rusak, respons aset HTTP ≥400, kegagalan jaringan teramati, exception JavaScript, atau permintaan aset eksternal. Screenshot tidak memperlihatkan tabrakan atau potongan teks. Ini **bukan kelulusan aksesibilitas menyeluruh** karena P1 tetap ada.

### Tipografi, gambar, dan konten

- Keempat font lokal berstatus `loaded` pada semua kombinasi. Computed style heading memakai Cormorant Garamond 600; isi dan UI memakai DM Sans. Pemeriksaan glyph font platform tidak diulang; konfirmasi glyph pada TASK-006E adalah bukti historis terpisah.
- H1 tunggal per halaman: 48px pada 375px, 49,92px pada 768px, dan 93,6px pada 1440px. Heading section 32–40px dan judul item DM Sans 600 menjaga peran editorial/UI. Isi 18px/1,65 dan metadata 16px terbaca pada screenshot; tidak ditemukan loncatan tingkat heading h1→h3.
- Warna teks pada kondisi awal terhadap latar solid yang terukur semuanya ≥4,5:1; nilai terendah sampel sekitar 6,54:1. Kondisi fokus select dibahas terpisah dalam P1. Tidak mengklaim seluruh state hover/disabled atau teks dalam foto telah diuji.
- Foto Beranda memiliki alt, dimensi intrinsik 1600×1200, rasio tampil 4:3 tanpa crop/distorsi, dan `fetchpriority="high"`. Eager loading tepat untuk gambar hero. Tidak ada gambar pada tiga halaman lain; ini bukan broken asset. Jumlah foto tidak menjadi syarat kelulusan situs informasi.
- `noindex, nofollow` terverifikasi pada DOM seluruh 12 tampilan (`app/Views/layouts/publik.php:8`). Banner dummy tetap tampil (`:19`), footer tetap menyatakan konten dummy (`app/Views/partials/footer.php:4`), dan berita/jadwal mempertahankan label contoh. Tidak ada konten resmi yang ditambahkan atau dikarang.

### Interaksi

- Menu 375px pada keempat route dapat dibuka dengan Enter; `aria-expanded=true`, keempat link terlihat dan tidak overflow. Urutan Tab menjangkau navigasi dan konten tanpa trap yang teramati. Pada 768/1440px navigasi horizontal tampil dan halaman aktif sesuai `aria-current`.
- Skip link mendapat outline dan Enter memindahkan fokus ke `main#konten` pada seluruh kombinasi.
- Ketiga `details` berita terbuka melalui Enter pada ketiga ukuran. Fokus summary terlihat. Tidak mengklaim pengujian ulang seluruh siklus tutup pada TASK-006F; siklus buka/tutup pernah diuji TASK-006E.
- ArrowDown pada filter mengganti ke Minggu dan status 2 hasil. Filter Minggu/Keluarga/Semua menghasilkan 2/1/3 item pada setiap ukuran. Status memakai `role=status` dan `aria-live=polite`.
- JavaScript dinonaktifkan pada 375px: semua link navigasi terlihat pada empat route, ketiga jadwal tersedia, dan kontrol filter disembunyikan. Tidak ada overflow.

## Temuan menurut prioritas

### F-01 — P1: indikator fokus komponen Bootstrap hilang atau terlalu lemah

**Kategori:** Accessibility / Theming / konflik CSS. **Lingkup:** navigasi semua halaman dan ukuran; tombol Menu pada 375px; select Jadwal semua ukuran; CTA Beranda kehilangan outline pada semua ukuran.

**Lokasi:** `public/assets/css/publik.css:75`, `:84`, `:119`, `:121`, `:156`; `app/Views/partials/navbar.php:4`, `:8`; `app/Views/publik/jadwal.php:5`. Aturan penimpa berada pada `public/assets/vendor/bootstrap/bootstrap.min.css:5` (file minified).

**Bukti:** ketika Tab benar-benar memfokuskan elemen, `:focus-visible=true`, tetapi computed outline `none 0px` dan box-shadow `none` pada `.nav-link`, `.navbar-toggler`, `.btn`, dan `.form-select`. Bootstrap memakai `.nav-link:focus-visible`, `.navbar-toggler:focus`, `.btn:focus-visible`, dan `.form-select:focus` yang lebih spesifik daripada `:focus-visible` global. CSS publik sekaligus mematikan shadow dengan `!important`. Urutan stylesheet setelah Bootstrap tidak mengalahkan specificity yang lebih tinggi.

- Link navigasi tidak mendapat penanda fokus baru; underline halaman aktif menunjukkan lokasi halaman, bukan posisi keyboard.
- Tombol Menu mempertahankan border yang sama saat tidak fokus, sehingga border itu tidak menunjukkan fokus.
- Select hanya berubah menjadi border `#86b7fe`; kontrasnya terhadap `#fffefa` sekitar **2,04:1**, di bawah 3:1 untuk indikator nonteks.
- CTA emas masih bergeser 2px pada `:focus` sebagai isyarat yang lemah. Outline tetap hilang; CSS reduced-motion juga menghilangkan transform tersebut, sehingga gerakan tidak dapat menjadi indikator yang diandalkan.

**Dampak/standar:** pengguna keyboard sulit mengetahui posisi navigasi atau kontrol yang akan diaktifkan. Hilangnya indikator pada navigasi/Menu bertentangan dengan WCAG 2.4.7 Focus Visible; indikator select perlu memenuhi kontras nonteks WCAG 1.4.11. Bukti screenshot: `.taste-skill/task-006f/beranda-375-menu-focus.jpg`, `jadwal-ibadah-768-select-focus.jpg`; detail computed style dalam `checks.json` → `results[].keyboard`.

**Rekomendasi:** tambahkan aturan `:focus-visible` yang secara eksplisit menargetkan kelas komponen Bootstrap dengan specificity memadai, memakai outline 3px dan offset 5px dari token publik. Gunakan warna kontras sesuai permukaan; jangan mengandalkan transform atau warna border bawaan. Pertahankan fokus skip link, summary dan footer yang sudah baik. Perbaiki di CSS publik, bukan file vendor. Uji kembali Tab pada 12 kombinasi dan reduced motion. **Perintah tahap perbaikan:** `$impeccable harden`.

### F-02 — P2: target sentuh komponen bersama belum konsisten antarhalaman

**Kategori:** Responsive / Implementation integrity. **Lokasi:** `public/assets/css/publik.css:84`–`:91`, `:165`, `:198`; `app/Views/layouts/publik.php:16`.

**Bukti:** pada 375px, tombol Menu Beranda berukuran sekitar 70,5×44px, sedangkan pada Profil/Berita/Jadwal sekitar 70,5×40,8px. Link “Kembali ke konten” setinggi 44px pada Beranda dan sekitar 42,4px pada halaman lain. Min-height dan aturan footer tertentu dibatasi `.page-home`, padahal markup navbar/footer sama. Perbedaan mengikuti body class yang hanya dipasang untuk Beranda.

**Dampak:** komponen bersama mempunyai jaminan target dan perilaku layout berbeda; pengguna sentuh mendapat area sedikit lebih kecil setelah berpindah halaman. **Ini bukan pelanggaran otomatis WCAG 2.5.8 AA**: target terukur masih melampaui minimum 24px. Patokan 44px di sini adalah rekomendasi kenyamanan sentuh dan konsistensi, bukan ambang wajib AA.

**Rekomendasi:** pindahkan jaminan min-height/min-width 44px untuk navbar/footer yang memang bersama ke selector umum; pertahankan override khusus komposisi Beranda bila diperlukan. Periksa kembali wrapping brand, link footer dan teks diperbesar. Tidak perlu menyeragamkan hero setiap halaman. **Perintah:** `$impeccable adapt`.

### F-03 — P3: satuan WIT terpisah dari angka pada tablet

**Kategori:** Responsive / typography polish. **Lokasi:** `public/assets/css/publik.css:139`, `:140`, `:157`; `app/Views/publik/jadwal.php:10`.

**Bukti:** screenshot `jadwal-ibadah-768.jpg` memperlihatkan 09.00/19.00 di baris pertama dan WIT di bawahnya pada ketiga jadwal. Pada 375/1440px satuan berada di samping angka. Kolom waktu `.7fr` pada grid tiga kolom belum cukup untuk angka 40px beserta satuan. Tidak terjadi overflow atau kehilangan informasi.

**Dampak:** pemindaian waktu/satuan sedikit kurang rapat dan ritme jadwal berubah pada tablet; informasi tetap terbaca.

**Rekomendasi:** berikan lebar kolom waktu yang cukup pada breakpoint tablet atau pindahkan grid ke susunan tablet yang menjaga angka+satuan sebagai kelompok. Jangan memasang nowrap tanpa memastikan lebar tersedia, terutama pada pembesaran teks. **Perintah:** `$impeccable typeset` atau `$impeccable adapt`.

## Evaluasi 2 FAIL dan 2 WARN TASK-006E

Sumber: `.taste-skill/task-006e-inspect-beranda/report.json`. Laporan tersebut hanya menginspeksi Beranda; hasilnya tidak boleh digeneralisasi sebagai inspector penuh empat halaman.

| Temuan asli | Penilaian audit kontekstual | Keputusan |
| --- | --- | --- |
| FAIL `few-pictures`: satu foto | Hitungan benar; tuntutan minimal empat foto merupakan aturan gaya generik. Tujuan halaman adalah akses informasi, bukan galeri. Foto Beranda menunjukkan gereja dan tampil utuh. | Tidak menjadi defect UX/P1. Foto berita/profil dapat ditambah saat materi relevan dan izin tersedia, tanpa memenuhi kuota rekaan. |
| FAIL `eyebrow`: “Dummy · Minggu, 25 Oktober 2026” di atas “09.00 WIT” | False positive semantik: tanggal, label contoh dan waktu adalah data jadwal yang dibutuhkan. Metrik viewport juga menganggap caption foto sebagai eyebrow menjelang bagian berikutnya. | Pertahankan tanggal, caption dan penanda dummy. Jangan menghapus informasi agar detector hijau. |
| WARN `palette-cream`: `#fffefa` | Nilai warna benar; tidak ditemukan kegagalan kontras teks akibat warna ini. Palet yang sedang diaudit tidak perlu diganti karena preferensi galeri. | Pertahankan; konfirmasi palet dengan mitra tetap terpisah menurut DEC-09. |
| WARN `no-footage`: tidak ada video | Pengamatan benar, tetapi video tidak diperlukan untuk menemukan jadwal atau membaca berita dan menambah biaya transfer. | Tidak menjadi defect; jangan menambahkan video untuk mengejar skor. |

Hasil mentah tetap **2 FAIL/2 WARN**, bukan diubah menjadi 0/0. Keempatnya tidak membuktikan masalah fungsional/aksesibilitas dalam konteks ini. Keterbatasan konten resmi tetap nyata sebagai prasyarat publikasi, tetapi solusinya validasi materi jemaat, bukan foto/video generik. P1 fokus yang ditemukan audit ini justru tidak terwakili oleh empat flag tersebut.

## Konsistensi dan kesiapan dokumentasi

Yang perlu dipertahankan: pasangan font dan pembagian peran serif/sans; margin responsif bersama; bentuk siku; baris berita/jadwal; navigasi sederhana; foto tanpa crop; status filter; fallback tanpa JS; banner dummy dan robots. Beranda terang dengan foto dan intro ungu pada tiga halaman lain merupakan variasi komposisi yang terarah, bukan konflik visual.

Pola sistemik yang perlu diselesaikan: global focus terlalu lemah terhadap specificity Bootstrap, serta jaminan ukuran komponen bersama yang masih terisolasi pada `.page-home`. Override `.home-news` atas `.news-row` dan pengecualian font judul item konsisten dengan peran komponen; tidak ditemukan tabrakan layout dari override tersebut pada 12 tampilan.

DESIGN.md masih merekam Georgia/Arial, paper `#ffffff`, surface `#f3f0f4`, dan ukuran hero lama. Implementasi memakai Cormorant/DM Sans, paper `#fffefa`, surface `#f5f1f6`, serta token TASK-006E. Ini drift yang diketahui, **bukan instruksi untuk memulihkan implementasi lama**. DESIGN.md tidak disentuh. Saat tahap dokumentasi disetujui, ambil nilai aktual dari `publik.css`, cantumkan pengecualian judul item/footer, dan pisahkan fakta implementasi dari identitas resmi yang belum disahkan.

Urutan lanjutan yang disarankan, masing-masing sebagai tahap tersendiri: `$impeccable harden` untuk P1; `$impeccable adapt` untuk komponen bersama dan tablet; `$impeccable polish` sebagai pemeriksaan akhir perbaikan. Setelah verifikasi, `$impeccable document` dapat dipakai untuk memperbarui DESIGN.md. Perintah dapat diminta satu per satu sesuai prioritas; jalankan ulang `$impeccable audit` setelah perbaikan. Tidak ada perintah perbaikan yang dijalankan pada task ini.

## Pengujian dan artefak

- Browser/CDP: 12 kombinasi, fokus Tab, Enter menu/detail, skip link, filter, root text 200%, dan fallback tanpa JS seperti dijelaskan di atas. **Hasil: fungsi dasar bekerja, P1/P2/P3 tetap terbuka.**
- Lint `public/assets/css/publik.css public/assets/js/publik.js app/Views/layouts/publik.php`: **0 FAIL, 0 WARN**; hanya dua file CSS/JS didukung scanner, PHP tidak dipindai oleh lint tersebut.
- `git diff --check`: lulus; Git hanya memberi peringatan normalisasi LF→CRLF pada beranda yang sudah berubah sebelum audit.
- PHPUnit **tidak dijalankan**: `vendor/bin/phpunit` tidak tersedia. Tidak menjalankan tes database, migrasi, autentikasi atau admin.
- Laporan ini adalah satu-satunya dokumen proyek baru. Harness audit dan bukti lokal berada pada `.taste-skill/task-006f-audit.mjs`, `.taste-skill/task-006f/checks.json`, 12 screenshot penuh, dan screenshot fokus tambahan (folder artefak diabaikan Git). Artefak historis TASK-006E tidak ditimpa.
