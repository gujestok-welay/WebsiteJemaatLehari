# Fondasi UI publik Jemaat Leahari

Implementasi memiliki halaman Beranda, Profil, Berita, Galeri, dan Jadwal Ibadah. Framework bawaan terverifikasi CodeIgniter **4.7.5**. Menggunakan PHP Views dan Bootstrap **5.3.8** lokal. Beranda dan halaman Berita menampilkan berita berstatus `Terbit` dari database jika koneksi database dikonfigurasi; konten contoh dipakai untuk berita saat database belum dikonfigurasi. Galeri hanya menampilkan kegiatan berstatus `Terbit` dan tidak membuat konten contoh. Halaman Profil membaca konten dan angka yang dikelola dari panel admin; nilai awal yang ada tetap merupakan konten prototipe dan belum otomatis menjadi fakta resmi.

Detail berita tersedia pada `/berita/{slug}` hanya untuk berita berstatus `Terbit`. Slug yang tidak ditemukan atau tidak publik menghasilkan halaman 404 dengan tautan kembali ke daftar berita. Gambar unggahan hanya disajikan melalui route publik ketika masih terhubung ke berita terbit.

## Berita dari sumber eksternal

Media Center dapat memilih jenis **Berita dari sumber lain**, menempelkan URL HTTP(S), lalu meminta sistem mengambil judul, nama sumber, ringkasan metadata, dan kandidat gambar dari halaman. Ringkasan kandidat hanya mengisi draf isian dan perlu ditinjau sebelum terbit. Gambar ditampilkan sebagai pratinjau; operator dapat mengonfirmasi izin dan menyimpannya sebagai cover lokal, atau mengunggah cover lain. Bila metadata tidak tersedia, judul, ringkasan, dan sumber tetap dapat diisi manual. Pengambilan otomatis hanya mengikuti URL publik tanpa redirect; alamat IP langsung, domain privat, kredensial URL, dan port nonstandar ditolak. Cover eksternal dibatasi 10 MB, diverifikasi tipe dan isi gambarnya, lalu disimpan di luar direktori publik.

Detail publik menampilkan atribusi sumber, ringkasan yang sudah ditinjau, cover lokal yang dipilih, dan tautan ke artikel asli; isi artikel sumber tidak disalin ke website. Berita eksternal tanpa cover yang dipilih tidak memakai gambar fallback berita internal.

Migrasi `2026-11-12-000001_AddExternalNewsAttribution` menambahkan jenis berita dan atribusi sebagai kolom nullable/default tanpa menghapus berita lama; entri lama tetap bertipe internal. Fitur ini adalah perluasan implementasi lokal dari berita eksternal P1 pada PRD, bukan bukti persetujuan mitra atau perubahan baseline P0.

Galeri kegiatan tersedia pada `/galeri` dan `/galeri/{slug}`. Kegiatan Draft atau Arsip serta gambarnya tidak tersedia melalui route publik. Beranda menampilkan paling banyak tiga kegiatan terbit yang dipilih secara khusus dari panel admin. Pengelolaan galeri tersedia pada `/admin/galeri`; unggahan gambar disimpan di luar direktori publik dan disajikan melalui route yang memeriksa status publikasi.

## Editor Profil publik

