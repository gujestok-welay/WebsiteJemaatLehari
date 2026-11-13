# TASK-008 — Penanganan hasil update berita

Tanggal: 5 November 2026.

## Tujuan

Membedakan update berita yang berhasil tanpa perubahan nilai dari update yang tidak lagi menemukan record setelah lookup. Tidak mengubah skema database atau perilaku arsip.

## Akar masalah dan perubahan

CodeIgniter mengembalikan `true` ketika query UPDATE berhasil dijalankan, terlepas dari jumlah baris yang berubah. Karena itu, `affectedRows() === 0` dapat berarti update no-op yang valid maupun record sudah tidak ada.

`BeritaModel::update()` kini mempertahankan sukses untuk query yang berhasil dan nilai yang tidak berubah. Bila driver melaporkan nol baris terdampak, model melakukan lookup berdasarkan ID; status `not_found` mengembalikan hasil gagal agar controller tidak menampilkan notifikasi sukses untuk record yang hilang. Kegagalan lookup tidak mengubah hasil query yang sudah berhasil.

Kegagalan query daftar berita tidak diubah pada task ini: audit menemukan exception handler production CodeIgniter sudah memberi HTTP 500 dengan halaman generik, sehingga belum ada kebutuhan terkonfirmasi untuk menambah jalur respons lokal.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Models/BeritaModel.php` | Memastikan update nol baris terdampak tetap sukses untuk record yang masih ada, tetapi gagal bila record tidak ditemukan. |
| `tests/unit/BeritaModelArchiveTest.php` | Menambahkan regresi untuk no-op update dan record yang hilang setelah lookup. |
| `docs/dokumentasi-task/TASK-008-BERITA-UPDATE-RESULT.md` | Catatan task dan hasil verifikasi. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| RED sebelum perubahan: `BeritaModelArchiveTest` | 6 tes, 41 assertions; 1 gagal sesuai reproduksi karena update record hilang menghasilkan `true`. |
| GREEN: `BeritaModelArchiveTest` | 6 tes, 41 assertions; lulus. |
| Regresi berita: `BeritaModelArchiveTest` + `AdminBeritaUploadTest` | 35 tes, 153 assertions; lulus. |
| Suite penuh PHPUnit | 118 tes, 590 assertions; lulus. |
| PHP syntax: `BeritaModel.php`, `BeritaModelArchiveTest.php` | Tanpa kesalahan sintaks. |

Semua PHPUnit dijalankan pada environment `testing`; konfigurasi memaksa grup `default` dan `tests` ke SQLite `:memory:`. Tidak ada koneksi atau perubahan ke database MySQL.
