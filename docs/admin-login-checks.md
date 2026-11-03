# Admin login: tampilan dan regresi

Pemeriksaan lokal: 3 November 2026, PHP development server `http://localhost:8080`, Chrome headless, tinggi viewport 900 px.

## Implementasi dan temuan awal

- `app/Config/Routes.php`: GET `/admin/login` ke `AdminAuth::loginForm`, POST ke `AdminAuth::login` dengan filter CSRF. Tidak ada nama route; form memakai reverse routing `url_to('AdminAuth::login')`.
- `app/Views/admin/login.php` adalah dokumen standalone, bukan turunan layout dashboard. Bootstrap 5.3.8 dan font dimuat lokal. Tidak perlu mengubah layout halaman lain.
- Handler tetap memakai `email` dan `password`, `withInput()`, serta flash `error` yang diteruskan controller ke view. Tidak ada error validasi per-field; kegagalan kredensial ditampilkan sebagai alert umum dan diasosiasikan ke kedua input, tanpa menyebut akun mana yang ada.
- Remember Me dan reset sandi dihilangkan karena backend/route belum mendukungnya. Tidak ada tautan pendaftaran. Tombol tampil/sembunyikan sandi yang sudah ada dipertahankan.
- Logo tersedia sebagai `public/assets/images/logo/LogoGPM.png`; referensi JPG lama tidak tersedia. Foto tetap memakai `public/assets/images/gereja/Gereja-tampak-samping.jpeg`. Aset tidak dipindahkan ke `assets/img/`; semua URL memakai helper.
- Font Cormorant Garamond dan DM Sans tersedia lokal, bukan placeholder. Tidak ada placeholder logo/foto. Empat variabel warna `--jl-*` masih placeholder yang perlu dikonfirmasi tim.
- `docs/PRD.md` tidak ada. Referensi aktual adalah `docs/PRD_Website_Jemaat_Leahari_v4.1_Baseline_Internal_FINAL.md`: baseline internal, bukan bukti pengesahan tertulis mitra. Referensi PRD v3 di PRODUCT.md merupakan konteks lama dan tidak diubah dalam task ini.

## File

- View: grid Bootstrap 50/50, form kiri maksimal 400 px, foto kanan tersembunyi di bawah 768 px, copy Indonesia, CSRF, email lama, password selalu kosong pada respons server, alert aksesibel.
- `public/assets/css/admin-login.css`: palet scoped, font lokal, kontrol 52 px, tombol toggle 44 px, focus ring, pembungkusan error, overlay foto, reduced motion.
- `public/assets/js/admin-login.js`: spinner dan pencegahan pengiriman ganda; pulihkan tombol dan sembunyikan sandi pada `pageshow`. Form tetap memakai POST native tanpa JavaScript.
- `tests/auth/admin-login-browser.mjs`: pemeriksaan Chrome CDP dengan Node bawaan, tanpa dependensi/bundler baru. Screenshot disimpan di `writable/login-checks/`.
- `tests/auth/admin-auth-http.mjs`: uji token CSRF kosong dan salah pada login. Ekspektasi logout anonim diperbaiki: filter autentikasi berjalan sebelum CSRF, sehingga uji CSRF logout dilakukan oleh suite entry memakai sesi aktif.

## Matriks responsif

| Lebar | Normal | Error dari POST | Foto | Overflow horizontal | Kontrol >=44 px |
| --- | --- | --- | --- | --- | --- |
| 320 px | Lulus | Lulus | Tersembunyi | Tidak ada | Lulus |
| 360 px | Lulus | Lulus | Tersembunyi | Tidak ada | Lulus |
| 390 px | Lulus | Lulus | Tersembunyi | Tidak ada | Lulus |
| 430 px | Lulus | Lulus | Tersembunyi | Tidak ada | Lulus |
| 768 px | Lulus | Lulus | Kanan | Tidak ada | Lulus |
| 1440 px | Lulus | Lulus | Kanan | Tidak ada | Lulus |

Email error memakai identitas acak yang panjang untuk memeriksa overflow. Pengukuran menunggu CSS, font, dan dokumen selesai dimuat; tidak memakai `overflow-x: hidden` untuk menutupi masalah. Screenshot desktop, mobile error 390 px, dan tablet error 768 px juga diperiksa secara visual.

## Hasil regresi

- Lulus: PHP lint view, pemeriksaan sintaks JavaScript, `git diff --check`.
- Lulus: GET login, POST gagal dengan pesan generik, email dipertahankan, password uji tidak ada pada HTML respons, rate limit tetap berfungsi.
- Lulus: token CSRF ada; login menolak token kosong maupun salah dengan HTTP 403 untuk permintaan AJAX. Logout dengan sesi aktif menolak token kosong.
- Lulus: `/admin` dan `/index.php/admin` anonim menuju login; sesi aktif sintetis menuju dashboard; GET login dengan sesi aktif menuju dashboard.
- Lulus: POST logout dengan CSRF valid merotasi sesi dan menghapus status admin; replay sesi lama tidak mendapat akses kembali.
- Lulus: empat halaman publik tetap HTTP 200, modul admin yang sudah ada tetap dapat diakses dengan sesi uji.
- Lulus: Tab melalui email, sandi, toggle, Masuk, dan tautan publik; masing-masing mempunyai indikator fokus. Input error memakai `aria-describedby` dan `aria-invalid`.
- Lulus: spinner, submit ganda diblokir, pemulihan `pageshow`, reduced motion, POST gagal tanpa JavaScript.
- **Belum diuji: login dengan kredensial admin valid.** Tidak ada kredensial uji yang diberikan dan tidak dibuat akun baru. Sesi sintetis menguji redirect/logout, bukan `password_verify`. UAT dengan akun resmi masih diperlukan untuk menutup acceptance ini.
- Audit screen reader penuh dan perangkat fisik tidak dijalankan. Pengujian viewport menggunakan Chrome desktop, bukan emulasi perangkat fisik.

## Menjalankan ulang

Jalankan `php spark serve --host localhost --port 8080`, lalu:

```powershell
php -l app/Views/admin/login.php
node --check public/assets/js/admin-login.js
node tests/auth/admin-auth-http.mjs
node tests/auth/admin-entry-http.mjs
$env:CHROME_PATH = 'PATH_TO_LOCAL_CHROME_EXE'
node tests/auth/admin-login-browser.mjs
```

Uji memakai identitas acak yang tidak ada; handler dapat mencatat percobaan gagal sementara sesuai mekanisme rate-limit yang sudah ada. Tidak ada perubahan skema, akun, atau konten. Tidak ada autentikasi controller, route, maupun halaman publik yang diubah.
