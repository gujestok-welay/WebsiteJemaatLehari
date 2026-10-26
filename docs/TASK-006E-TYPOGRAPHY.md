# TASK-006E — Catatan implementasi dan audit tipografi

Tanggal: 26 Oktober 2026. Lingkup: empat halaman publik, perubahan tipografi saja.
Token berikut merupakan implementasi untuk diaudit/disahkan; **DESIGN.md tidak diubah**.
Keterangan Georgia/Arial dalam DESIGN.md kini bersifat historis untuk halaman publik.
PRODUCT.md juga memiliki keterangan implementasi lama; dokumen konteks tidak diperbarui
sebagai efek samping task ini. Status draft dan keputusan terbuka PRD tetap berlaku.

## Inventaris sebelum perubahan

- Empat route memakai `app/Views/layouts/publik.php`, Bootstrap lokal, dan satu stylesheet
  `public/assets/css/publik.css`; tidak ditemukan CSS inline pada view publik aktif.
- Token lama `--display-font` = Georgia/Times New Roman/serif dan `--body-font` =
  Arial/Helvetica/sans-serif. Heading h1–h3 dan `.footer-name` sebelumnya serif 400.
- Ukuran tersebar pada aturan heading umum, `.home-hero h1`, `.home-news`,
  `.home-schedule`, `.footer-name`, serta media query HP/tablet.
- CSS halaman error/welcome bawaan dan CSS inline admin tidak dipakai oleh layout
  publik dan tidak diubah. Bootstrap vendored juga tidak diubah.

## Sistem peran

Cormorant Garamond **600** untuk judul hero, judul halaman, section dan heading editorial.
DM Sans **400** untuk paragraf/metadata; **600** untuk judul item berita/jadwal dan nama
footer; **700** untuk navbar brand, tombol, dan penekanan. Navbar, kontrol native,
label, kartu/baris berita, waktu jadwal, dan seluruh footer memakai DM Sans.
Judul item adalah pengecualian eksplisit dari heading serif umum, sesuai keputusan task.

Judul HP minimum 48px (h1/hero) dan 32px (section), bobot 600; item UI 24px.
Tracking judul dilonggarkan dari -.04/-.035em menjadi -.02em, leading hero dari 1.02
menjadi 1.1. Isi tetap 18px/1.65, metadata 16px, measure isi maksimum 65ch.
Ukuran footer disesuaikan terhadap metrik DM Sans agar masthead tetap muat.
Grid menggunakan batas minimum nol dan pembungkusan teks; navigasi dan header filter
dapat membungkus ketika teks diperbesar. Komposisi kolom normal tetap dipertahankan.

## Token CSS untuk pengesahan setelah audit

Sumber nilai: `:root` pada `public/assets/css/publik.css`.

| Token | Nilai | Peran |
| --- | --- | --- |
| `--display-font` | `'Cormorant Garamond', Georgia, 'Times New Roman', serif` | Judul editorial |
| `--body-font` | `'DM Sans', Arial, Helvetica, sans-serif` | Isi dan UI |
| `--font-weight-body` | `400` | Isi |
| `--font-weight-heading` | `600` | Judul serif |
| `--font-weight-ui` | `600` | Item UI/footer |
| `--font-weight-strong` | `700` | Penekanan |
| `--font-size-body` | `1.125rem` | Isi |
| `--font-size-meta` | `1rem` | Metadata |
| `--font-size-page` | `clamp(3rem, 6.5vw, 6.5rem)` | Judul halaman |
| `--font-size-hero` | `clamp(3.25rem, 6.5vw, 6rem)` | Hero desktop |
| `--font-size-section` | `clamp(2rem, 3vw, 2.5rem)` | Section/editorial |
| `--font-size-item` | `clamp(1.5rem, 2.2vw, 2rem)` | Item berita/jadwal |
| `--font-size-footer` | `clamp(2.5rem, 8vw, 8rem)` | Nama footer |
| `--font-leading-body` | `1.65` | Isi |
| `--font-leading-display` | `1.1` | Hero/judul halaman |
| `--font-leading-heading` | `1.2` | Heading section/footer |
| `--font-leading-ui` | `1.35` | Judul item UI |
| `--font-tracking-display` | `-.02em` | Judul besar/footer |
| `--font-tracking-heading` | `-.015em` | Section |
| `--font-tracking-ui` | `0` | Item UI |
| `--font-measure-body` | `65ch` | Batas lebar paragraf |

Hero tetap memiliki penyesuaian breakpoint yang diwarisi: HP
`clamp(3rem, 9vw, 3.5rem)`; tablet 768–1023.98px `clamp(3rem, 6.5vw, 4.25rem)`.
Ukuran lead, waktu jadwal, navbar, dan kontrol mengikuti peran komponen yang ada.

## Pengiriman font

Empat WOFF2 Latin statis, total **66.088 byte**, Fontsource versi tetap 5.3.0.
Asal unduhan, ukuran, hash, copyright dan dua lisensi SIL OFL 1.1 dicatat dalam
`public/assets/fonts/README.md`. Tidak menambah package/dependensi frontend.
`@font-face` lokal memakai `font-display: swap`, fallback serif/sans sejenis,
dan preload dua font utama di layout. Tidak ada kebutuhan koneksi Google Fonts/CDN
saat runtime. Internet hanya dipakai untuk mengambil aset saat pengembangan.

## Hasil pengujian nyata

Chrome headless dengan CDP terhadap `http://localhost:8080`, langsung pada view/CSS
yang dilayani CodeIgniter. Proyek ini tidak memiliki tahap bundling CSS frontend.
Pemeriksaan awal browser dalam sandbox gagal dengan timeout `Page.enable`; pengujian
berhasil setelah dijalankan di luar sandbox dengan izin.

