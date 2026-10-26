---
name: Website Informasi Jemaat Leahari
description: Sistem desain aktual empat halaman publik; keputusan proyek, bukan identitas resmi gereja.
colors:
  purple: "#663f72"
  purple-dark: "#482950"
  gold: "#d8bd72"
  paper: "#fffefa"
  surface: "#f5f1f6"
  ink: "#29232c"
  muted: "#5d5361"
typography:
  display:
    fontFamily: "'Cormorant Garamond', Georgia, 'Times New Roman', serif"
    fontSize: "clamp(3.25rem, 6.5vw, 6rem)"
    fontWeight: 600
    lineHeight: 1.1
    letterSpacing: "-.02em"
  headline:
    fontFamily: "'Cormorant Garamond', Georgia, 'Times New Roman', serif"
    fontSize: "clamp(3rem, 6.5vw, 6.5rem)"
    fontWeight: 600
    lineHeight: 1.1
    letterSpacing: "-.02em"
  title:
    fontFamily: "'Cormorant Garamond', Georgia, 'Times New Roman', serif"
    fontSize: "clamp(2rem, 3vw, 2.5rem)"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-.015em"
  item:
    fontFamily: "'DM Sans', Arial, Helvetica, sans-serif"
    fontSize: "clamp(1.5rem, 2.2vw, 2rem)"
    fontWeight: 600
    lineHeight: 1.35
    letterSpacing: "0"
  body:
    fontFamily: "'DM Sans', Arial, Helvetica, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.65
  label:
    fontFamily: "'DM Sans', Arial, Helvetica, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.65
  footer:
    fontFamily: "'DM Sans', Arial, Helvetica, sans-serif"
    fontSize: "clamp(2.5rem, 8vw, 8rem)"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-.02em"
rounded:
  square: "0"
spacing:
  frame: "clamp(1.25rem, 4vw, 4rem)"
  home-space: "clamp(1.5rem, 3vw, 3rem)"
  home-updates: "clamp(2rem, 4vw, 4rem)"
  section: "clamp(3.5rem, 7vw, 7rem)"
  about-preview: "clamp(2.5rem, 4vw, 4rem)"
  schedule-panel: "clamp(1.25rem, 2.5vw, 2.5rem)"
components:
  button-primary:
    backgroundColor: "{colors.gold}"
    textColor: "{colors.ink}"
    rounded: "{rounded.square}"
    padding: ".85rem 1.35rem"
  schedule-panel:
    backgroundColor: "{colors.surface}"
    padding: "{spacing.schedule-panel}"
---

# Design System: Website Informasi Jemaat Leahari

## Overview

TASK-006H, 26 Oktober 2026. Dokumen ini merekam implementasi aktual Beranda `/`, Profil `/profil`, Berita `/berita`, dan Jadwal Ibadah `/jadwal-ibadah`, setelah tipografi TASK-006E dan perbaikan TASK-006G. Sumber utama: [publik.css](public/assets/css/publik.css), [layout publik](app/Views/layouts/publik.php), [navbar](app/Views/partials/navbar.php), [footer](app/Views/partials/footer.php), [views publik](app/Views/publik/), dan [publik.js](public/assets/js/publik.js). Kode aktual menjadi sumber fakta implementasi; keputusan produk terbuka tetap mengikuti proses validasi.

**Arah observasional: “Ruang Informasi yang Terbuka”.** Judul editorial serif, isi dan kontrol sans-serif, ruang baca lapang, bidang warna datar, serta sudut siku membentuk karakter halaman publik. Beranda memakai hero terang dengan foto gereja utuh; tiga halaman lainnya memakai pengantar ungu. Perbedaan ini adalah pola aktual, bukan instruksi menyeragamkan semua hero menjadi ungu.

**Status keputusan:** font, palet, dan komposisi di sini merupakan keputusan desain proyek yang telah diterapkan. Identitas resmi gereja, logo, palet, dan label menu masih memerlukan persetujuan Media Center/Majelis sesuai DEC-09 dalam [PRD](docs/PRD.md), yang tetap **draft untuk validasi**. Nama arah di atas bukan slogan resmi. Sejarah, susunan Majelis, kontak, jadwal, serta hak publikasi foto harus divalidasi terpisah; keberadaan aset di repositori bukan bukti izin publikasi.

