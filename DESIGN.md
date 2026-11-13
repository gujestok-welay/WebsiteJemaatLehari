---
name: Website Informasi Jemaat Leahari
description: Identitas publik yang hangat dan mudah dibaca, memakai palet ungu-gading, tipografi Cormorant Garamond dan DM Sans, serta interaksi responsif.
colors:
  bg-primary: "#FFFEFA"
  bg-secondary: "#F5F1F6"
  bg-tertiary: "#EEE2BD"
  card-bg: "#FFFEFA"
  brand-purple: "#663F72"
  brand-purple-dark: "#482950"
  brand-purple-hover: "#482950"
  brand-gold: "#D8BD72"
  text-main: "#29232C"
  text-muted: "#5D5361"
typography:
  display:
    fontFamily: "'Cormorant Garamond', serif"
    fontWeight: 600
    letterSpacing: "-.02em"
  body:
    fontFamily: "'DM Sans', sans-serif"
    fontWeight: 400, 600, 700
    lineHeight: 1.65
rounded:
  control: "0.5rem"
  card: "1rem"
  panel: "1.6rem"
  pill: "9999px"
---

# Design System: Website Informasi Jemaat Leahari

## Overview

Antarmuka publik memakai komposisi editorial yang mengutamakan identitas jemaat, berita, dan jadwal ibadah. Implementasi memakai PHP Views, Bootstrap 5, dan CSS khusus. Panduan ini mencatat token serta perilaku yang saat ini diterapkan di `public/assets/css/publik.css`; warna palet adalah pilihan desain proyek, bukan identitas visual yang telah disahkan mitra.

## 1. Warna

- **Latar utama:** `#FFFEFA`, gading lembut.
- **Permukaan sekunder:** `#F5F1F6`; aksen permukaan hangat `#EEE2BD`.
- **Aksen utama:** ungu `#663F72`; ungu gelap `#482950` untuk tautan dan hover.
- **Aksen emas:** `#D8BD72`.
- **Teks utama:** `#29232C`; teks sekunder `#5D5361`.

Gunakan warna melalui token CSS pada `:root` agar elemen publik tetap konsisten. Status konten contoh menggunakan label berteks “Contoh”, bukan warna saja.

## 2. Tipografi

- **Cormorant Garamond, 600:** judul halaman dan bagian.
- **DM Sans, 400/600/700:** isi, metadata, tombol, dan antarmuka.
- Ukuran teks isi dasar 18 px dengan line-height 1.65. Ukuran heading responsif menggunakan `clamp()`.
- Font disimpan lokal dan menggunakan `font-display: swap`.

## 3. Bentuk, permukaan, dan hierarki

- Kartu konten umumnya menggunakan radius 1rem; panel hero menggunakan radius 1.6rem. Kontrol kecil dapat memakai sudut 0.5rem, sementara pil digunakan untuk chip kecil.
- Header publik sticky dengan latar gading semi-transparan, blur 10 px, garis bawah, dan bayangan lembut.
- Bayangan membantu membedakan panel dan kartu dari latar; gunakan secara hemat.
- Susun konten dengan grid Bootstrap dan urutan yang mendahulukan berita serta jadwal setelah hero. Di ponsel, daftar menjadi satu kolom tanpa overflow horizontal.

## 4. Interaksi dan aksesibilitas

- Kartu berita dan panel hero memiliki gerak hover skala hingga 1.05. Gerak otomatis carousel Majelis menyediakan tombol jeda/navigasi dan menghormati `prefers-reduced-motion`.
- Semua kontrol keyboard mempertahankan indikator fokus yang terlihat: outline ungu 3 px dengan offset.
- Navigasi menggunakan offcanvas Bootstrap pada layar kecil; skip link mengarahkan langsung ke konten utama.
- Konten berita contoh menampilkan badge “Contoh” pada setiap kartu.
- Jadwal beranda hanya menampilkan waktu ibadah yang belum dimulai menurut WIT; jika tidak ada, tampilkan empty state.

## 5. Komponen

- **Navbar:** logo, navigasi dengan halaman aktif, dan menu offcanvas responsif.
- **Hero beranda:** identitas Jemaat Leahari, tautan langsung ke jadwal dan berita, serta panel jadwal terdekat.
- **Kartu berita:** kategori, tanggal, status contoh bila relevan, judul, dan ringkasan.
- **Jadwal:** tanggal, jenis ibadah, waktu WIT, lokasi, dan pelayan bila tersedia.
- **Carousel Majelis:** hanya ditampilkan setelah konten anggota diaktifkan untuk publikasi; menyediakan kontrol untuk jeda dan navigasi.

Konten, nama personel, tanggal, statistik, dan materi visual resmi harus berasal dari informasi yang telah disahkan atau diizinkan. Jangan mengisi kekosongan dengan klaim atau identitas rekaan.

## 6. Halaman Kelola admin Profil

- Setiap tindakan Kelola dari halaman Profil membuka halaman editor tersendiri, dengan navigasi kembali yang terlihat.
- Editor berbagian panjang (Wilayah, Sensus, Konten Profil, dan Pengelolaan Majelis) memakai langkah yang dapat dipilih langsung, satu kelompok isian pada satu waktu, serta tombol Sebelumnya/Berikutnya. Pengelolaan Majelis terdiri dari periode, formulir anggota, dan daftar anggota; editor ringkas tetap tampil utuh.
- Editor mengisi seluruh lebar konten admin yang tersedia tanpa bingkai kartu; judul, langkah, isian, dan aksi simpan berada dalam satu bidang kerja.
- Setelah simpan, editor tetap berada di halaman modul dan menampilkan status berhasil. Navigasi keluar memperingatkan bila ada perubahan yang belum disimpan.
- Pada layar sempit, panel editor memakai padding yang lebih rapat dan tetap mengikuti alur gulir halaman.

## 7. Tema tampilan

- Halaman publik, admin, login, dan halaman pengguna menyediakan sakelar tema **Terang/Gelap**. Sebelum pengguna memilih, tema mengikuti preferensi sistem; setelah sakelar digunakan, pilihannya disimpan pada browser dan dibagikan antarhalaman pada origin yang sama.
- Tema gelap memakai latar utama `#211D25`, permukaan `#2B2530`, teks `#F4EDF5`, teks sekunder `#C7BACD`, serta aksen ungu muda `#E4C9EF`. Aksen tombol ungu tetap gelap agar label tombol putih terbaca; emas disesuaikan ke `#E2C97F`.
- Kartu galeri memakai permukaan terangkat `#302936` dan garis tepi `#51465A` agar bidang kontennya tidak menyatu dengan latar halaman gelap; tanggal detail galeri memakai teks gelap di atas heading ungu muda.
- Komponen sakelar bersama berada di `app/Views/components/theme-toggle.php`; warna dan geraknya diterapkan melalui `public/assets/css/theme.css`. Perilaku, penyimpanan, dan sinkronisasi preferensi sistem ditangani `public/assets/js/theme.js`.
- Sakelar memakai semantik switch, label aksesibel, indikator fokus, dan gerak yang menghormati `prefers-reduced-motion`. Tema hanya mengubah presentasi; konten, autentikasi, navigasi, dan alur kerja tetap sama.
