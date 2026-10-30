# TASK-006I-AUTH-FIX — Diagnosis dan Perbaikan Redirect Login Admin

Tanggal: 28 Oktober 2026.

## Tujuan dan lingkup

Memperbaiki alur login admin yang kembali ke Beranda saat dibuka melalui `http://localhost:8080/index.php/admin/login`. AGENTS.md, PRD draft, routes, controller, filter, view, JavaScript, konfigurasi URL/CSRF/cookie/session, dan implementasi framework diperiksa. Status draft PRD dan keputusan terbuka tetap berlaku. TASK-006J tidak dikerjakan; tidak ada commit.

## Akar masalah yang terbukti

### 1. Host baseURL berbeda dari host halaman login

Sebelum perubahan, override `app.baseURL` dalam `.env` menunjuk host LAN, sedangkan halaman pengujian dibuka di `localhost`. GET login menghasilkan HTTP **200**, tetapi action formulir menunjuk host LAN dengan path `/index.php/admin/login`. Filter dashboard juga menghasilkan **302** ke host LAN.

Cookie CSRF dan sesi yang diterbitkan pada `localhost` tidak dibagikan ke host LAN. Reproduksi HTTP dengan token dari GET localhost, tanpa cookie localhost pada POST host LAN, dan Referer yang hanya berisi origin menghasilkan **303**, `Location: http://localhost:8080/`. Ini sesuai pengiriman Referer lintas origin pada kebijakan default browser. Implementasi `system/Filters/CSRF.php` mengembalikan redirect ke halaman sebelumnya saat verifikasi CSRF gagal dalam production. Jadi perpindahan ke Beranda terjadi sebelum pemeriksaan kredensial, bukan bukti autentikasi berhasil.

Form sudah benar memakai `site_url('admin/login')`; `indexPage` efektif adalah `index.php`. Tidak ada JavaScript yang mengganti tujuan submit. Menghapus `index.php` atau mengganti action menjadi URL hardcoded tidak diperlukan.

### 2. Tujuan kegagalan bergantung pada riwayat navigasi

Dua cabang gagal dalam `AdminAuth::login()` memakai `redirect()->back()`. Framework mengutamakan `_ci_previous_url` dalam sesi, lalu Referer. Halaman publik yang dibuka pada tab lain dapat menjadi tujuan kembali tersebut. Cabang gagal kredensial dan pembatasan percobaan sekarang memiliki tujuan login eksplisit dengan `site_url()`. Regresi HTTP mengunjungi Beranda setelah GET login sebelum POST untuk memeriksa kasus ini.

### 3. Respons redirect login dengan sesi aktif dibuang

`loginForm(): string` sebelumnya mengembalikan `redirect()->to(...)->getBody()`. Nilai ini hanya body respons, bukan objek `RedirectResponse` yang membawa status dan Location. Controller sekarang mengembalikan objek redirect dan menerima tipe `string|RedirectResponse`. Dengan sesi sintetis lokal, GET login menghasilkan **302 → dashboard**, kemudian dashboard **200**.

## Perubahan dan file terdampak

| File | Perubahan |
| --- | --- |
| `.env` (lokal, diabaikan Git) | Menyamakan `app.baseURL` menjadi `http://localhost:8080/` sesuai alamat pengujian. |
| `app/Controllers/AdminAuth.php` | Mengembalikan objek redirect untuk sesi aktif dan menetapkan URL login eksplisit pada dua cabang gagal. |
| `tests/auth/admin-auth-http.mjs` | Regresi HTTP GET/POST, action same-origin, gagal kredensial, flash, throttle, CSRF, dashboard anonim, dan empat halaman publik. |
| `docs/dokumentasi-task/TASK-006I-AUTH-FIX.md` | Laporan ini. |

Routes, filter autentikasi, view/desain login, halaman publik, hashing, regenerasi sesi, skema database, serta mekanisme CSRF/rate limiting tidak diubah dalam task ini. Perubahan lain yang sudah ada di working tree dipertahankan.

Artefak diagnosis lokal dalam `.taste-skill/` diabaikan Git: `auth-fix-probe.php`, `auth-fix-session-check.mjs`, dan `auth-fix-browser.mjs`. Artefak tidak dimuat aplikasi. Pemeriksaan sesi memakai identitas sintetis tanpa membuat akun/database record; file sesi fixture dibersihkan setelah pengujian. Tidak ada nilai kredensial, cookie sesi, atau token dicetak dalam hasil pengujian/laporan.

## Pengujian