- Panel `admin/profil` mengelola seluruh teks, angka, label, tautan bagian, dan daftar berulang di halaman Profil; menu utama dan footer dikelola terpisah.
- Konten halaman disimpan pada kolom `profil_jemaat.page_content`. Migrasi `2026-11-05-000001_AddPublicProfilePageContent` menambahkan kolom teks nullable tanpa mengubah isi kolom profil yang sudah ada.
- Dashboard Profil Jemaat menampilkan kartu ringkasan untuk Gambaran Umum Wilayah, Jumlah KK dan Jiwa, Pelayanan Peribadahan, Peta Pelayanan, serta editor teks Profil dan Majelis. Setiap tindakan Kelola membuka halaman khusus modul, dengan navigasi kembali, status penyimpanan, dan perlindungan perubahan yang belum disimpan. Editor Wilayah, Sensus, dan Konten Profil membagi isian panjang menjadi langkah yang dapat dipilih langsung. Jadwal ibadah tetap dikelola melalui `/admin/jadwal`.
- Pada editor Konten Profil, isian informasi dan data dipisahkan dari pengaturan judul, label statistik, nama tautan, dan nama bagian Majelis. Langkah pengaturan tersebut sementara tidak ditampilkan, tetapi nilainya tetap disertakan saat formulir disimpan agar tidak hilang.
- Kartu Gambaran Umum Wilayah, Jumlah KK dan Jiwa, serta Pelayanan Peribadahan memakai ilustrasi latar yang sesuai modul dengan lapisan gelap dan bayangan teks agar tetap terbaca. Label judul berwarna, status berbentuk pil, ringkasan, dan tombol putih mengikuti hierarki visual yang konsisten; pada HP kartu tersusun satu kolom dengan ukuran teks dan kontrol yang disesuaikan. Gambar latar tersebut merupakan ilustrasi, bukan dokumentasi lokasi atau kegiatan jemaat.
- Wilayah dan sensus disimpan pada kolom nullable `territory_content` dan `census_content`; migrasi `2026-11-06-000001_AddProfileManagementModules` menambahkan kolom tersebut beserta `service_map_path`, `service_map_title`, dan `service_map_alt` tanpa menghapus atau menulis ulang data lama. Konten publik menggunakan data modul bila tersedia dan mempertahankan fallback konten Profil lama.
- Total jiwa dan kelompok usia terbesar dihitung dari kategori jumlah yang tersimpan. Nilai angka referensi hanya ditampilkan jika memang ada di data; tidak ada angka contoh yang dikunci dalam kode.
- Modal peta menyimpan judul bagian dan alt text, serta menerima JPG, PNG, atau WEBP hingga 5 MB. File lama tetap menjadi sumber publik sampai unggahan baru lolos validasi dan referensinya berhasil disimpan.
- Modal menampilkan validasi dekat input, status penyimpanan dan notifikasi, serta meminta konfirmasi jika ditutup dengan perubahan yang belum disimpan. Pada layar kecil, formulir menggunakan tata letak satu kolom.
- Baris daftar baru dimulai kosong agar tidak tanpa sengaja menggandakan informasi. Total konten yang disimpan dibatasi 60 KB agar sesuai dengan kapasitas kolom database.
- Riwayat dan visi-misi tetap dikelola pada form Data Jemaat. Alamat, kontak, dan tautan Maps yang sudah diisi ditampilkan di bagian lokasi dan kontak.
- Kartu Majelis pada Beranda dan Profil menggunakan data anggota periode aktif yang sama. Nama, jabatan, dan foto dikelola dari panel `admin/profil`; penayangan kartu tidak aktif secara default dan hanya dapat diaktifkan dari editor setelah izin publikasi dipastikan.
- Foto Majelis mengisi kartu dengan pemotongan `cover`; deteksi wajah browser, jika tersedia, mengatur titik fokus agar wajah tetap masuk bingkai. Browser tanpa dukungan deteksi memakai fokus aman dekat bagian atas foto.
- Jika periode belum tersedia, buat periode terlebih dahulu, tambahkan anggotanya, lalu aktifkan opsi publikasi pada bagian Majelis dan kontak. Tanpa anggota yang dipublikasikan, halaman menampilkan keterangan kosong dan tidak menampilkan nama contoh.
- Nilai awal editor menyalin isi prototipe yang sebelumnya tampil pada halaman Profil. Verifikasi setiap informasi sebelum menggunakannya sebagai informasi resmi.

## Menjalankan

1. Gunakan PHP 8.2+ dengan `intl` dan `mbstring`.
2. Jalankan `composer install` dari root proyek.
3. Jalankan `php spark serve`.
4. Buka `http://localhost:8080/`, `/profil`, `/berita`, dan `/jadwal-ibadah`.

Untuk host/port berbeda, atur `app.baseURL` pada `.env` lokal mengikuti contoh `env`. Jangan commit kredensial. Produksi harus menggunakan document root `public/`. Konfigurasikan database untuk menampilkan dan mengelola berita dinamis; jika belum dikonfigurasi, setiap kartu berita contoh diberi badge “Contoh”. Naskah berita database yang mengandung penanda “Lorem ipsum” juga diberi badge contoh; periksa dan perbarui konten melalui panel admin sebelum publikasi.

Beranda hanya menampilkan jadwal yang belum dimulai menurut zona waktu WIT, diurutkan berdasarkan tanggal dan waktu terdekat. Jadwal lampau tidak ditampilkan dalam sorotan maupun daftar ringkas beranda; jika tidak ada jadwal mendatang, beranda menyampaikan keadaan kosong. Berita dan jadwal ditempatkan setelah hero sebelum bagian Majelis agar informasi inti lebih mudah ditemukan di ponsel.

## File implementasi

