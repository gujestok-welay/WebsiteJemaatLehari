# TASK-006I — Login Media Center

26 Oktober 2026. Lingkup: tampilan `/admin/login` saja. PRD tetap draft untuk validasi; penggunaan foto/logo dalam implementasi bukan bukti izin publikasi atau persetujuan identitas resmi.

Login memakai foto asli `Gereja-tampak-samping.jpeg` (720×900), logo GPM yang disediakan (283×275), heading Cormorant Garamond 600, DM Sans lokal, serta palet ungu/krem dari DESIGN.md. Desktop memiliki dua kolom sama lebar; di bawah 992px formulir muncul lebih dahulu dan foto setinggi 240px berada setelahnya. Tidak ada aset referensi, CDN, font baru, atau dependensi frontend baru.

Foto tampak samping dipilih karena orientasi potret sesuai panel desktop dan menampilkan bangunan beserta menaranya tanpa teks tambahan pada gambar. Foto tampak depan 1600×1200 memiliki teks dan olahan visual; foto lain menampilkan orang/ibadah sehingga kurang sesuai untuk panel bangunan. Foto dan logo tidak diubah. Logo memakai lebar CSS dan tinggi otomatis untuk mempertahankan proporsinya.

## File

| File | Perubahan |
| --- | --- |
| `app/Views/admin/login.php` | Komposisi foto/form, logo, teks utama, pemuatan font/CSS/JS lokal, error dengan `role=alert` dan relasi ke form, tombol mata |
| `public/assets/css/admin-login.css` | CSS khusus login: grid responsif, token, tipografi, fokus, target sentuh, error dan reduced motion |
| `public/assets/js/admin-login.js` | Toggle password, ikon/aria-label sesuai keadaan, kembali tersembunyi pada `pageshow`; tombol disembunyikan bila JS tidak berjalan |
| `docs/dokumentasi-task/TASK-006I-LOGIN.md` | Keputusan dan hasil verifikasi ini |

`public/assets/images/logo/Logo_Gereja_Protestan_Maluku.jpg` telah tersedia sebagai file untracked sebelum pekerjaan dimulai. File tersebut dipakai langsung dan perlu ikut dalam distribusi proyek. Tidak digambar ulang atau dimodifikasi.

Controller, route, filter, session, hashing, CSRF, rate limiting, redirect, logout, database/schema, dashboard, empat halaman publik, PRODUCT.md, dan DESIGN.md tidak diedit. Field `email`, `password`, POST action, `csrf_field()`, autocomplete, `old('email')`, dan escaping error tetap dipertahankan. Password tidak dipulihkan dari old input.

## Verifikasi

Browser: Chrome headless lokal, halaman PHP yang benar-benar dilayani di `http://localhost:8080/admin/login`. Semua screenshot normal pada tiga lebar, screenshot error, pembesaran teks, dan fokus HP dibuka dan diperiksa secara visual.

