---
name: Website Informasi Jemaat Leahari - Revamp Dinamis
description: Sistem desain estetik dan dinamis. Menggabungkan gaya modern (glassmorphism, soft shadows, fluid hover) dengan pondasi Bootstrap 5.
colors:
  bg-primary: "#FBF8F3"
  bg-secondary: "#F3ECDF"
  bg-tertiary: "#E7DECF"
  card-bg: "#FFFFFF"
  brand-purple: "#4B2A6B"
  brand-purple-dark: "#2E1A45"
  brand-purple-hover: "#3C2157"
  brand-gold: "#d8bd72"
  text-main: "#241B2E"
  text-muted: "#6B6275"
  text-light: "#8A7F94"
typography:
  display:
    fontFamily: "'Cormorant Garamond', serif"
    fontWeight: 500, 600
    letterSpacing: "-.02em"
  body:
    fontFamily: "'DM Sans', sans-serif"
    fontWeight: 400, 500
    lineHeight: 1.65
rounded:
  md: "0.5rem" # 8px
  lg: "0.75rem" # 12px
  xl: "1rem" # 16px
  2xl: "1.5rem" # 24px
  pill: "9999px"
---

# Design System: Website Informasi Jemaat Leahari (Dinamis & Estetik)

## Overview

Dokumen ini adalah panduan desain utama yang **telah dirombak total** untuk memprioritaskan estetika, fluiditas, dan pengalaman antarmuka yang dinamis. Desain referensi mengusung gaya modern dengan sudut membulat yang lembut (_rounded-2xl_), efek kaca (_backdrop-blur_), dan interaksi _hover_ yang hidup.

**Tumpukan Teknologi:** Implementasi wajib menggunakan **CodeIgniter 4, MySQL, dan Bootstrap 5**. Pendekatan _utility-first_ (seperti Tailwind pada desain awal referensi) harus diadaptasi menggunakan _Bootstrap 5 Utility API_ atau penambahan _Custom CSS_ yang efisien.

## 1. Warna & Palet (Kaya & Elegan)

Desain membuang skema warna datar dan menggunakan hierarki warna yang memberi kontras lembut dan elegan.

- **Background:** Menggunakan `#FBF8F3` (krem sangat terang) untuk memberi kesan hangat, dipadukan dengan `#F3ECDF` untuk _section_ sorotan.
- **Aksen Utama:** `#4B2A6B` (Ungu Leahari) digunakan untuk elemen interaktif, tombol utama, dan inisial avatar.
- **Garis & Pembatas (Borders):** Menggunakan `#E7DECF` yang sangat tipis untuk memisahkan kartu konten tanpa bayangan yang berat.
- **Tipografi Kontras:** `#241B2E` untuk _heading_ (sangat gelap) dan `#6B6275` untuk teks paragraf/deskripsi agar tidak melelahkan mata.

## 2. Tipografi

- **Cormorant Garamond (Serif):** Eksklusif untuk _Heading_ (H1-H4) dan angka penunjuk waktu/tanggal yang besar. Menggunakan _tracking_ rapat (`letter-spacing: -0.02em`) dan _line-height_ padat (`0.95` - `1.1`) untuk kesan premium.
- **DM Sans (Sans-serif):** Digunakan untuk seluruh teks tubuh, paragraf, tombol, metadata, dan antarmuka _Dashboard Admin_. Memberi kesan bersih dan geometris.

## 3. Bentuk, Elevasi & _Micro-Interactions_ (Kebebasan Animasi)

Aturan lama yang melarang animasi dan membatasi bayangan **dihapus sepenuhnya**. Interaksi kini wajib terasa hidup:

- **Bentuk (Border Radius):** Kartu berita, panel jadwal, dan gambar wajib menggunakan lengkungan besar (`border-radius: 1rem` hingga `1.5rem` / `xl` dan `2xl` di Bootstrap).
- **Hover Cards (Transisi Mulus):** Kartu konten (berita, profil, jadwal) wajib memiliki efek transisi halus. Saat di-_hover_, kartu akan sedikit membesar (`transform: scale(1.02)`), garis batas (`border-color`) berubah menjadi lebih gelap (`#CFC2AE`), dan memancarkan _soft shadow_ untuk mengangkat elemen dari latar belakang.
- **Glassmorphism (Efek Kaca):** _Header/Navbar_ utama menggunakan latar belakang transparan (`bg-color` dengan opasitas 90%) dikombinasikan dengan efek _backdrop-filter: blur(8px)_ agar terlihat menyatu dengan konten yang di-scroll di bawahnya.
- **Image Reveal:** Gambar _thumbnail_ memiliki efek _zoom-in_ yang mulus di dalam kontainernya saat kartu di-_hover_.

## 4. Adaptasi Komponen ke Bootstrap 5

Karena kita menggunakan Bootstrap 5, berikut panduan _mapping_ gayanya:

1.  **Grid & Layout:** Tinggalkan aturan _custom grid_ kaku dari desain lama. Gunakan sistem `row` dan `col-lg-*` dari Bootstrap 5 dengan kelas _gap_ (`gap-3`, `gap-4`) untuk jarak antar elemen.
2.  **Custom Utilities:** Tambahkan kelas utilitas di file `style.css` Anda untuk mengakomodasi kebutuhan estetik:
    - `.backdrop-blur` -> `backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);`
    - `.hover-scale` -> `transition: transform 0.3s ease, border-color 0.3s ease;` (tambahkan `:hover { transform: scale(1.02); }`)
    - `.line-clamp-2` -> (Gunakan trik _webkit-box_ untuk memotong teks berita panjang).
3.  **Aksesibilitas (Tanpa Mengorbankan Estetika):** Elemen interaktif tetap wajib bisa diakses _keyboard_. Ganti _outline_ standar peramban dengan _ring_ estetik: saat elemen difokuskan (`:focus-visible`), berikan bayangan cincin berwarna ungu transparan (`box-shadow: 0 0 0 3px rgba(75, 42, 107, 0.3)`).

## 5. Komponen Inti

- **Navbar Sticky:** Berada di `top-0`, dengan _border-bottom_ tipis, _background_ tembus pandang, dan _backdrop-blur_.
- **Badges/Tags:** Membulat penuh (`rounded-pill`), teks tebal berukuran kecil (`text-xs`), digunakan untuk status ibadah ("Terjadwal", "Perubahan") dan kategori berita.
- **Empty States (Kondisi Kosong):** Tidak lagi menggunakan teks polos yang membosankan. Gunakan kontainer dengan _border-dashed_, warna latar transparan, dipadu dengan ikon _Iconify_ ukuran besar di tengah untuk mempertahankan estetika.
- **Dashboard Admin:** Mewarisi kebersihan UI publik, menggunakan sidebar responsif. Kartu statistik menggunakan _border_ tipis dan radius `xl`.
