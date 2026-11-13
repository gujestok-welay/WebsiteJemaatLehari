# TASK-014 — Menghapus judul dan deskripsi pengelola profil

Tanggal: 9 November 2026.

## Tujuan

Menghapus judul “Kelola Profil Jemaat” dan paragraf pengantar pada halaman kelola konten profil sesuai elemen yang ditunjuk, tanpa menghilangkan navigasi kembali atau nama aksesibel bagian.

## Perubahan

- Menghapus elemen `h1#profile-manage-title` dan paragraf pengantar dari header halaman editor profil.
- Mempertahankan tautan “Kembali ke Profil” dan memberi nama aksesibel pada landmark pengelolaan profil.
- Menghapus aturan CSS yang hanya digunakan oleh judul dan paragraf tersebut.
- Menambahkan tes regresi untuk memastikan elemen hilang dan nama landmark serta navigasi tetap ada.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Views/admin/profil/manage.php` | Menghapus judul dan paragraf; mempertahankan nama aksesibel dan tautan kembali. |
| `public/assets/css/admin.css` | Menghapus aturan judul dan paragraf yang tidak lagi dipakai. |
| `tests/unit/PublicProfileContentIntegrationTest.php` | Menguji hilangnya elemen dan tetap tersedianya landmark serta tautan kembali. |
| `docs/dokumentasi-task/TASK-014-HAPUS-HEADER-KELOLA-PROFIL.md` | Mencatat lingkup dan hasil verifikasi task. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes regresi sebelum implementasi | FAIL sesuai harapan — judul masih ditemukan pada halaman editor sebelum dihapus. |
| Tes regresi sesudah implementasi | PASS — 1 tes, 4 assertion. |
| PHPUnit seluruh suite (`--no-coverage`) | PASS — 183 tes, 1.115 assertion. |
| E2E Profil & Majelis untuk navigasi editor dan tampilan desktop/ponsel | PASS — 2 tes Playwright. |
| Sintaks PHP dan `git diff --check` | PASS. |

## Keterbatasan

Halaman browser yang dibagikan tidak dapat dimuat ulang karena server lokal pada port 8091 sudah berhenti. Alur halaman tetap diuji melalui Playwright dengan server tes terisolasi.