| Pemeriksaan | Status | Hasil |
| --- | --- | --- |
| Reproduksi awal lintas host | PASS | POST lintas host tanpa cookie localhost → 303 Beranda; GET/action membuktikan mismatch baseURL. |
| Database dan konfigurasi | PASS | Koneksi/query tersedia, tabel `admin_users` dan `admin_login_attempts` tersedia; direktori sesi writable, production, indexPage `index.php`. Tidak membaca/mencetak identitas akun. |
| GET login dan action form setelah perbaikan | PASS | 200; action tetap localhost, `/index.php/admin/login`, CSRF hidden field tersedia. |
| POST kredensial tidak valid | PASS | 303 → login 200; flash aman tampil, termasuk setelah Beranda menjadi previous URL. |
| Rate limiting | PASS | Lima percobaan gagal pada identitas acak yang tidak dikenal; percobaan keenam menampilkan pembatasan 15 menit dan tetap kembali ke login. |
| CSRF login/logout | PASS | POST AJAX tanpa token pada kedua endpoint menghasilkan 403. Proteksi tidak dinonaktifkan. |
| Dashboard tanpa sesi / sesudah login gagal | PASS | 302 → login; tidak memberikan akses admin. |
| GET login dengan sesi sintetis aktif | PASS | 302 → dashboard, bukan body kosong. |
| Dashboard dengan sesi sintetis tersimpan | PASS | 200, membaca state admin dari file sesi antar-request. Bukan bukti kredensial valid. |
| Logout dengan CSRF dan sesi sintetis | PASS | 303 → login; state admin dihapus dan identifier sesi berubah. |
| Dashboard setelah logout dan replay sesi lama | PASS | Keduanya 302 → login. Sesi lama tidak memberi akses ulang. |
| Browser desktop 1440 / HP 390 — alur autentikasi | PASS | Submit form nyata: GET login → POST login → 303 login → GET login; pesan kredensial gagal terlihat pada kedua viewport. |
| Browser — overflow horizontal | FAIL | Desktop tidak overflow; pada viewport 390 halaman setelah error terukur overflow. CSS/layout tidak diubah karena di luar lingkup perbaikan autentikasi. |
| Redirect loop | PASS | Cabang yang diuji berakhir di login 200 atau dashboard 200 sesuai state sesi. |
| Empat halaman publik | PASS | `/`, `/profil`, `/berita`, `/jadwal-ibadah` menghasilkan 200 dan identitas halaman. |
| Sintaks PHP | PASS | `php -l` pada Routes, AdminAuth, AdminAuthFilter, login view, App, Session. |
| Sintaks runner regresi | PASS | `node --check tests/auth/admin-auth-http.mjs`. |
| Whitespace | PASS | `git diff --check`; hanya pemberitahuan konversi LF/CRLF Git. |
| Login berhasil dengan akun uji nyata | BLOCKED | Database tersedia, tetapi kredensial akun pengujian aman tidak tersedia. Tidak membuat akun atau mengganti kata sandi. Cabang sukses password_verify dan POST → dashboard belum diverifikasi end-to-end. |
| PHPUnit | BLOCKED | `vendor/bin/phpunit` tidak tersedia. Regresi HTTP Node dijalankan sebagai verifikasi task ini; tidak mengklaim suite PHPUnit lulus. |

Perintah utama: `node tests/auth/admin-auth-http.mjs`, `node .taste-skill/auth-fix-session-check.mjs`, `node .taste-skill/auth-fix-browser.mjs`, `php .taste-skill/auth-fix-probe.php`, pemeriksaan sintaks di atas, dan `git diff --check`.

Percobaan browser dalam sandbox mengalami timeout CDP `Page.enable`; pengujian browser kemudian dijalankan di luar sandbox dengan izin. Pemeriksaan pertama berhenti pada assertion overflow HP; konfirmasi akhir memisahkan assertion alur autentikasi dari metrik layout dan tetap melaporkan overflow tersebut sebagai FAIL. Tidak dilakukan redesign, pemilihan arah desain baru, atau audit visual penuh; skill dipakai untuk mempertahankan antarmuka/form yang sudah disetujui.

## Keterbatasan dan tindak lanjut

- `.env` tidak masuk Git. Saat dipindahkan ke mesin/hosting lain, pastikan `app.baseURL` berisi origin serta subfolder yang benar. Gunakan host yang sama untuk membuka login dan melakukan submit; jangan mencampur localhost dan alamat LAN. Untuk akses LAN, konfigurasi baseURL ke alamat LAN lalu mulai sesi dari alamat tersebut.
- Pertahankan `indexPage = 'index.php'` bila rewrite belum aktif. Perubahan ini tetap memakai `site_url()`, sehingga mengikuti konfigurasi CodeIgniter. Matriks server Apache tanpa rewrite, konfigurasi indexPage kosong, dan deployment subfolder belum diuji langsung.
- Regresi memakai identitas non-akun acak. Percobaan login menghasilkan catatan throttle melalui mekanisme aplikasi biasa; tidak menghapus data akun atau melakukan migrasi. Pembersihan percobaan kedaluwarsa tetap mekanisme yang sudah ada.
- Verifikasi sukses autentikasi penuh perlu akun uji aman yang disediakan pemilik: login → dashboard, buka ulang login saat sesi aktif, logout → akses dashboard ditolak. Sesi sintetis hanya membuktikan perilaku redirect, persistensi sesi, filter, dan logout.
- Perangkat fisik, browser selain Chromium, HTTPS produksi, dan UAT Media Center belum diuji.
- Overflow horizontal pada halaman error viewport 390 perlu diagnosis UI terpisah; hasil alur redirect tidak menghapus temuan visual ini.
- Context Impeccable melaporkan sidecar `.impeccable/design.json` stale. Sinkronisasi melalui `document` dapat dikerjakan terpisah; tidak diubah dalam task autentikasi ini.
