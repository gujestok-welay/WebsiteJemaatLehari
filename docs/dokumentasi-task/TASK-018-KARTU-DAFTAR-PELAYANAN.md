# TASK-018 — Memisahkan kartu daftar pelayanan

## Tujuan

Memudahkan admin mengenali dan mengedit setiap pelayanan secara terpisah pada editor Pelayanan Peribadahan.

## Perubahan

- Setiap entri pelayanan ditampilkan sebagai kartu dengan border, permukaan, dan jarak antar-kartu yang konsisten dengan tema admin.
- Gaya dibatasi pada editor Pelayanan Peribadahan; daftar berulang di modul lain tidak berubah.
- Menambahkan tes untuk kartu yang sudah ada dan kartu baru.

## File terdampak

| File | Perubahan |
| --- | --- |
| `public/assets/css/admin.css` | Gaya kartu khusus editor pelayanan. |
| `e2e/profile-modules.spec.js` | Tes tampilan kartu dan penambahan pelayanan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Tes kartu pelayanan terfokus | PASS — 1 tes. |
| Playwright seluruh suite | 85 lulus, 4 gagal pada tes ringkasan Majelis, galeri kosong, pencarian jadwal, dan tema gelap admin. |
| `git diff --check` | PASS. |
