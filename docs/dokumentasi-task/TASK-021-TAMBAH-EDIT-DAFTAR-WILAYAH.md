# TASK-021 — Menambah dan mengubah daftar wilayah

## Tujuan

Memudahkan Media Center meninjau dan mengubah item daftar wilayah tanpa menampilkan seluruh kolom sekaligus. Nilai data dapat disunting; redaksi informasi publik tidak diubah oleh task ini.

## Perubahan

- Item batas wilayah, sektor pelayanan, jarak ke lokasi penting, dan musim ditampilkan sebagai ringkasan.
- Tombol **Ubah** membuka kolom item; tombol **Selesai mengubah** menutupnya kembali.
- Ringkasan mengikuti perubahan arah/nama batas, nama sektor, nama/jarak lokasi, serta periode/nama musim.
- Tombol tambah yang sudah ada membuka item baru agar dapat langsung diisi. Hapus item tetap tersedia.
- Tidak ada perubahan skema database atau penambahan konten resmi Jemaat.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Views/admin/profil/_content-fields.php` | Ringkasan, panel edit, dan template item baru untuk empat daftar. |
| `app/Views/admin/profil/_module-editors.php` | Mengelompokkan daftar wilayah dalam langkah editor yang sesuai. |
| `public/assets/js/admin.js` | Buka/tutup panel, fokus input, dan pembaruan ringkasan. |
| `public/assets/css/admin.css` | Menjamin panel beratribut `hidden` tidak ditampilkan. |
| `tests/unit/PublicProfileContentIntegrationTest.php` | Memastikan empat daftar merender ringkasan dan panel edit. |
| `e2e/profile-modules.spec.js` | Menguji edit, ringkasan, tambah/hapus item, dan penyimpanan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 198 tes, 1.227 assertion. |
| Playwright `profile-modules.spec.js` | PASS — 29 tes. |
| Pemeriksaan JavaScript (`node --check`) dan PHP (`php -l`) | PASS. |
| `git diff --check` | PASS. |
