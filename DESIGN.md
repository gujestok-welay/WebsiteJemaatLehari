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