| Halaman | 375px | 768px | 1440px |
| --- | --- | --- | --- |
| Beranda | Lulus | Lulus | Lulus |
| Profil | Lulus | Lulus | Lulus |
| Berita | Lulus | Lulus | Lulus |
| Jadwal Ibadah | Lulus | Lulus | Lulus |

Kriteria tabel: tidak ada overflow horizontal, clipping terdeteksi, aset HTTP gagal,
error JavaScript, atau permintaan aset eksternal. Seluruh font berstatus `loaded`.
CDP `CSS.getPlatformFontsForNode` mengonfirmasi glyph judul memakai font web Cormorant,
sedangkan paragraf, item berita/jadwal dan nama footer memakai font web DM Sans.
Screenshot penuh keempat halaman pada semua ukuran telah dibuka dan ditinjau;
tidak terlihat tabrakan atau potongan teks. Foto gereja tetap utuh dengan rasio aslinya.

- Menu HP membuka dan menampilkan seluruh link di empat route tanpa overflow.
- Ketiga detail berita dapat dibuka dan ditutup pada ketiga ukuran.
- Filter jadwal Minggu/Keluarga/Semua memberi 2/1/3 hasil serta status yang sesuai,
  pada ketiga ukuran.
- Pembesaran teks root 200%: seluruh 12 kombinasi bebas overflow/clipping. Ini uji
  pembesaran teks, bukan klaim uji zoom browser/perangkat fisik.
- WOFF2 diblokir melalui CDP: empat route pada 375px tetap terbaca dan bebas overflow
  dengan fallback. Ini simulasi kegagalan font, bukan mode offline seluruh website.
- JavaScript dinonaktifkan: navigasi dan tiga jadwal tetap tersedia pada HP.
- `php -l app/Views/layouts/publik.php`: lulus.
- `lint.mjs public/assets/css/publik.css app/Views/layouts/publik.php`: 0 FAIL, 0 WARN
  (lint skill memindai satu file CSS yang didukung).
- `git diff --check` pada CSS dan layout: lulus.
- **PHPUnit tidak dijalankan**: `vendor/bin/phpunit` tidak tersedia. Tidak menginstal
  dependensi atau menjalankan tes database, migration, autentikasi maupun admin.
- Tidak melakukan pengujian pembaca layar, perangkat fisik, CLS lab, atau browser selain Chrome.

Artefak lokal (diabaikan Git): `.taste-skill/task-006e-before/`,
`.taste-skill/task-006e-after/checks.json`, 12 screenshot akhir dan skrip
`.taste-skill/task-006e-check.mjs` (argumen `before`/`after`).

## Catatan skill dan batas audit

Impeccable `context` dicoba sekali; engine 0.1.5 belum terpasang dan cache di luar
workspace tidak dapat ditulis. Panduan `typeset` dan `craft-floor` dibaca dan diterapkan
manual. Tidak mengganti atau membuat ulang DESIGN.md. Tidak menggambar direction baru
karena pasangan font dan komposisi sudah ditetapkan pengguna.

Inspector taste dijalankan tambahan pada Beranda di 1440/768/375 dengan izin
`palette-purple,still-page`, mempertahankan warna dan perilaku yang ada. Hasilnya
**2 FAIL, 2 WARN**, bukan kelulusan penuh skill:

- FAIL `few-pictures`: hanya satu foto. Menambah foto/layout di luar lingkup task.
- FAIL `eyebrow`: metadata dummy tanggal di atas angka waktu pada HP/tablet dianggap
  kicker oleh heuristik. Tanggal/keterangan dummy dipertahankan sebagai informasi.
- WARN `palette-cream`: latar `#fffefa` yang sudah ada wajib dipertahankan.
- WARN `no-footage`: tidak menambah video untuk task tipografi.

Tidak dilaporkan kegagalan font, overflow, kontras atau headline lebih dari dua baris.
Enam screenshot fold/lembar inspector telah ditinjau; laporan tersedia di
`.taste-skill/task-006e-inspect-beranda/report.json`. Halaman lain diuji dengan matriks
CDP di atas, bukan diklaim telah menjalani inspector skill penuh.

ROUND 1
Seen in one second: nama Jemaat Leahari dan foto gereja pada Beranda.
Idea (one sentence): judul Cormorant mempertahankan warta editorial; DM Sans memperjelas isi dan tindakan.
Live thing: filter jadwal yang telah ada, dengan fallback daftar lengkap.
Layout words by section: hero=split foto / berita=baris editorial / jadwal=bidang informasi / profil=dua kolom / footer=masthead.
Scores: design 6.5 · usability 8 · creativity 6 · content 5 → overall 6.0.
Caps applied: FAIL inspector membatasi overall 6; jumlah foto membatasi design 6.5; konten masih dummy.
Top three problems (most visible first), each with the screenshot it shows in and the fix:
1. Identitas/footer sebelumnya Georgia kini DM Sans sesuai brief (`beranda-1440.jpg`); ukuran dituning agar tidak melampaui frame.
2. Item berita/jadwal sebelumnya serif bersaing dengan heading section (`berita-375.jpg`, `jadwal-ibadah-768.jpg`); dipisahkan menjadi DM Sans 600, leading 1.35.
3. Foto/konten resmi terbatas (semua screenshot); kebutuhan konten tetap untuk tahap tersendiri setelah validasi, tidak diisi dengan fakta rekaan.

Warna, foto, teks, struktur editorial, link dan perilaku publik dipertahankan. Tidak
ada perubahan backend, autentikasi, database, migration atau fitur admin dalam task ini.
