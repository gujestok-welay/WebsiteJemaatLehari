# TASK-015 — Mempersempit jarak navbar ke konten admin

Tanggal: 9 November 2026.

## Tujuan

Mengurangi ruang kosong antara navbar admin dan awal konten halaman, termasuk editor Profil & Majelis, tanpa mengubah jarak internal formulir.

## Perubahan

- Mengubah padding atas `.admin-content` menjadi `clamp(20px, 2.5vw, 32px)`; padding bawah dan lebar konten tidak berubah.
- Padding tablet/ponsel yang sudah ditetapkan pada breakpoint tetap digunakan.
- Menambahkan tes E2E yang mengukur jarak visual navbar ke konten pada desktop dan ponsel.

## File terdampak

| File | Perubahan |
| --- | --- |
| `public/assets/css/admin.css` | Mengurangi padding atas default area konten admin. |
| `e2e/profile-modules.spec.js` | Menguji jarak navbar ke konten pada viewport desktop dan ponsel. |
| `docs/dokumentasi-task/TASK-015-PERSEMPIT-JARAK-ADMIN-CONTENT.md` | Mencatat lingkup dan hasil verifikasi task. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes E2E baru sebelum perubahan CSS | FAIL sesuai harapan — jarak desktop 51,2 px, melebihi batas 36 px. |
| Tes E2E jarak sesudah perubahan CSS | PASS — 1 tes pada desktop dan ponsel. |
| Pengukuran browser desktop | PASS — jarak navbar ke konten 32 px. |
| Pengukuran browser ponsel 390 px | PASS — jarak 22 px dan tanpa overflow horizontal. |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 183 tes, 1.115 assertion. |
| Sintaks JavaScript E2E dan `git diff --check` | PASS. |