**Status pengembangan:** banner “Pratinjau dengan konten dummy”, label contoh berita/jadwal, keterangan footer, dan meta robots `noindex, nofollow` masih berlaku. Jangan menghapusnya sebelum konten dan kesiapan publikasi diperiksa. Konten contoh bukan informasi resmi; sorotan jadwal bukan perhitungan otomatis ibadah terdekat.

Frontmatter merangkum nilai yang dipakai. Nama spacing selain `frame`/`home-space` merupakan nama dokumentasi untuk nilai komponen yang diekstrak, bukan klaim adanya custom property global baru. Breakpoint, fokus, gerak, dan state dicatat di bawah. Tahap ini hanya memperbarui DESIGN.md; PRODUCT.md, PRD, dan sidecar `.impeccable/design.json` tidak disinkronkan sebagai efek samping.

## Colors

Nilai berikut sama dengan custom properties `:root` pada CSS publik. Nama warna bersifat deskriptif, bukan identitas resmi yang telah disahkan.

| Token CSS | Nilai | Penggunaan aktual |
| --- | --- | --- |
| `--purple` | `#663f72` | Intro halaman dalam, merek navbar, tautan umum, navigasi aktif, outline permukaan terang |
| `--purple-dark` | `#482950` | Footer, judul dan fokus hero Beranda, hover tautan umum |
| `--gold` | `#d8bd72` | CTA utama Beranda, outline fokus footer |
| `--paper` | `#fffefa` | Latar halaman, teks intro ungu/footer, latar skip link |
| `--surface` | `#f5f1f6` | Banner dummy, panel jadwal, preview profil, bagian profil dan baris arsip berselang |
| `--ink` | `#29232c` | Isi, navigasi biasa, judul item bertaut, teks CTA emas |
| `--muted` | `#5d5361` | Metadata, caption, sublabel merek, pesan kosong Beranda |

`--gold-soft: #eee2bd` masih dideklarasikan, tetapi belum digunakan oleh selector publik; bukan token aktif untuk komponen baru dan tidak dimasukkan ke palet frontmatter. Pemilihan teks memakai `paper` di atas `purple`. Warna bawaan Bootstrap bukan pengganti token proyek.

## Typography

**Editorial:** Cormorant Garamond **600**, gaya normal. **Isi/UI:** DM Sans **400/600/700**, gaya normal. Heading h1–h6 memakai font editorial, dengan pengecualian eksplisit judul item berita/jadwal. Seluruh footer, navbar, tombol, label, input/select/textarea, metadata, dan waktu memakai DM Sans.

### Token dan hierarki

| Peran / token CSS | Ukuran | Bobot | Leading / tracking | Penerapan |
| --- | --- | --- | --- | --- |
| `--font-size-hero` | `clamp(3.25rem, 6.5vw, 6rem)` | 600 | `1.1` / `-.02em` | h1 Beranda; override HP/tablet di Layout |
| `--font-size-page` | `clamp(3rem, 6.5vw, 6.5rem)` | 600 | `1.1` / `-.02em` | h1 halaman dalam |
| `--font-size-section` | `clamp(2rem, 3vw, 2.5rem)` | 600 | `1.2` / `-.015em` | h2/h3 editorial |
| `--font-size-item` | `clamp(1.5rem, 2.2vw, 2rem)` | 600 | `1.35` / `0` | `.news-row h3`, `.schedule-row h3`, `.schedule-highlight h3`; DM Sans |
| `--font-size-body` | `1.125rem` | 400 | `1.65` / bawaan | Isi (18px pada root 16px) |
| `--font-size-meta` | `1rem` | 400 | `1.65` / bawaan | Metadata; label filter juga 1rem |
| `--font-size-footer` | `clamp(2.5rem, 8vw, 8rem)` | 600 | `1.2` / `-.02em` | Nama besar footer; DM Sans |

Bobot dipetakan ke `--font-weight-body: 400`, `--font-weight-heading: 600`, `--font-weight-ui: 600`, `--font-weight-strong: 700`. Leading: `--font-leading-body: 1.65`, `--font-leading-display: 1.1`, `--font-leading-heading: 1.2`, `--font-leading-ui: 1.35`. Tracking: `--font-tracking-display: -.02em`, `--font-tracking-heading: -.015em`, `--font-tracking-ui: 0`.

