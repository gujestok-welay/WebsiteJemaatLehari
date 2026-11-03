# TASK-007 — Dashboard Admin Media Center

Tanggal: 3 November 2026.

## Tujuan

Mengimplementasikan dashboard admin responsif berdasarkan `DESIGN.md` dan bahan rancangan yang tersedia. PRD baseline tetap diperlakukan sesuai statusnya; tidak ada fakta resmi Jemaat yang ditambahkan atau dikarang.

## Perubahan

- Mengganti halaman placeholder dengan dashboard berbahasa Indonesia: salam admin, pintasan berita/jadwal, jumlah berita terbit dan draf, jadwal mendatang, daftar berita terbaru, serta jadwal berikutnya.
- Ringkasan berita dan jadwal dibaca dari tabel aplikasi. Kondisi database kosong ditampilkan sebagai empty state, bukan diisi konten contoh.
- Menambahkan layout admin dan navigasi samping yang dapat dibuka di layar kecil. Gaya memakai palet ungu/krem, DM Sans, Cormorant Garamond, fokus keyboard, skip link, dan dukungan reduced motion.
- Mendaftarkan route untuk modul berita, jadwal, profil/Majelis, dan statistik di bawah filter `adminAuth`; semua aksi POST tetap dilindungi CSRF.
- Memperbarui PRODUCT.md agar status dashboard/modul admin sesuai implementasi.
- Tidak mengubah skema/database, akun/kredensial, empat halaman publik, atau data konten.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Config/Routes.php` | Route admin yang tersedia dikelompokkan di bawah `adminAuth`; POST memakai CSRF. |
| `app/Controllers/AdminAuth.php` | Menyediakan agregat database dan ringkasan tanggal WIT untuk dashboard. |
| `app/Views/admin/dashboard.php` | Dashboard operasional dengan data aktual dan empty state. |
| `app/Views/admin/partials/sidebar.php` | Navigasi responsif ke modul yang sudah tersedia. |
| `app/Views/layouts/admin.php` | Shell halaman admin, topbar, tombol logout CSRF, dan aset lokal. |
| `public/assets/css/admin.css` | Gaya dashboard responsif dan aksesibel sesuai DESIGN.md. |
| `public/assets/js/admin.js` | Interaksi menu samping pada layar kecil. |
| `tests/auth/admin-entry-http.mjs` | Regresi akses anonim/admin ke modul yang ditautkan. |
| `PRODUCT.md` | Memperbarui status implementasi aktual. |
| `docs/dokumentasi-task/TASK-007-ADMIN-DASHBOARD.md` | Catatan task ini. |

## Pengujian

| Pemeriksaan | Status | Hasil |
| --- | --- | --- |
| `node tests/auth/admin-entry-http.mjs` | PASS | Halaman admin ditolak untuk sesi anonim; dashboard dan empat modul tersedia bagi sesi sintetis; logout, CSRF, replay sesi, dan empat halaman publik tetap lulus. |
| `php spark routes` | PASS | Route modul terdaftar dengan filter `adminAuth`; aksi POST juga menunjukkan filter CSRF. |
| `php -l` | PASS | Controller, Routes, layout, sidebar, dan view tanpa kesalahan sintaks. |
| `node --check` | PASS | JavaScript menu dan runner regresi valid. |
| `git diff --check` | PASS | Tidak ada kesalahan whitespace. |
| Browser 1440, 390, dan 320 px | PASS | Tampilan diperiksa; tidak ada overflow horizontal; menu mobile membuka/menutup dengan status ARIA dan mengunci scroll latar. |
| Impeccable detector | RUN; warning dikoreksi | Detektor menemukan garis aksen 3 px yang dihapus setelah pemeriksaan. Radius diselaraskan ke skala DESIGN.md; advisory warna tonal status/permukaan tetap sebagai turunan untuk kontras. Detektor dijalankan satu kali. |
| PHPUnit | NOT RUN | Regresi HTTP terarah dijalankan; suite PHPUnit tidak dijalankan. |

Pemeriksaan browser memakai sesi admin sintetis lokal untuk pratinjau; tidak membuat akun atau mengubah database. Endpoint dan file sesi sementara untuk pratinjau telah dihapus setelah verifikasi.

## Batasan

Konten publik tetap bersumber dari data contoh dan harus diverifikasi sebelum rilis. Pengelolaan pengguna admin tidak ditambahkan pada navigasi task ini. Status database/deployment selain lingkungan lokal yang diuji tidak diverifikasi.
