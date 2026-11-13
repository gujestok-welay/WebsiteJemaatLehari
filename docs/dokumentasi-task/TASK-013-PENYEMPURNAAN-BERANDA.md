# TASK-013 — Penyempurnaan keandalan dan hierarki Beranda

Tanggal: 6 November 2026.

## Tujuan

Menampilkan informasi yang bisa dipercaya sebagai prioritas beranda serta mengurangi usaha menemukan berita dan jadwal pada layar kecil.

## Perubahan

- Sorotan dan daftar ringkas beranda hanya menggunakan jadwal mendatang berdasarkan tanggal dan waktu WIT; jadwal terdekat dipilih terlebih dahulu.
- Beranda menampilkan keadaan kosong bila tidak ada jadwal mendatang.
- Kartu fallback contoh dan berita yang memuat filler “Lorem ipsum” menunjukkan badge “Contoh” pada beranda dan halaman Berita. Data/status di database tidak diubah.
- Bagian berita dan jadwal dipindahkan tepat setelah hero; bagian komunitas/profil berulang dihapus, satu tautan profil tetap tersedia.
- Hierarki hero diringkas dan tanggal jadwal ditampilkan sekali dalam format Indonesia.
- `DESIGN.md` diselaraskan dengan token dan perilaku visual yang saat ini dipakai CSS publik.

## File terdampak

| File | Perubahan |
| --- | --- |
| `app/Controllers/Home.php` | Memilih dan mengurutkan jadwal mendatang menurut WIT. |
| `app/Views/publik/beranda.php` | Menggunakan empty state jadwal, memprioritaskan berita/jadwal, dan merampingkan konten berulang. |
| `app/Views/publik/berita.php` | Mengirim status contoh ke kartu berita bersama. |
| `app/Views/components/berita-card.php` | Menampilkan badge status contoh yang eksplisit. |
| `public/assets/css/publik.css` | Gaya badge status contoh. |
| `DESIGN.md` | Mencatat token dan perilaku CSS yang berlaku. |
| `docs/UI-PUBLIK.md` | Dokumentasi perilaku beranda dan kartu contoh. |
| `tests/unit/PublicPagesTest.php` | Regresi jadwal, badge, dan urutan beranda. |
| `tests/unit/PublicScheduleIntegrationTest.php` | Memastikan jadwal tersimpan yang ditampilkan di beranda memang mendatang. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes regresi baru sebelum implementasi | PASS — tes jadwal, penanda konten contoh, dan urutan beranda gagal pada perilaku yang belum tersedia. |
| PHPUnit seluruh suite | PASS — 151 tes, 879 assertion (`--no-coverage`). |
| E2E empat halaman publik | PASS — 4 tes Playwright. |
| Browser beranda desktop 1440 px | PASS — berita/jadwal mendahului Majelis; badge contoh terlihat; tanpa overflow horizontal. |
| Browser beranda ponsel 390 px | PASS — urutan berita/jadwal mendahului Majelis; badge contoh terlihat; tanpa overflow horizontal. |
| `git diff --check` | PASS. |