| Pemeriksaan | Hasil | Bukti / batas |
| --- | --- | --- |
| Inspector sebelum | FAIL | 3 FAIL: heading empat baris pada 375/768/1440px; tablet juga tampak bertabrakan secara visual |
| Inspector sesudah 375/768/1440px | PASS | 0 FAIL, 4 WARN yang dijelaskan di bawah; heading maksimal dua baris pada ukuran normal |
| Pemeriksaan browser khusus | PASS | 43 pemeriksaan lulus, 0 gagal; satu kelompok autentikasi terhalang |
| Foto, logo, font dan overflow | PASS | Aset termuat, logo proporsional, tidak ada horizontal overflow pada tiga lebar |
| Tombol mata | PASS | Klik, Enter, Space; label dan ikon berubah; `type=button`; tidak memicu submit; password awal tersembunyi |
| Keyboard/fokus | PASS | Email → password → mata → Masuk → kembali; outline solid 3px pada semua kontrol; target minimum 44px |
| Validasi browser | PASS | Field kosong dan format email salah mencegah submit; UI validasi memakai perilaku native browser |
| Teks 200%, reduced motion, tanpa JS | PASS | Tidak ada overflow; transisi mati ketika reduced motion; form tetap tersedia tanpa JS dan password tetap tersembunyi |
| JavaScript / HTTP aset | PASS | Tidak ada exception JavaScript atau respons aset gagal pada kunjungan normal |
| Error login/rate limit pada tampilan | PASS (fixture) | View PHP yang sama dirender dengan pesan pembatasan yang sudah ada di controller; wrapping, role alert, relasi form, password kosong, dan tiga lebar diperiksa. Ini bukan bukti rate limiting end-to-end |
| CSRF tanpa token | PASS | POST tanpa token dialihkan kembali ke login dengan error; HTTP akhir 200 karena lingkungan memakai perilaku redirect. Tidak mencapai dashboard |
| Login invalid | BLOCKED | POST dengan identitas non-akun acak berakhir HTTP 500; log menunjukkan MySQL lokal menolak koneksi. Percobaan lanjutan dihentikan |
| Rate limiting end-to-end | BLOCKED | Tidak dapat mencapai lima kegagalan dengan database yang tidak tersedia. Tidak ada penguncian akun nyata yang dipicu |
| Login valid | BELUM DIJALANKAN | Tidak ada akun uji terisolasi/kredensial uji, dan MySQL tidak tersedia. Tidak membuat akun atau mengubah data untuk menguji |
| Empat halaman publik | PASS (smoke) | `/`, `/profil`, `/berita`, `/jadwal-ibadah`: HTTP 200, h1 dan pemberitahuan dummy tetap ada; tautan kembali bekerja |
| Proteksi dashboard | PASS | Akses tanpa sesi diarahkan ke login |
| PHP syntax | PASS | `php -l app/Views/admin/login.php` |
| JavaScript syntax | PASS | `node --check public/assets/js/admin-login.js` |
| CSS lint | PASS | Stylelint lokal cache, aturan proyek `.taste-skill/task-006g-stylelint.json`; exit 0 |
| Taste lint | PASS | 0 FAIL, 2 WARN karena `box-shadow: none` yang memang meniadakan shadow Bootstrap |
| Impeccable detector | PASS dengan advisory | Dua advisory ukuran heading 2.25rem: ukuran login yang sengaja berada di antara skala editorial dan kontrol. DESIGN.md tidak diubah |
| Git whitespace | PASS | `git diff --check` |
| PHPUnit | TIDAK BERJALAN | `vendor/bin` tidak tersedia; runner global lama gagal pada PHP 8.5 dengan fungsi `each()` yang sudah dihapus. Tidak mengklaim unit suite lulus |

Pemeriksaan browser tersimpan di `.taste-skill/task-006i-check/checks.json`. Script lokal `.taste-skill/task-006i-check.mjs` dan renderer fixture `.taste-skill/task-006i-render-error.php` hanya untuk verifikasi dan tidak dimuat produk. Artefak `.taste-skill/` diabaikan Git. Tidak ada kredensial akun nyata yang digunakan, dicetak, atau disimpan pada dokumen/screenshot. Runtime lokal menghasilkan file session/log di `writable/`; file tersebut bukan sumber perubahan produk.

Keterbatasan lain: belum diuji pada perangkat fisik, browser selain Chrome, pembaca layar, zoom browser 400%, dan jaringan lambat. Pembesaran root font 200% bukan pengganti semua pengujian zoom. Bahasa balon validasi native mengikuti browser/perangkat. Kontras teks menggunakan pasangan ink/muted/purple pada paper serta error ink pada error surface; tidak ada klaim audit WCAG penuh.

## Bukti browser

