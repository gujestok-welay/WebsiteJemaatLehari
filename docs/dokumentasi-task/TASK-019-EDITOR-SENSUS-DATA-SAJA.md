# TASK-019 — Membatasi editor sensus pada data

## Tujuan

Menyederhanakan tugas admin agar hanya angka sensus yang dapat diubah, sementara teks tampilan dan kelompok usia tetap.

## Perubahan

- Jumlah kepala keluarga serta angka laki-laki dan perempuan tetap dapat diedit.
- Judul, keterangan, label, satuan, dan rentang usia tidak lagi ditampilkan sebagai kolom edit; nilai tersebut tetap dikirim saat formulir disimpan.
- Rentang usia tidak dapat ditambah atau dihapus dari editor.
- Langkah terakhir menampilkan pratinjau tetap dari jumlah KK dan jiwa.
- Tidak ada perubahan skema atau penghapusan data.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Views/admin/profil/_module-editors.php` | Menampilkan isian angka dan pratinjau tanpa kolom edit teks. |
| `app/Views/admin/profil/_content-fields.php` | Menampilkan rentang usia tetap dan menyembunyikan kontrol tambah/hapus khusus sensus. |
| `app/Libraries/PublicProfileContent.php` | Memperjelas nama field sensus. |
| `tests/unit/PublicProfileContentIntegrationTest.php` | Memastikan teks/rentang tetap dan angka masih dapat diedit serta disimpan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 197 tes, 1.211 assertion. |
| Pemeriksaan sintaks PHP | PASS — semua file PHP yang berubah valid. |
| `git diff --check` | PASS. |
| Pratinjau browser | PASS — perubahan jumlah KK memperbarui pratinjau; rentang usia tampil tanpa kontrol edit. |