- `app/Config/Routes.php`: route publik eksplisit untuk halaman utama, detail dan gambar berita, serta route metadata berita eksternal yang hanya tersedia bagi admin.
- `app/Controllers/Home.php`: menyiapkan data dan merender halaman; mengambil berita publik dari `BeritaModel` saat database dikonfigurasi dan membaca konten Profil dari model profil jemaat. Detail dan gambar berita hanya tersedia untuk berita terbit.
- `app/Controllers/AdminBerita.php` dan `app/Libraries/ExternalNewsMetadata.php`: mengelola atribusi sumber, mengambil metadata halaman publik secara terbatas, dan menyediakan isian manual bila metadata gagal.
- `app/Libraries/PublicSampleContent.php`: sumber berita contoh bila database belum dikonfigurasi dan tiga jadwal contoh; tidak melakukan persistence.
- `app/Views/layouts/publik.php`: dokumen HTML, aset lokal, skip link, pemberitahuan dummy, slot konten.
- `app/Views/partials/navbar.php`: navigasi responsif dengan penanda halaman aktif dan dropdown Profil ke ringkasan Profil Jemaat, Peta Pelayanan, Majelis Jemaat, Gambaran Umum Wilayah Pelayanan, Jumlah Kepala Keluarga dan Jiwa, serta Pelayanan Peribadahan.
- `app/Views/partials/footer.php`: penutup dan navigasi kembali.
- `app/Views/publik/beranda.php`: pengantar, berita terbaru, jadwal mendatang, akses profil; bagian berita/jadwal mengikuti hero sebelum bagian Majelis.
- `app/Views/publik/profil.php`: profil wilayah, statistik, Majelis, kontak, dan peribadahan dari konten yang dapat dikelola melalui panel admin.
- `app/Views/publik/berita.php`: daftar berita terbit dari database atau fallback contoh; filter kategori hanya tersedia pada konten contoh yang memiliki kategori.
- `app/Views/publik/berita_detail.php` dan `app/Views/publik/berita_tidak_ditemukan.php`: isi berita publik, atribusi dan tautan berita eksternal, serta halaman 404 dengan navigasi kembali ke daftar berita.
- `app/Views/publik/jadwal.php`: daftar lengkap jadwal dan filter jenis ibadah.
- `public/assets/css/publik.css`: token palet, tipografi, responsivitas, fokus keyboard, reduced motion, dan cetak.
- `public/assets/js/publik.js`: progressive enhancement filter jadwal dan carousel Majelis dengan pergantian otomatis 5 detik, kontrol jeda/navigasi, dukungan reduced motion, dan seluruh kartu tetap tersedia tanpa JavaScript.
- `public/assets/vendor/bootstrap/bootstrap.min.css` dan `bootstrap.bundle.min.js`: distribusi Bootstrap 5.3.8, lisensi MIT pada header aset.
- `tests/unit/PublicPagesTest.php`, `tests/unit/PublicNewsDetailTest.php`, dan `tests/unit/ExternalNewsMetadataTest.php`: regresi halaman publik, atribusi berita eksternal, pemrosesan metadata dan validasi URL.
- `e2e/external-news.spec.js`: pengujian alur Media Center dari formulir hingga detail berita publik.
- `composer.lock`: hasil instalasi dependensi untuk versi yang dapat direproduksi; `composer.json` dan framework tidak diubah.
- `.gitignore`: mencegah dependensi, pengaturan rahasia, dan hasil pemeriksaan lokal ikut ter-commit.

## Arah visual dan catatan skill

Skill: `.agents/skills/design-taste-frontend/SKILL.md`.
Direction: **Type and nothing else**, seed **250235948**; tidak ada redraw.
Palet dipertahankan dari brief: ungu `#663F72`, emas `#D8BD72`, putih. Georgia untuk judul dan Arial untuk teks, tanpa permintaan font eksternal. Teks utama 18px. Kartu berita beranda memakai komponen Bootstrap 5 dengan elevasi ringan dan gerak mengambang halus; animasi berhenti saat diarahkan atau difokuskan dan dinonaktifkan untuk preferensi reduced motion. Tidak memakai glass effect atau gambar dekoratif.

Inventaris yang dapat digunakan dengan jujur: nama Jemaat Leahari, konteks Maluku, Bahasa Indonesia, empat tujuan navigasi, kebutuhan berita dan jadwal, zona WIT, pembaca semua usia, palet usulan, serta status data belum disahkan. Tidak ada foto, logo, nama pengurus, sejarah, atau kontak resmi di aset proyek. Tidak mengarang makna nama, tahun berdiri, statistik, atau identitas personel untuk melengkapi konsep.

Ide: **Nama Jemaat Leahari menjadi masthead warta digital yang mempertemukan berita dan jadwal dalam satu ritme baca.** Kekhasan konsep masih terbatas karena identitas visual dan materi resmi belum tersedia.

Argumentasi: pengunjung mengenali tujuan situs melalui judul dan pengantar; menemukan berita lewat daftar ringkasan; melihat contoh waktu ibadah dalam blok jadwal; mengenal struktur profil lewat tautan yang jelas. Halaman turunan menyediakan isi masing-masing tanpa memerlukan akun.