Paragraf dibatasi `--font-measure-body: 65ch`; heading memakai `text-wrap: balance`, paragraf/judul item `pretty`. Hero intro memakai `clamp(1.125rem, 1.4vw, 1.25rem)`, maksimum 34ch (44ch di HP). Lead `.intro-text` memakai `1.5rem/1.55`, menjadi `1.25rem` di HP. Intro halaman dalam memakai 1.5rem, maksimum 58ch, menjadi 1.125rem di HP. Navbar brand memakai `1.5rem/1.4` bobot 700, sublabel 1rem/400; link navbar dan tombol 1.125rem (tombol 700). `strong`/`b` memakai 700. Waktu memakai `2.5rem/1.2`, angka tabular, dengan satuan WIT 1rem.

### Pengiriman dan fallback font

Stack CSS aktual:

```css
--display-font: 'Cormorant Garamond', Georgia, 'Times New Roman', serif;
--body-font: 'DM Sans', Arial, Helvetica, sans-serif;
```

Georgia/Arial hanya **fallback**, bukan font utama atau aturan desain lama yang harus dipulihkan. Empat WOFF2 Latin statis dilayani lokal dari `public/assets/fonts/`: Cormorant 600 dan DM Sans 400/600/700. Semua `@font-face` memakai `font-display: swap`. Layout melakukan preload Cormorant 600 dan DM Sans 400 (`as="font"`, `type="font/woff2"`, `crossorigin`); DM Sans 600/700 dimuat saat digunakan. Tidak ada ketergantungan font CDN saat runtime.

Saat font belum tersedia/gagal atau glyph tidak tercakup, stack sistem menjaga konten terbaca; metrik dan pemenggalan dapat berbeda. Hanya gaya normal yang disediakan; italic/bobot lain bukan aset font terpisah yang sudah tersedia. Provenance Fontsource 5.3.0, total 66.088 byte, hash, dan lisensi SIL OFL ada di [README font](public/assets/fonts/README.md). Pertahankan lisensi saat distribusi.

## Layout

### Grid dan ritme ruang

Bootstrap 5 lokal dimuat sebelum CSS publik. `.container-fluid.site-frame` memberi bingkai horizontal `--frame: clamp(1.25rem, 4vw, 4rem)`; tidak ada lebar maksimum konten global tambahan di CSS publik.

| Pola | Susunan dan spacing normal |
| --- | --- |
| `.home-hero` | `minmax(0, .8fr) minmax(0, 1.3fr)`, align center; gap/padding-block `--home-space: clamp(1.5rem, 3vw, 3rem)` |
| `.home-updates` | Berita/jadwal `minmax(0, 1.3fr) minmax(0, .9fr)`; gap/padding-block `clamp(2rem, 4vw, 4rem)` |
| `.section-space` | Padding-block `clamp(3.5rem, 7vw, 7rem)`; preview profil menimpanya dengan `clamp(2.5rem, 4vw, 4rem)` |
| `.about-preview`, `.profile-section` | `minmax(0, 1fr) minmax(0, 1.5fr)`; gap 3rem. Preview Beranda memakai gap `clamp(1.25rem, 4vw, 3rem)` |
| `.profile-section` | Padding-block 3.25rem |
| `.page-intro`, `.news-archive`, `.schedule-section` | Padding-block 3.5rem |
| `.news-row` arsip | `1fr 1fr`, gap 3rem; padding `2rem 1.5rem` |
| `.schedule-row` | `minmax(0, 1.5fr) auto minmax(0, 1fr)`, gap 2rem; padding `2rem 1.5rem` |
| `.section-heading` | Flex-wrap, justify space-between, gap 1.5rem; margin bawah 2rem (berita Beranda 1rem) |

Ini ritme per komponen, bukan skala spacing global seragam. Nilai berulang meliputi .5/.75/1/1.25/1.5/2/2.5/3/3.5rem sesuai selector. Pertahankan `min-width: 0`, pembungkusan teks, dan grid `minmax(0, …)` pada pola yang memakainya.

