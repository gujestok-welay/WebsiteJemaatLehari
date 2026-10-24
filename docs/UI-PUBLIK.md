# Fondasi UI publik Jemaat Leihari

Implementasi berdasarkan `docs/PRD.md` (draft), dengan lingkup mengikuti permintaan empat halaman publik. Framework bawaan terverifikasi CodeIgniter **4.7.5**. Menggunakan PHP Views dan Bootstrap **5.3.8** lokal; tanpa database, admin, login, atau build frontend.

## Menjalankan

1. Gunakan PHP 8.2+ dengan `intl` dan `mbstring`.
2. Jalankan `composer install` dari root proyek.
3. Jalankan `php spark serve`.
4. Buka `http://localhost:8080/`, `/profil`, `/berita`, dan `/jadwal-ibadah`.

Untuk host/port berbeda, atur `app.baseURL` pada `.env` lokal mengikuti contoh `env`. Jangan commit kredensial. Produksi harus menggunakan document root `public/`. Tidak ada konfigurasi database yang diperlukan oleh halaman ini.

## File implementasi

- `app/Config/Routes.php`: empat route GET eksplisit.
- `app/Controllers/Home.php`: menyiapkan data dan merender halaman.
- `app/Libraries/PublicSampleContent.php`: sumber tunggal tiga berita dan tiga jadwal dummy; tidak melakukan persistence.
- `app/Views/layouts/publik.php`: dokumen HTML, aset lokal, skip link, pemberitahuan dummy, slot konten.
- `app/Views/partials/navbar.php`: navigasi responsif dan penanda halaman aktif.
- `app/Views/partials/footer.php`: penutup dan navigasi kembali.
- `app/Views/publik/beranda.php`: pengantar, berita, jadwal contoh, akses profil.
- `app/Views/publik/profil.php`: ruang deskripsi, sejarah, Majelis, serta kontak tanpa fakta rekaan.
- `app/Views/publik/berita.php`: daftar berita dengan isi yang dapat dibuka melalui elemen HTML `details`.
- `app/Views/publik/jadwal.php`: daftar lengkap jadwal dan filter jenis ibadah.
- `public/assets/css/publik.css`: token palet, tipografi, responsivitas, fokus keyboard, reduced motion, dan cetak.
- `public/assets/js/publik.js`: progressive enhancement filter jadwal dengan status untuk pembaca layar.
- `public/assets/vendor/bootstrap/bootstrap.min.css` dan `bootstrap.bundle.min.js`: distribusi Bootstrap 5.3.8, lisensi MIT pada header aset.
- `tests/unit/PublicPagesTest.php`: regresi respons halaman, pemberitahuan dummy, struktur navigasi, fallback jadwal, dan profil tanpa data resmi rekaan.
- `composer.lock`: hasil instalasi dependensi untuk versi yang dapat direproduksi; `composer.json` dan framework tidak diubah.
- `.gitignore`: mencegah dependensi, pengaturan rahasia, dan hasil pemeriksaan lokal ikut ter-commit.

## Arah visual dan catatan skill

Skill: `.agents/skills/design-taste-frontend/SKILL.md`.
Direction: **Type and nothing else**, seed **250235948**; tidak ada redraw.
Palet dipertahankan dari brief: ungu `#663F72`, emas `#D8BD72`, putih. Georgia untuk judul dan Arial untuk teks, tanpa permintaan font eksternal. Teks utama 18px, permukaan datar, tanpa gradient/glass/kartu dekoratif.

Inventaris yang dapat digunakan dengan jujur: nama Jemaat Leihari, konteks Maluku, Bahasa Indonesia, empat tujuan navigasi, kebutuhan berita dan jadwal, zona WIT, pembaca semua usia, palet usulan, serta status data belum disahkan. Tidak ada foto, logo, nama pengurus, sejarah, atau kontak resmi di aset proyek. Tidak mengarang makna nama, tahun berdiri, statistik, atau identitas personel untuk melengkapi konsep.

Ide: **Nama Jemaat Leihari menjadi masthead warta digital yang mempertemukan berita dan jadwal dalam satu ritme baca.** Kekhasan konsep masih terbatas karena identitas visual dan materi resmi belum tersedia.

Argumentasi: pengunjung mengenali tujuan situs melalui judul dan pengantar; menemukan berita lewat daftar ringkasan; melihat contoh waktu ibadah dalam blok jadwal; mengenal struktur profil lewat tautan yang jelas. Halaman turunan menyediakan isi masing-masing tanpa memerlukan akun.

Copy deck sebelum markup: “Jemaat Leihari” / “Ruang informasi jemaat di Maluku untuk mengikuti berita, mengenal jemaat, dan menemukan jadwal ibadah.” / “Lihat jadwal ibadah”; bagian lanjutan “Berita jemaat”, “Jadwal ibadah”, “Mengenal jemaat”. Judul halaman turunan “Profil jemaat”, “Berita jemaat”, “Jadwal ibadah”. Semua berita dan jadwal diberi label dummy.

Live thing: filter jenis ibadah, status jumlah hasil, seluruh jadwal tersedia tanpa JavaScript. Navigasi mobile juga memiliki fallback `noscript`.

