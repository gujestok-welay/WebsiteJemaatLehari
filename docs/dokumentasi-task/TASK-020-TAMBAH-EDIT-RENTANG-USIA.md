# TASK-020 — Menambah dan mengubah rentang usia sensus

## Tujuan

Memungkinkan Media Center menyesuaikan kategori rentang usia tanpa membuat teks tampilan lain ikut dapat diedit.

## Perubahan

- Setiap rentang usia memiliki tombol untuk membuka kolom pengubahan label.
- Tombol **Tambah rentang usia** menambahkan baris baru dengan kolom label dan angka laki-laki/perempuan.
- Perubahan label langsung terlihat pada judul baris; label dan angka tetap dikirim sebagai data formulir.
- Label baru wajib diisi; teks tampilan publik lainnya tetap.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Views/admin/profil/_content-fields.php` | Kontrol edit label, template baris baru, dan tombol tambah. |
| `public/assets/js/admin.js` | Perilaku buka/tutup editor label, pembaruan judul, dan fokus ke baris baru. |
| `tests/unit/PublicProfileContentIntegrationTest.php` | Memastikan tombol, field tersembunyi, dan kontrak data. |
| `e2e/profile-modules.spec.js` | Menguji edit dan tambah rentang usia hingga tersimpan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 197 tes, 1.217 assertion. |
| Playwright: alur edit/tambah rentang usia sensus | PASS — 1 tes. |
| Pemeriksaan sintaks JavaScript/PHP | PASS. |
| `git diff --check` | PASS. |