### Responsivitas

| Kondisi aktual | Perilaku |
| --- | --- |
| `max-width: 767.98px` | Navbar collapse (Bootstrap `md`); hero, updates, berita, profil, preview, jadwal satu kolom; `.section-heading` vertikal |
| HP pada breakpoint di atas | Hero gap/padding-block 1.5rem, h1 `clamp(3rem, 9vw, 3.5rem)` dan `<br>` disembunyikan; updates gap 2rem, padding-block `1.5rem 2.5rem`; intro/profil padding-block 2.5rem |
| HP untuk baris/filter | Gap berita/profil/jadwal 1.25rem; padding-inline arsip/jadwal 1rem (berita Beranda tetap 0); filter/select lebar 100%, min-width 0 |
| `max-width: 575.98px` | CTA hero bertumpuk dan selebar area; footer atas/bawah vertikal, align start, gap 1rem |
| `768px–1023.98px` | Hero `1fr 1.2fr` dengan minmax nol, gap 2rem; h1 `clamp(3rem, 6.5vw, 4.25rem)`; updates `1fr 1fr`, gap 2rem |
| `@container (max-width: 38rem)` | `.schedule-list` memakai `container-type: inline-size`; baris jadwal satu kolom, gap 1.25rem dan margin waktu 0 saat ruang relatif terhadap teks tidak cukup |

Kolom waktu `auto` menyisakan lebar intrinsik angka+WIT tanpa memaksa `nowrap`; teks tetap boleh membungkus saat diperbesar. Container query melengkapi media query HP. Target navbar (merek/Menu/link) dan tautan footer minimum **44×44 CSS px** berlaku di seluruh empat halaman. Beberapa tautan konten/summary/CTA memakai min-height 44px; ini bukan jaminan universal semua elemen. Select minimum tinggi 48px.

`prefers-reduced-motion: reduce` mematikan transisi/animasi dan menetapkan scroll auto; transform tombol hero dinonaktifkan. Pada cetak, header, banner dummy, footer dan filter disembunyikan; hero/intro memakai warna kertas/tinta, dan jadwal tersembunyi oleh filter ditampilkan kembali. Penyembunyian banner pada cetak bukan izin menghapus label contoh pada konten.

## Elevation & Depth

CSS publik tidak mendefinisikan bayangan dekoratif. Hierarki dibentuk melalui warna permukaan, jarak, bobot teks, dan susunan editorial. Panel jadwal/preview profil memakai permukaan pucat; arsip memakai baris berselang. Jangan mengubahnya menjadi kartu berbayang sebagai penerapan otomatis sistem ini.

### Fokus keyboard dan state

Outline fokus adalah **solid 3px dengan offset 5px**, bukan bayangan. Aturan `:focus-visible` diperkuat dengan selector `.navbar .nav-link`, `.navbar .navbar-toggler`, `.btn`, dan `.form-select` agar reset Bootstrap tidak menghilangkannya; `box-shadow: none !important` menghapus shadow sambil mempertahankan outline.

- Permukaan umum: `purple`; hero terang Beranda: `purple-dark`; footer gelap: `gold`.
- Tautan umum: underline offset .25em, tebal .08em; hover menjadi `purple-dark` dan .14em. Navbar aktif memakai underline 3px/offset .5em dan `aria-current="page"`; status aktif tidak menggantikan fokus keyboard.
- Tombol emas mempertahankan warna pada hover/focus dan bergeser `translateY(-2px)`; `.btn:active` bergeser 1px dengan transisi transform `.15s ease`. Outline tetap menjadi penanda saat gerak berkurang.

## Shapes

Tombol, toggler, dan select bersudut siku (`border-radius: 0`). Toggler/select memakai border ungu 2px, tombol emas tanpa border. Foto hero berbentuk persegi panjang alami tanpa crop, mask, atau radius khusus. Tidak ada sistem chip, modal, atau token status error/sukses publik yang dapat dianggap sudah diterapkan.

## Components

### Hero dan pengantar halaman

