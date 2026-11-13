# TASK-009 — Penyempurnaan metrik dashboard admin

Tanggal: 5 November 2026.

## Tujuan

Memperjelas hierarki tiga metrik di dashboard admin, terutama pada layar ponsel, tanpa mengubah isi, tujuan tautan, atau perilaku modul lain.

## Perubahan

- Metrik dashboard menjadi kartu terpisah dengan garis tipis, radius 14 px, dan jarak yang konsisten.
- Pada layar sempit, kartu ditata satu kolom; label dan nilai tetap mudah dipindai, sementara tautan memiliki area sentuh minimum 44 px.
- Hover kartu mengikuti palet admin dan hanya aktif bila perangkat mendukung hover serta preferensi gerak tidak dikurangi.
- Aturan dibatasi pada `.metrics` dashboard; ringkasan jadwal tetap memakai tata letak bersama yang ada.

## File terdampak

| File | Perubahan |
| --- | --- |
| `public/assets/css/admin.css` | Penyempurnaan kartu metrik dashboard dan tata letak responsif. |
| `docs/dokumentasi-task/TASK-009-DASHBOARD-METRICS-POLISH.md` | Catatan task dan hasil pemeriksaan. |

## Pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Browser dashboard pada 320, 390, 768, dan 1440 px | PASS — tiga metrik tampil; satu kolom pada lebar hingga 700 px dan tiga kolom di atasnya; tidak ada overflow horizontal; tautan minimal 44 px. |
| Fokus keyboard dan reduced motion | PASS — fokus tautan terlihat; transformasi kartu tidak aktif saat reduced motion. |
| Ringkasan jadwal pada 390 px | PASS — empat metrik tetap dua kolom dengan bingkai bersama. |
| `impeccable detect` pada view dan CSS | 0 anti-patterns. Pemindaian berkas sumber tidak menjalankan pemeriksaan yang memerlukan halaman ter-render. |
| `git diff --check` | PASS. |
| `php vendor/bin/phpunit tests/unit/AdminPartialNavigationTest.php` | GAGAL — 7 tes: 2 error karena `$formStatusOptions` tidak terdefinisi pada view berita dan 3 assertion gagal pada respons parsial jadwal, profil, dan statistik; 2 tes lain lulus. |
| `node tests/auth/admin-entry-http.mjs` | GAGAL — pemeriksaan anonim lulus, tetapi sesi sintetis menerima 302 saat skrip mengharapkan 200. |

Pemeriksaan PHPUnit dan HTTP mencakup alur lain selain CSS dan dicatat apa adanya; keduanya perlu ditangani terpisah.
