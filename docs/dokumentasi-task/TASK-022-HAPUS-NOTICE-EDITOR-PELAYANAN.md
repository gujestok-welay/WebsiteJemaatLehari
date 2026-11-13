# TASK-022 — Menghapus notice editor pelayanan

## Tujuan

Menghapus pemberitahuan yang ditunjuk dari editor Pelayanan Peribadahan tanpa mengubah formulir atau pemberitahuan pada editor lain.

## Perubahan

- Menghapus `.profile-content-notice` yang menjelaskan bahwa daftar berisi jenis pelayanan, bukan jadwal ibadah.
- Mempertahankan deskripsi editor, field pelayanan, dan notice pada editor Konten Profil.
- Menambahkan assertion regresi bahwa editor pelayanan tidak merender notice tersebut.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Views/admin/profil/_module-editors.php` | Menghapus notice dari editor Pelayanan Peribadahan saja. |
| `tests/unit/PublicProfileContentIntegrationTest.php` | Memastikan notice tidak dirender pada editor pelayanan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes regresi sebelum implementasi | FAIL sesuai harapan — notice masih dirender. |
| Tes editor pelayanan terfokus | PASS — 1 tes, 5 assertion. |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 198 tes, 1.228 assertion. |
| Pemeriksaan sintaks PHP | PASS — kedua file PHP valid. |
| `git diff --check` | PASS. |