Penyesuaian skill terhadap brief: navbar tetap dibuat karena diminta; tidak memakai scroll scene, entrance berulang, atau video otomatis agar akses informasi tenang dan hemat. Inspector dijalankan dengan `--allow palette-purple,still-page`: warna berasal dari brief dan gerak dibatasi pada umpan balik tombol. Tidak ada foto stok/gambar generated, gambar pengganti, video, atau aset yang memerlukan kredit. Pencarian `assets.mjs "Leihari Maluku"` tidak menemukan aset yang dapat digunakan. **Kebutuhan skill minimal empat foto belum terpenuhi**, tetap dilaporkan sebagai FAIL, bukan disembunyikan.

## Hasil validasi yang telah dijalankan

- `php spark routes`: empat route publik terdaftar pada CodeIgniter 4.7.5.
- `vendor/bin/phpunit tests/unit --no-coverage`: **5 tes, 31 assertion, lulus**, mencakup dua tes HealthTest bawaan. Suite database/session dan coverage tidak dijalankan.
- `php -l`: semua file PHP implementasi valid.
- `lint.mjs public/assets/css/publik.css public/assets/js/publik.js`: **0 FAIL, 0 WARN**. Pemindaian lebih luas juga menandai monospace dan border di stylesheet error bawaan CI, yang tidak diubah.
- Browser Chrome headless: 24 kombinasi (empat halaman × 320, 360, 390, 430, 768, 1440 px), tanpa horizontal overflow, satu h1 dan satu tautan aktif per halaman, tidak ada aset HTTP gagal. Menu mobile terbuka; filter Keluarga menghasilkan satu jadwal dan Semua menghasilkan tiga; navigasi serta tiga jadwal terlihat tanpa JavaScript. Tidak ada exception JavaScript.
- Inspector skill pada semua halaman di 1440, 768, 390 px. Bukan kelulusan penuh: setiap halaman masih memiliki **FAIL `few-pictures`** dan **WARN `no-footage`**. Tidak ada kegagalan kontras/overflow/judul yang dilaporkan. Pengecualian warna dan gerak seperti dijelaskan di atas.
- Screenshot setiap viewport dan lembar halaman diperiksa secara visual. Artefak lokal berada di `.taste-skill/final-*/`, ringkasan browser di `.taste-skill/ui-checks.json`; tidak disertakan dalam Git.

## Kritik visual terakhir

ROUND 2
Seen in one second: nama Jemaat Leihari pada bidang ungu.
Idea (one sentence): nama jemaat menjadi masthead warta digital untuk berita dan jadwal.
Live thing: filter jenis ibadah dengan fallback daftar lengkap.
Layout words by section: hero=type field / berita=editorial rows / jadwal=split / profil=wide paragraph / footer=masthead.
Scores: design 6.5 · usability 8 · creativity 6 · content 5 → overall **6.0** setelah cap.
Caps applied: FAIL foto membatasi overall 6; tanpa foto membatasi design 6.5; konsep masih dapat dipakai jemaat lain membatasi kreativitas; isi dummy membatasi penilaian substansi.
Top three problems (most visible first), each with the screenshot it shows in and the fix:
1. Tidak ada dokumentasi nyata (semua lembar): tambahkan foto resmi berizin saat tersedia, tanpa mengklaim foto tempat lain sebagai jemaat.
2. Profil belum menyampaikan sejarah dan kontak nyata (`final-profil/*`): lengkapi setelah validasi data, bukan menambah paragraf rekaan.
3. Identitas masih bertumpu pada nama dan warna (`final-beranda/*`): lengkapi logo/foto serta bahasa visual resmi setelah disahkan. Koreksi yang telah dilakukan: mengurangi ukuran teks menjadi lima peran dominan dan memindahkan metadata ke bawah judul; status filter berada setelah daftar.

## Pengujian ulang manual

1. Buka empat route; periksa navbar aktif dan semua tautan beranda/footer.
2. Pada HP, buka/tutup Menu, gunakan keyboard Tab dan skip link; pastikan fokus terlihat.
3. Buka/tutup tiap “Baca isi contoh” di Berita.
4. Di Jadwal Ibadah pilih Minggu, Keluarga, dan Semua. Periksa jumlah serta jenis hasil.
5. Matikan JavaScript: navigasi dan seluruh jadwal harus tetap tersedia, berita tetap dapat dibuka.
6. Uji zoom browser 200%, reduced motion, dan cetak jadwal; lanjutkan UAT bersama pengguna semua usia. Pengujian pembaca layar/perangkat fisik belum dilakukan.

## Sebelum publikasi

Ganti seluruh contoh berita, tanggal terbit, jadwal/tanggal/jam/lokasi/keterangan; lengkapi profil, sejarah, susunan Majelis, alamat, kontak, serta logo/foto berizin. Banner dummy dan `noindex,nofollow` sengaja dipasang pada pratinjau; hapus hanya setelah seluruh konten dan kesiapan rilis diperiksa. Label contoh berada di view dan data, sehingga tidak cukup hanya menghapus banner. Jangan menganggap tanggal contoh sebagai jadwal aktual atau otomatis mendatang.