Beranda menempatkan copy dan CTA di kiri, foto di kanan, di atas latar kertas. Foto `Gereja-tampak-depan.jpeg` memiliki atribut 1600×1200, rasio 4:3; CSS `width: 100%; height: auto` mempertahankan keseluruhan foto. Ada alt deskriptif, `fetchpriority="high"`, dan caption 1rem muted rata kanan. Jangan memperlakukan foto sebagai background yang boleh terpotong tanpa keputusan baru. Intro Profil/Berita/Jadwal memakai bidang ungu, h1 editorial dan teks kertas, tanpa foto hero.

### Navbar

Navigasi bersama menautkan empat halaman dan memiliki `aria-label="Navigasi utama"`. Brand DM Sans 700 dengan sublabel “Maluku” 400. Padding navbar 1.1rem vertikal; gap link 1.2rem dengan wrap. Link normal ink, padding `.65rem .25rem`; aktif ungu/underline. Di HP gap .25rem, padding atas daftar 1rem, padding-block link .75rem. Tombol teks “Menu” mengontrol collapse dengan `aria-controls`, `aria-expanded` dan label buka/tutup. Tanpa JS, `noscript` menampilkan daftar tautan dan menyembunyikan toggler.

### CTA dan tautan

CTA emas “Lihat jadwal ibadah” menuju halaman jadwal; tautan “Berita jemaat” menuju `#warta`. Tombol memakai padding `.85rem 1.35rem`, DM Sans 700/1.125rem, radius 0. Khusus hero, flex-wrap dan `white-space: normal` mengizinkan label panjang; panah dekoratif `aria-hidden="true"`. Tautan sekunder tetap berupa tautan. CTA hero minimum tinggi 44px; penumpukan HP kecil mengikuti Layout.

### Berita dan keadaan kosong

Beranda menampilkan paling banyak dua berita: tiap item satu kolom, gap .5rem, padding vertikal 1.5rem, tanpa latar berselang/padding horizontal. Judul DM Sans 600 menautkan anchor artikel pada halaman berita; label Dummy, kategori, tanggal contoh, dan ringkasan tetap terlihat. Jika data kosong, Beranda menampilkan “Belum ada berita yang tersedia.”

Arsip berita memakai baris dua kolom dan `surface` pada `nth-of-type(even)`; judul/metadata di kiri, ringkasan dan `details` di kanan. `summary` “Baca isi contoh” memakai underline, padding-block .5rem, min-height 44px, margin bawah .75rem saat terbuka. Detail native tersedia tanpa JS; ini bukan route artikel terpisah atau editor konten. View arsip saat ini hanya mengiterasi data; pesan kosong khusus Beranda tidak berarti arsip sudah memiliki empty state setara.

### Jadwal dan filter

Panel Beranda memakai `surface`, padding `clamp(1.25rem, 2.5vw, 2.5rem)`, gap 1.5rem (HP 1.25rem). Sorotan berasal dari item pertama data contoh, bukan logika waktu mendatang. Jika kosong, tampil “Belum ada jadwal ibadah yang tersedia.”

Daftar jadwal memisahkan nama/tanggal, waktu WIT, dan lokasi/keterangan; baris ganjil memakai `surface`. Tanggal menggunakan `time`. Select native memiliki label terhubung, font 1.125rem, border ungu 2px, radius 0, min-width 15rem dan min-height 48px; lebar minimum dilepas di HP. JS menampilkan filter dan mengubah atribut `hidden` baris menurut jenis. Jumlah hasil diumumkan melalui `role="status" aria-live="polite"`. Tanpa JS filter tersembunyi dan semua jadwal tersedia. Jumlah awal pada view masih teks tetap “3 jadwal contoh ditampilkan.”; jangan menganggap semua kondisi data dinamis sudah ditangani.

### Profil

Bagian Tentang, Sejarah, Majelis, dan Lokasi & kontak memakai grid judul/isi `1fr 1.5fr`, menjadi satu kolom di HP. Sejarah dan kontak memakai `tone-surface`; kontak berupa `dl/dt/dd` dengan margin bawah dd 1.2rem. Placeholder menegaskan data belum dikonfirmasi. Belum ada kartu pengurus, statistik resmi, bagan organisasi, atau nomor kontak resmi sebagai komponen publik aktif.

### Footer

