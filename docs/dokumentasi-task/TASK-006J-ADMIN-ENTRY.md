# TASK-006J — Pintu Masuk Admin Jemaat Leahari

Tanggal: 28 Oktober 2026.

## Tujuan

Menjadikan `GET /admin` sebagai alamat masuk administrasi yang dapat disimpan operator, dengan mengarahkan sesi anonim ke login dan sesi admin aktif ke dashboard yang sudah ada. AGENTS.md, PRODUCT.md, DESIGN.md, PRD, routing, autentikasi, filter, dan konfigurasi sesi telah diperiksa. PRD tetap draft untuk validasi; task ini tidak menetapkan keputusan terbuka sebagai persetujuan mitra.

## Perubahan

- Menambahkan route GET `admin` ke `AdminAuth::index()`.
- Method tersebut memeriksa truthiness `session()->get('admin_id')`, sama dengan `loginForm()` dan `AdminAuthFilter`. Tanpa nilai tersebut: redirect ke `admin/login`; dengan nilai tersebut: redirect ke `admin/dashboard`.
- Mengembalikan objek `RedirectResponse` dengan `site_url($target)`, sehingga URL mengikuti baseURL, subfolder, dan konfigurasi `indexPage`, termasuk `index.php`.
- Mempertahankan GET/POST `/admin/login`, GET `/admin/dashboard`, POST `/admin/logout`, filter dashboard, dan CSRF pada POST.
- Tidak mengubah desain/view, tautan publik, `.env`, database, filter keamanan, konfigurasi sesi, atau server. Tidak membuat login/akun baru dan tidak melakukan commit.

Working tree sudah memuat perubahan TASK-006I dan berkas lain sebelum task ini. Perubahan tersebut dipertahankan; diff controller keseluruhan juga memuat perubahan sebelumnya.

## File terdampak

| File | Perubahan TASK-006J |
| --- | --- |
| `app/Config/Routes.php` | Satu route GET `admin`. |
| `app/Controllers/AdminAuth.php` | Method `index()` untuk menentukan tujuan masuk. |
| `tests/auth/admin-entry-http.mjs` | Regresi HTTP dengan sesi file sintetis lokal, tanpa akses database. |
| `docs/dokumentasi-task/TASK-006J-ADMIN-ENTRY.md` | Laporan dan panduan bookmark/shortcut ini. |

## Pengujian

Server lokal dijalankan memakai `php spark serve --host localhost --port 8080`. Regresi dijalankan melalui `node tests/auth/admin-entry-http.mjs` dari root proyek, dengan konfigurasi efektif URL memakai `index.php` dan FileHandler sesi bawaan. Runner membuat sesi sintetis acak, tidak mencetak cookie/token, dan membersihkan file sesi milik pengujian dalam `finally`. Tidak membuat akun atau mengubah data database.

| Pemeriksaan | Status | Hasil |
| --- | --- | --- |
| Sebelum login: GET `/admin` dan `/index.php/admin` | PASS | 302 ke `/index.php/admin/login`, lalu login 200; tidak ada loop. |
| Sesi admin aktif sintetis: kedua URL masuk | PASS | 302 ke `/index.php/admin/dashboard`, lalu dashboard 200. |
| GET login dengan sesi aktif sintetis | PASS | Route lama tetap 302 ke dashboard. |
| Dashboard tanpa sesi admin | PASS | 302 ke login, tetap terlindungi. |
| POST login/logout tanpa CSRF | PASS | Keduanya 403 untuk request AJAX tanpa token; sesi aktif tetap dapat membuka dashboard. |
| POST logout dengan CSRF dan sesi sintetis | PASS | 303 ke login; identifier sesi berubah dan `admin_id`/`admin_name` dihapus. |
| Setelah logout: kedua URL masuk dan dashboard | PASS | 302 ke login, login 200; tidak ada loop. |
| Replay identifier sesi sebelum logout | PASS | Entry dan dashboard tetap diarahkan ke login. |
| Empat halaman publik | PASS | `/`, `/profil`, `/berita`, `/jadwal-ibadah` menghasilkan 200 dan tidak memuat tautan admin. |
| `php spark routes` | PASS | Route baru terdaftar; route lama, filter `adminAuth`, dan filter `csrf` tetap terdaftar. |
| `php -l` | PASS | `app/Config/Routes.php`, `app/Controllers/AdminAuth.php`, `app/Filters/AdminAuthFilter.php` tanpa kesalahan sintaks. |
| `node --check tests/auth/admin-entry-http.mjs` | PASS | Runner valid secara sintaks. |
| `git diff --check` | PASS | Tidak ada kesalahan whitespace; Git hanya memberi pemberitahuan LF/CRLF pada berkas working tree. |
| Login berhasil dengan kredensial akun uji nyata → buka `/admin` → logout | BLOCKED | Kredensial akun uji aman belum tersedia. Sesi sintetis menguji perilaku setelah sesi aktif, bukan keberhasilan verifikasi kata sandi melalui POST login. |
| PHPUnit | BLOCKED | `vendor/bin/phpunit` tidak tersedia; suite tidak dijalankan. |
| Deployment dengan indexPage kosong/subfolder, Apache/HTTPS produksi | BLOCKED | Lingkungan tersebut tidak tersedia untuk verifikasi langsung; konfigurasi tidak diubah. Dukungan URL didasarkan pada penggunaan `site_url()`. |
| Bookmark/shortcut pada HP fisik dan UAT operator | BLOCKED | Perangkat/operator tidak tersedia. Panduan berikut belum diuji pada perangkat nyata. |