- [Desktop 1440px](../../.taste-skill/task-006i-after/desktop-fold.jpg)
- [Tablet 768px](../../.taste-skill/task-006i-after/tablet-fold.jpg)
- [HP 375px](../../.taste-skill/task-006i-after/mobile-fold.jpg)
- [Error pada HP — fixture](../../.taste-skill/task-006i-check/error-375.jpg)
- [Error pada tablet — fixture](../../.taste-skill/task-006i-check/error-768.jpg)
- [Error pada desktop — fixture](../../.taste-skill/task-006i-check/error-1440.jpg)
- [Fokus tombol mata pada HP](../../.taste-skill/task-006i-check/focus-375.jpg)
- [Teks 200% pada HP](../../.taste-skill/task-006i-check/text200-375.jpg)
- [Laporan inspector](../../.taste-skill/task-006i-after/report.json)
- [Hasil pemeriksaan interaksi](../../.taste-skill/task-006i-check/checks.json)

## Catatan desain dan kritik

Skill design-taste: app screen, gaya **Editorial greeting**, seed **717157971**. Palet/type dipertahankan dari proyek. Tidak ada redraw; komposisi, aset dan heading menyesuaikan keputusan eksplisit TASK-006I: foto/form dua kolom, logo asli, dan judul yang ditentukan. Shell/sidebar/panel data pada sheet tidak diterapkan karena ini adalah gerbang login dengan satu tugas. Impeccable mode Operate; context dan detector berjalan. Assessment layout independen membaca sumber sebelum perubahan.

Pertanyaan layar: “Bagaimana pengelola masuk ke Media Center Jemaat Leahari?” Identitas/logo mengantar heading dan satu formulir. Foto gereja menjadi pengenal tempat yang mendampingi pintu masuk digital. Tidak ada data contoh jemaat, klaim persetujuan, fitur baru, atau foto dari sumber luar.

ROUND 1 — hasil akhir, satu batch visual dan satu batch interaksi.

Seen in one second: login Media Center Jemaat Leahari, dengan aksi Masuk yang jelas.

Idea (one sentence): foto gereja Jemaat Leahari menjadi panel pengenal tempat di samping pintu masuk pengelolaan informasi.

Live thing: formulir autentikasi yang sudah ada dan kontrol tampilkan/sembunyikan password.

Layout words by section: foto=panel potret penuh / formulir=kolom baca tenang / mobile=formulir dahulu.

Scores (penilaian visual internal, bukan hasil UAT): design 8 · usability 8 · creativity 7 · content 8 → overall 7.9.

Caps applied: tidak ada cap halaman pemasaran; ini layar kerja dengan tugas tunggal.

Catatan paling terlihat dan keputusan:

1. `desktop-fold.jpg`: bagian bangunan dalam foto asli relatif gelap. Dipertahankan tanpa filter/olahan untuk menjaga aset asli; siluet, menara dan bentuk gedung tetap terlihat.
2. `tablet-fold.jpg`: foto lanskap bawah memang hanya menampilkan sebagian bangunan. Dipertahankan agar foto tidak mendorong formulir ke bawah.
3. `error-375.jpg`: pesan error menambah tinggi form sehingga tautan kembali berada di bagian bawah viewport. Semua kontrol tetap mengalir dan dapat dijangkau melalui scroll, tanpa tinggi tetap atau elemen terpotong.

Jawaban kritik app: tugas login paling dominan; kolom seimbang dan form memiliki lebar baca yang dibatasi; identitas asli terlihat; angka/tabel/chart/legend tidak relevan; pada HP semua kontrol mendahului foto. Tidak ada sidebar atau panel statistik.

Empat WARN inspector diterima: `palette-cream` adalah paper DESIGN.md; `unused-canvas` membaca foto sebagai sidebar padahal form berada di tengah kolom kanannya; `panel-void` adalah ruang di sekitar form dalam panel layar penuh; `app-title-size` adalah heading editorial login sesuai brief. Tidak ada FAIL yang disembunyikan. Warna proyek dipertahankan meskipun berlawanan dengan preferensi palet generik skill.

Sidecar `.impeccable/design.json` dilaporkan stale oleh engine. Tidak diperbaiki karena di luar TASK-006I; dapat disinkronkan melalui perintah `document` pada task terpisah dengan tetap menjaga DESIGN.md.
