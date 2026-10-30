# TASK-DOCS — Standarisasi dokumentasi pengerjaan

Tanggal: 28 Oktober 2026.

## Tujuan

Memusatkan laporan task di `docs/dokumentasi-task/`, mempertahankan isi substantif laporan, dan membakukan aturan dokumentasi pengerjaan. Status draft serta keputusan terbuka pada [PRD](../PRD.md) tetap berlaku.

## Perubahan dan file terdampak

Empat laporan dipindahkan dari direktori langsung `docs/` ke folder laporan yang sudah tersedia:

- [TASK-006E-TYPOGRAPHY.md](TASK-006E-TYPOGRAPHY.md).
- [TASK-006F-AUDIT.md](TASK-006F-AUDIT.md).
- [TASK-006G-HARDEN.md](TASK-006G-HARDEN.md).
- [TASK-006I-LOGIN.md](TASK-006I-LOGIN.md), termasuk laporan yang belum dilacak Git sebelum task ini.

Referensi yang diperbaiki:

| File | Perbaikan |
| --- | --- |
| [DESIGN.md](../../DESIGN.md) | Tiga tautan menuju laporan E/F/G. |
| [MIGRATION-MAP.md](../wordpress-migration/MIGRATION-MAP.md) | Empat tautan menuju laporan E/F/G/I dan satu keterangan lokasi folder laporan. |
| [TASK-006F-AUDIT.md](TASK-006F-AUDIT.md) | Satu path laporan E dalam daftar acuan. |
| [TASK-006I-LOGIN.md](TASK-006I-LOGIN.md) | Satu path laporan sendiri dan sepuluh tautan relatif ke artefak lokal `.taste-skill/`. |

[AGENTS.md](../../AGENTS.md) ditambah aturan permanen lokasi laporan, nama `TASK-<ID>-<NAMA>.md`, isi minimum, lokasi dokumentasi utama, dan larangan mencatat informasi sensitif. Laporan ini ditambahkan mengikuti aturan tersebut. Tidak ada file selain laporan task yang dipindahkan; dokumentasi utama tetap di lokasi semula.

## Pengujian

- **PASS:** empat laporan tersedia pada lokasi baru dan tidak tersisa langsung di `docs/`.
- **PASS:** isi empat laporan dibandingkan dengan salinan sebelum pemindahan; identik selain penggantian path yang dirinci di atas.
- **PASS:** 849 file lain yang terlihat melalui `git ls-files --cached --others --exclude-standard` memiliki hash SHA-256 identik sebelum/sesudah pemindahan dan pembaruan referensi. Ini mencakup perubahan aplikasi yang sudah ada sebelum task ini; file ignored dan kondisi database tidak termasuk bukti tersebut.
- **PASS:** 16 file Markdown proyek diperiksa (dokumentasi akar, `docs/`, README pengujian dan font; dependency/framework serta skill dikecualikan). Seluruh 50 kemunculan tautan lokal memiliki target yang tersedia, termasuk sepuluh artefak lokal laporan login; tidak ditemukan referensi path laporan lama. Pemeriksaan ini memverifikasi keberadaan target file/folder, bukan jaringan atau anchor halaman.
- **PASS:** empat laporan pindahan dan satu laporan baru mengikuti format nama yang ditetapkan; tidak ada laporan task langsung di `docs/`.
- **PASS:** `git diff --check`, exit 0. Git hanya memperingatkan normalisasi LF ke CRLF pada AGENTS.md, DESIGN.md, dan file login yang sudah berubah sebelum task ini. File Markdown baru/belum dilacak juga diperiksa tersendiri untuk trailing whitespace dan penanda konflik.

## Keterbatasan

Pengujian aplikasi, browser, database, autentikasi, dan UI tidak dijalankan karena perubahan hanya dokumentasi. Tautan eksternal tidak diperiksa melalui jaringan. Tautan artefak `.taste-skill/` mengacu pada bukti lokal yang diabaikan Git; ketersediaannya pada checkout lain tidak dijamin. Hasil pengujian fitur di laporan lama adalah catatan historis dan tidak diuji ulang.

## Tindak lanjut

Gunakan folder, penamaan, dan isi minimum laporan sesuai AGENTS.md untuk setiap penyelesaian fitur/task berikutnya. Tidak membuat commit otomatis.