Footer bersama berlatar `purple-dark`, teks/tautan `paper`, padding-block 2.5rem. Bagian atas berisi kalimat pengantar dan tautan jadwal; nama besar menggunakan **DM Sans 600**, bukan heading editorial serif. Baris bawah menyebut konten dummy, kontak/alamat menunggu konfirmasi, dan tautan ke `#konten`. Baris atas/bawah flex-wrap, gap 1.5rem, berubah vertikal di HP kecil. Tautan min 44×44px, fokus emas; wrapping dan `min-width: 0` melindungi layout saat teks diperbesar.

### Aksesibilitas bersama dan bukti verifikasi

Layout memakai `lang="id"`, landmark nav/main/footer, skip link “Lewati ke konten” menuju `main#konten` dengan `tabindex="-1"`, dan satu h1 per halaman. Skip link berada di atas viewport saat normal, tampil pada `:focus` di top 1rem, z-index 10, padding 1rem. Pertahankan heading berurutan, label kontrol, alt foto, status filter, akses konten inti tanpa JS, fokus kontras, dan wrapping saat teks diperbesar.

[TASK-006E](docs/TASK-006E-TYPOGRAPHY.md) mencatat pemuatan font/glyph dan fallback WOFF2 yang diblokir pada HP. [TASK-006F](docs/TASK-006F-AUDIT.md) menemukan konflik fokus Bootstrap, target bersama, dan WIT tablet. [TASK-006G](docs/TASK-006G-HARDEN.md) mencatat ketiganya diperbaiki dan lulus pemeriksaan Chrome lokal pada empat halaman × 375/768/1440px, termasuk reduced-motion dan simulasi root teks 200%, interaksi serta fallback tanpa JS. Hasil ini **bukti historis**, bukan pengujian ulang TASK-006H atau klaim kepatuhan WCAG penuh.

Masih perlu divalidasi: persetujuan mitra atas identitas/konten dan izin foto; lebar tambahan PRD 320/360/390/430px; perangkat sentuh fisik dan browser selain Chrome; pembaca layar; zoom browser 400%; teks resmi panjang, karakter di luar subset Latin, variasi data kosong/panjang; jaringan lambat dan CLS/Core Web Vitals; UAT warga serta Media Center. Simulasi root teks 200% tidak sama dengan zoom browser/perangkat nyata.

## Do's and Don'ts

### Aturan konsistensi untuk pengembangan admin

Ini panduan penerapan berikutnya, **bukan klaim bahwa admin saat ini telah memakai seluruh sistem desain publik**. Dokumentasi dan verifikasi di atas hanya mencakup empat halaman publik; admin tidak diaudit atau diubah dalam TASK-006H.

- **Do** gunakan DM Sans 400/600/700 untuk tabel, formulir, navigasi dan tindakan; Cormorant Garamond 600 untuk heading editorial yang sesuai, tanpa memaksakan skala hero pada layar kerja.
- **Do** gunakan nilai palet bersama untuk makna yang sama, sudut siku, label Bahasa Indonesia yang jelas, outline 3px/offset 5px yang tahan override Bootstrap, dan target sentuh nyaman.
- **Do** sesuaikan kepadatan/layout admin dengan tugas operator. Pertahankan hirarki dan keterbacaan; grid hero/foto/footer besar publik tidak perlu disalin ke dashboard.
- **Do** rancang serta uji validasi, error, sukses, disabled, tabel dan dialog saat fitur admin dikembangkan; catat token tambahannya sebagai keputusan baru, bukan hasil ekstraksi komponen publik.

### Batas sistem dan konten

- **Do** rujuk token CSS aktual dan pengecualian font item/footer; periksa responsivitas, fallback font/JS, serta fokus keyboard saat mengembangkan komponen berikutnya.
- **Don't** mengembalikan Georgia/Arial sebagai font utama, memakai token warna/ukuran historis yang bertentangan dengan kode, atau mengganti palet demi skor detector generik.
- **Don't** menganggap arah editorial sebagai identitas resmi yang telah disahkan, mengarang data jemaat, atau menghapus banner dummy, label contoh, dan `noindex, nofollow` selama pengembangan.
- **Don't** menyamakan penerapan publik dengan kesiapan admin, aksesibilitas penuh, izin publikasi, atau persetujuan seluruh PRD.
