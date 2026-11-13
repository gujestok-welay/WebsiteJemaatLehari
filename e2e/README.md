# Tes browser Playwright

Playwright adalah alat yang membuka website di Chromium dan menjalankan langkah seperti pengguna: menekan tombol, mengisi formulir, menyimpan, lalu memeriksa hasilnya.

## Menjalankan tes

Dari terminal VS Code di folder proyek, jalankan:

```bash
npm run test:e2e
```

Server tes akan berjalan sendiri di `http://127.0.0.1:8091`. Server berhenti setelah tes selesai. Jika port 8091 sedang dipakai, tes berhenti tanpa mengganti server yang sudah berjalan.

Perintah lain:

```bash
npm run test:e2e:ui
npm run test:e2e -- --headed
npx playwright show-report
npm run test:e2e:clean
```

`--headed` menampilkan jendela browser selama tes. Perintah `show-report` membuka laporan terakhir. Jalankan `test:e2e:clean` setelah server tes berhenti jika ingin menghapus database tes.

## Data dan keamanan

- Server E2E selalu diarahkan ke SQLite khusus di `writable/playwright-test-data/profile-modules.sqlite`; server tidak memakai database dari `.env`.
- Setiap kali tes dimulai, tabel-tabel dalam database khusus tersebut direset dan akun admin tes dibuat ulang.
- Kredensial hanya untuk database lokal tes; jangan gunakan atau salin sebagai akun admin website.
- Sesi browser tes disimpan terpisah di `writable/playwright-session`.
- Jangan ubah konfigurasi agar tes terhubung ke database produksi.

## Cakupan saat ini

Tes memeriksa halaman publik, tombol buka/tutup modul Profil & Majelis, penyimpanan wilayah dan data KK/jiwa, navigasi langkah sensus, keterangan dan validasi file peta, konten profil, data jemaat, periode dan kartu Majelis, serta alur tambah/edit/batal/arsip/pulihkan jadwal. Panel Majelis juga diuji pada lebar ponsel, termasuk scroll, aksesibilitas saat terbuka, dan alur tambah/hapus anggota.

Data yang dimasukkan tes adalah data dummy dan hanya disimpan ke database tes lokal.
