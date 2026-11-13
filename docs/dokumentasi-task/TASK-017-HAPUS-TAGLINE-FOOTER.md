# TASK-017 — Menghapus tagline footer

## Tujuan

Menghapus teks “Informasi yang mendekatkan.” dari footer publik tanpa mengubah elemen footer lainnya.

## Perubahan

- Menghapus paragraf `.footer-tagline` dari partial footer bersama.
- Menambahkan assertion regresi bahwa tagline tidak ditampilkan.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Views/partials/footer.php` | Menghapus paragraf tagline footer. |
| `tests/unit/PublicPagesTest.php` | Memastikan tagline tidak dirender. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes footer terfokus | PASS — 1 tes, 14 assertion. |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 196 tes, 1.190 assertion. |
| Pemeriksaan sintaks PHP | PASS — kedua file PHP valid. |
| `git diff --check` | PASS. |