Copy deck sebelum markup: “Jemaat Leahari” / “Ruang informasi jemaat di Maluku untuk mengikuti berita, mengenal jemaat, dan menemukan jadwal ibadah.” / “Lihat jadwal ibadah”; bagian lanjutan “Berita jemaat”, “Jadwal ibadah”, “Mengenal jemaat”. Judul halaman turunan “Profil jemaat”, “Berita jemaat”, “Jadwal ibadah”. Semua berita dan jadwal diberi label dummy.

Live thing: filter jenis ibadah, status jumlah hasil, seluruh jadwal tersedia tanpa JavaScript. Navigasi memakai drawer melengkung dengan tombol ikon pada layar di bawah 992 px, dan tetap tersedia melalui fallback `noscript`.

Penyesuaian skill terhadap brief: navbar tetap dibuat karena diminta; tidak memakai scroll scene, entrance berulang, atau video otomatis agar akses informasi tenang dan hemat. Inspector dijalankan dengan `--allow palette-purple,still-page`: warna berasal dari brief dan gerak dibatasi pada umpan balik tombol. Tidak ada foto stok/gambar generated, gambar pengganti, video, atau aset yang memerlukan kredit. Pencarian `assets.mjs "Leahari Maluku"` tidak menemukan aset yang dapat digunakan. **Kebutuhan skill minimal empat foto belum terpenuhi**, tetap dilaporkan sebagai FAIL, bukan disembunyikan.

## Hasil validasi yang telah dijalankan

- `php spark routes`: enam route GET publik terdaftar pada CodeIgniter 4.7.5.
- `php vendor/bin/phpunit --no-coverage`: **155 tes, 904 assertion, lulus**, termasuk tes detail berita dan proteksi draft/arsip.
- `php -l`: semua file PHP implementasi valid.
- `lint.mjs public/assets/css/publik.css public/assets/js/publik.js`: **0 FAIL, 0 WARN**. Pemindaian lebih luas juga menandai monospace dan border di stylesheet error bawaan CI, yang tidak diubah.
- Browser Chrome headless: 24 kombinasi (empat halaman × 320, 360, 390, 430, 768, 1440 px), tanpa horizontal overflow, satu h1 dan satu tautan aktif per halaman, tidak ada aset HTTP gagal. Menu mobile terbuka; filter Keluarga menghasilkan satu jadwal dan Semua menghasilkan tiga; navigasi serta tiga jadwal terlihat tanpa JavaScript. Tidak ada exception JavaScript.
- Inspector skill pada semua halaman di 1440, 768, 390 px. Bukan kelulusan penuh: setiap halaman masih memiliki **FAIL `few-pictures`** dan **WARN `no-footage`**. Tidak ada kegagalan kontras/overflow/judul yang dilaporkan. Pengecualian warna dan gerak seperti dijelaskan di atas.
- Screenshot setiap viewport dan lembar halaman diperiksa secara visual. Artefak lokal berada di `.taste-skill/final-*/`, ringkasan browser di `.taste-skill/ui-checks.json`; tidak disertakan dalam Git.

## Kritik visual terakhir

ROUND 2
Seen in one second: nama Jemaat Leahari pada bidang ungu.
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

1. Buka lima halaman publik; periksa navbar aktif dan semua tautan beranda/footer.
2. Pada desktop, arahkan pointer ke Profil untuk membuka dropdown; pada HP, buka/tutup Menu dan dropdown Profil dengan sentuhan. Ikuti tautan bagian, gunakan keyboard Tab dan skip link, lalu pastikan fokus terlihat.
3. Buka/tutup tiap “Baca isi contoh” di Berita.
4. Di Jadwal Ibadah pilih Minggu, Keluarga, dan Semua. Periksa jumlah serta jenis hasil.
5. Matikan JavaScript: navigasi dan seluruh jadwal harus tetap tersedia, berita tetap dapat dibuka.
6. Uji zoom browser 200%, reduced motion, dan cetak jadwal; lanjutkan UAT bersama pengguna semua usia. Pengujian pembaca layar/perangkat fisik belum dilakukan.
7. Pada Galeri, periksa empty state, detail kegiatan, status Draft/Arsip, tautan video eksternal, dan lebar layar ponsel. Pada panel admin, uji unggah gambar, batas jumlah/ukuran, edit, arsip, dan pemulihan.

## Sebelum publikasi

Ganti seluruh contoh berita, tanggal terbit, jadwal/tanggal/jam/lokasi/keterangan; lengkapi profil, sejarah, susunan Majelis, alamat, kontak, serta logo/foto berizin. Pemberitahuan pratinjau pada layout publik sudah dihapus setelah pemeriksaan konten dan kesiapan rilis dikonfirmasi. Meta `noindex,nofollow` tetap aktif sampai website disetujui untuk publikasi. Label contoh berada di view dan data, sehingga tidak cukup hanya menghapus pemberitahuan. Jangan menganggap tanggal contoh sebagai jadwal aktual atau otomatis mendatang.