Tidak dilakukan audit visual/browser karena task ini tidak mengubah antarmuka. Skill Impeccable dipakai sesuai lingkup mempertahankan desain. Loader context salah menafsirkan target `/admin` sebagai direktori; PRODUCT.md dan DESIGN.md dibaca langsung dari root proyek, tanpa membuat ulang atau menyinkronkan dokumen desain.

## Bookmark dan shortcut HP

Simpan alamat masuk **`/admin`** pada origin website yang benar. Pada konfigurasi lokal yang diuji, alamat eksplisitnya adalah `http://localhost:8080/index.php/admin`. Jika hosting memakai rewrite dan `indexPage` kosong, gunakan alamat website diikuti `/admin`. Jangan menebak domain yang belum ditetapkan; pertahankan subfolder jika website dipasang dalam subfolder.

Redirect dapat membuat browser menyimpan alamat login/dashboard sebagai bookmark. Setelah menambahkan bookmark atau shortcut, periksa dan ubah URL yang disimpan menjadi alamat **entry `/admin`** (atau `/index.php/admin` sesuai konfigurasi), bukan alamat hasil redirect.

1. **Bookmark desktop:** buka alamat masuk, tekan Ctrl+D (Cmd+D pada Mac), beri nama “Admin Jemaat Leahari”, lalu edit URL bookmark ke alamat entry di atas.
2. **Android/Chrome:** buka alamat masuk lewat URL yang dapat dijangkau HP. Menu ⋮ → Bookmark untuk menyimpan bookmark, atau “Tambahkan ke layar utama” untuk shortcut. Periksa URL bila browser menyediakan penyuntingan. Bila shortcut justru menyimpan hasil redirect dan tidak bisa diedit, gunakan bookmark yang URL-nya telah dikoreksi sebagai akses utama.
3. **iPhone/Safari:** buka alamat masuk → Bagikan → “Tambah Penanda” untuk bookmark, atau “Tambah ke Layar Utama” untuk shortcut. Koreksi URL entry bila bisa diedit. Bila shortcut menyimpan hasil redirect tanpa opsi edit URL, gunakan bookmark yang sudah dikoreksi.

Nama menu dapat berbeda menurut versi browser. `localhost` pada HP menunjuk HP sendiri, sehingga alamat lokal komputer di atas bukan alamat akses HP. Untuk HP, gunakan alamat deployment/LAN yang memang sudah disiapkan pengelola dan konsisten dengan baseURL aplikasi. Task ini tidak mengubah konfigurasi jaringan/server.

Bookmark/shortcut tidak menyimpan hak akses: ketika sesi tidak valid, operator tetap masuk melalui login; ketika sesi aktif, entry mengarahkan ke dashboard. Gunakan tombol **Keluar** untuk mengakhiri sesi.

## Keterbatasan dan tindak lanjut

- Definisi sesi valid mengikuti implementasi sekarang: nilai `admin_id` yang truthy. Task ini tidak menambahkan pemeriksaan akun ke database atau mengubah kebijakan autentikasi.
- Verifikasi end-to-end autentikasi nyata perlu akun uji yang disediakan pengelola: buka entry sebelum login, login, buka ulang entry saat sesi aktif, logout, dan pastikan entry/dashboard kembali ke login.
- Uji URL pada deployment final serta bookmark/shortcut di HP operator sebelum serah terima. Tidak mengklaim hasil lokal sebagai kesiapan hosting/UAT.
- Tunggu review pengguna; jangan commit otomatis.
