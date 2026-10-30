# WP-MIGRATION-001 — Audit dan Peta Migrasi

Tanggal audit: 26 Oktober 2026. Status: **usulan berbasis audit sumber; belum mengeksekusi migrasi atau mengesahkan PRD**. Pasangan dokumen: [BLUEPRINT.md](BLUEPRINT.md).

## 1. Dasar bukti dan batas pemeriksaan

Audit membaca implementasi kerja saat ini, termasuk perubahan yang belum masuk Git. Kode menjadi sumber fakta implementasi; dokumen task menjadi bukti historis pengujian. Tidak ada koneksi database, pembacaan kredensial, ekstraksi arsip privat, atau pembacaan isi workbook data jemaat. Keberadaan migration bukan bukti tabel sudah diterapkan atau akun tersedia.

| Sumber | Fakta/implikasi bagi migrasi |
| --- | --- |
| [AGENTS.md](../../AGENTS.md) | PRD dibaca lebih dahulu; satu task per tahap; empat halaman tetap berfungsi; data dan database dilindungi; hasil pengujian dilaporkan. |
| [PRODUCT.md](../../PRODUCT.md) | Tujuan informasi publik dan Media Center; admin menerbitkan langsung; berita internal/eksternal direncanakan; Rich Text Editor belum dirinci. Pernyataan belum ada auth/admin sudah tertinggal terhadap kode. |
| [PRD v3](../PRD.md) | **Draft untuk validasi**; P0/P1/P2 dan DEC-01–10 tidak otomatis disahkan. Pemilihan WordPress adalah rekomendasi audit ini, bukan persetujuan final stack/hosting/mitra. |
| [DESIGN.md](../../DESIGN.md) | TASK-006H membakukan catatan desain publik aktual setelah E/G; bukan pengesahan identitas resmi gereja. Tidak diubah pada audit ini. |
| [UI-PUBLIK.md](../UI-PUBLIK.md) | Fondasi empat halaman dan regression cases. Keterangan belum ada font/foto/admin merupakan kondisi historis, bukan keadaan sekarang. |
| [TASK-006E](../dokumentasi-task/TASK-006E-TYPOGRAPHY.md) | Cormorant/DM Sans lokal, lisensi dan pengujian font; laporan browser 375/768/1440, fallback, teks 200%, tanpa JS. |
| [TASK-006F](../dokumentasi-task/TASK-006F-AUDIT.md) | Menemukan konflik fokus Bootstrap, target bersama dan layout waktu tablet; flag detector foto/video bukan instruksi menambah konten rekaan. |
| [TASK-006G](../dokumentasi-task/TASK-006G-HARDEN.md) | Melaporkan perbaikan tiga temuan F, 36 kondisi browser dan 399 pengamatan fokus. Klaim drift DESIGN pada F/G bersifat historis karena kemudian ada H. |
| [TASK-006I](../dokumentasi-task/TASK-006I-LOGIN.md) | Login UI diperbarui; 43 pemeriksaan lulus, tetapi login invalid/rate limit end-to-end terhalang MySQL, login valid belum diuji. Tidak sama dengan autentikasi produksi terverifikasi. |
| [Inventaris aset](../ASSET-INVENTORY.md) | Izin foto depan tercatat dikonfirmasi pemilik proyek berdasarkan izin gereja; foto lain belum terverifikasi. Ada kalimat lama yang menyatakan semua foto belum berizin: gunakan status per aset, jangan menyimpulkan semua aman atau semua dilarang. |
| [Routes](../../app/Config/Routes.php), [Home](../../app/Controllers/Home.php), [sample](../../app/Libraries/PublicSampleContent.php), [views](../../app/Views/publik/) | Empat halaman menggunakan array statis; tidak ada model/persistence berita atau jadwal dalam alur publik. |
| [AdminAuth](../../app/Controllers/AdminAuth.php), [filter](../../app/Filters/AdminAuthFilter.php), [migration](../../app/Database/Migrations/2026-10-26-000001_CreateAdminAuthTables.php) | Fondasi autentikasi sudah ditulis, berbeda dari PRODUCT. Status penerapan database tidak diperiksa. |

Dokumen TASK-006 tersendiri yang ditemukan di `docs/dokumentasi-task/`: E, F, G, I. H direkam di DESIGN.md; artefak C tersedia di `.taste-skill/task-006c-*`. Tidak menganggap seluruh subtask A–I mempunyai dokumen atau telah selesai. Artefak `.taste-skill/` hanya referensi lokal, bukan bahan distribusi publik.

Stack sumber terverifikasi dari `system/CodeIgniter.php` dan header vendor: CI **4.7.5**, PHP requirement `^8.2` pada `composer.json`, Bootstrap **5.3.8** lokal. MySQL/XAMPP disebut dokumentasi; ketersediaannya saat audit ini tidak diuji. Tidak ada build frontend yang diperlukan oleh empat halaman.

## 2. Inventaris halaman dan kemampuan

| Route / sumber | Yang sudah ada pada kode | Yang belum selesai / batas |
| --- | --- | --- |
| `/` — `publik/beranda.php` | Hero terang, foto gereja utuh, CTA jadwal, tautan `#warta`, maksimum dua berita, sorotan jadwal, preview profil; empty state berita/jadwal. | Sorotan mengambil **item pertama**, bukan ibadah terdekat menurut waktu. Pengantar/profil/berita/jadwal masih contoh. |
| `/profil` — `publik/profil.php` | Tentang, Sejarah, Majelis, Lokasi & kontak; layout dua kolom dengan permukaan berselang. | Sejarah, nama/jabatan/periode, alamat lengkap/kontak resmi belum tersedia; tidak ada statistik/bagan aktif. “Maluku” bukan alamat lengkap. |
| `/berita` — `publik/berita.php` | Tiga berita dengan kategori, tanggal, ringkasan dan isi di HTML `details`; anchor per item. Dapat dibaca tanpa JS. | Tidak ada route detail artikel, database, editor, upload, pencarian, pagination, field berita eksternal atau empty state arsip khusus. |
| `/jadwal-ibadah` — `publik/jadwal.php` | Tiga jadwal, tanggal semantik, jam WIT, lokasi/keterangan; filter Minggu/Keluarga/Semua; status live; semua item tersedia tanpa JS. | Count awal hardcoded tiga, bukan dinamis. Tidak ada CRUD, validasi jadwal, sorting waktu otomatis, aturan batal/perubahan, jam selesai, pelayan atau recurring events. |
| Bersama — `layouts/publik.php`, `partials/*`, `publik.css/js` | Navbar empat tujuan/aktif, menu HP dan fallback `noscript`, skip link, footer, banner dummy, robots `noindex, nofollow`, font/aset lokal, reduced motion dan cetak. | Tidak berarti aksesibilitas penuh atau kesiapan rilis sudah disahkan; konten dan izin masih gate publikasi. |
| GET/POST `/admin/login` | Form email/password, error ter-escape, CSRF pada POST; verifikasi hash, regenerasi sesi; UI foto/logo, tombol tampilkan password dan fallback tanpa JS. | Tidak ada reset password/email teruji; bukan auth WordPress. Valid login belum dibuktikan oleh TASK-006I. |
| GET `/admin/dashboard` | Filter `adminAuth` mengecek sesi `admin_id`; dashboard sambutan dan status sesi. | Dashboard menyatakan pengelolaan konten belum termasuk tahap ini. Tidak ada CRUD/profil/berita/jadwal/media maupun hak akses granular. |
| POST `/admin/logout` | CSRF, menghapus identitas sesi dan regenerasi sesi. | Bukan endpoint WP; tidak dimigrasikan sebagai controller. |

Auth sumber memiliki migration kandidat tabel `admin_users` dan `admin_login_attempts`, serta command interaktif `auth:create-admin`. Controller membatasi **lima kegagalan per hash IP+email selama 900 detik**, membersihkan percobaan lama, dan memakai `password_verify`. Ini bukti kode, bukan audit keamanan lengkap atau bukti rate limiting end-to-end lulus. Command dapat membuat/memperbarui akun; **tidak dijalankan**. Migration `down()` sengaja tidak merusak data; **tidak dijalankan**.

## 3. Data dummy dan pemetaan field

Seluruh array `PublicSampleContent` adalah fixture demonstrasi, bukan data resmi. Tanggal contoh tidak boleh dimaknai sebagai tanggal kegiatan yang benar. Tidak ada asumsi jumlah rekord produksi.

| Data sumber | Inventaris | Pemetaan kandidat WordPress |
| --- | --- | --- |
| Berita `id/title/category/date/summary/body` | 3: `persekutuan` — “Ruang untuk bersekutu” (24 Oktober 2026), `pelayanan` — “Bersama dalam pelayanan” (22 Oktober), `informasi` — “Membaca warta jemaat” (20 Oktober). | `post_name`/`post_title`/kategori/`post_date`/`post_excerpt`/`post_content`; tanggal lokal perlu parsing eksplisit dan konfirmasi jika menjadi konten resmi. ID lama hanya peta fixture/anchor, bukan primary key WP. |
| Jadwal `type/name/day/date/time/location/note` | 3: Minggu 25 Oktober 09.00 A, Keluarga 28 Oktober 19.00 B, Minggu 1 November 09.00 A. Semua 2026, WIT, lokasi contoh. | CPT `leahari_ibadah`; title, meta jenis, timestamp mulai, lokasi, keterangan. `day` diturunkan dari timestamp; `09.00` dinormalisasi ke waktu 24 jam. Jangan menyimpan dua sumber hari/tanggal yang berpotensi tidak konsisten. |
| Profil dan layout copy | Placeholder di view; footer “Informasi yang mendekatkan.” adalah copy prototipe. | Page Profil dan pola tema, dengan label contoh sampai mendapat naskah sah. Copy proyek bukan slogan resmi. |

Fixture boleh disiapkan nanti **hanya untuk lokal/staging terisolasi**: judul diawali `[CONTOH]`, label terlihat di isi, tanggal disebut contoh, banner dan robots tetap aktif. Default data entry adalah draft. Demo publikasi boleh hanya di lingkungan uji untuk acceptance test. Situs produksi dibangun dari konten allowlist yang disahkan, bukan kloning semua fixture lalu menghapus banner.

## 4. Inventaris desain yang dipertahankan

| Komponen / bukti | Kontrak desain dan perilaku | Strategi WordPress |
| --- | --- | --- |
| Palet `publik.css`, DESIGN | purple `#663f72`, purple-dark `#482950`, gold `#d8bd72`, paper `#fffefa`, surface `#f5f1f6`, ink `#29232c`, muted `#5d5361`. `gold-soft` belum aktif. | Preset `theme.json`; CSS komponen untuk aturan spesifik. Jangan menambah gold-soft sebagai warna aktif hanya karena terdeklarasi. |
| Font lokal | Cormorant Garamond normal 600 untuk editorial; DM Sans 400/600/700 untuk UI, item berita/jadwal, navbar/footer. Fallback Georgia/Arial. | Font assets tema dan font families/font faces; `swap`, preload selektif dan lisensi dipertahankan. Periksa URL editor/frontend; tidak menggantungkan runtime pada CDN. |
| Skala | Isi `1.125rem/1.65`, metadata `1rem`; hero `clamp(3.25rem,6.5vw,6rem)`, judul dalam `clamp(3rem,6.5vw,6.5rem)`, section `clamp(2rem,3vw,2.5rem)`, item `clamp(1.5rem,2.2vw,2rem)`, measure isi 65ch. | Preset typography dan CSS breakpoint; jangan menerapkan serif global ke judul item/footer. Nilai lengkap/leading/tracking mengikuti DESIGN. |
| Hero Beranda | Latar terang, copy/foto dua kolom, foto 4:3 utuh tanpa crop, caption, CTA emas bersudut siku. | Pattern hero dari Group/Columns/Image/Heading/Buttons; scoped CSS untuk rasio dan breakpoint. |
| Intro tiga halaman dalam | Bidang ungu; heading serif, teks paper. | Template/pattern intro bersama, berbeda dari hero Beranda. |
| Berita/profil/jadwal | Baris editorial, bidang datar, ruang lapang, sudut siku, tanpa shadow dekoratif; grid berita dua kolom, jadwal nama/waktu/lokasi. | Block theme dan CSS komponen; Query Loop berita; block jadwal dinamis untuk field dan urutan. |
| Navigasi/footer | Empat tujuan tetap; footer gelap dengan nama besar DM Sans, link kembali ke konten; target navbar/footer minimum 44×44px. | Template parts; Navigation block diuji ulang untuk fallback tanpa JS atau gunakan navigasi HTML semantik sederhana dari tema jika fallback tidak memenuhi kontrak. |
| Fokus/resize | Outline solid 3px offset 5px; ungu/ungu gelap/emas menurut latar. Breakpoint 767.98/575.98, tablet sampai 1023.98; jadwal container query 38rem. | CSS tema yang disesuaikan markup blok, bukan menyalin selector Bootstrap dengan asumsi masih cocok. |
| Akses dasar | `lang=id`, satu h1, skip link ke main, `aria-current`, alt, label select, status live, wrapping, reduced motion, fallback font/JS, cetak semua jadwal. | Acceptance test tersendiri pada WP. `theme.json` saja tidak menjamin semantik dan interaksi tersebut. |

Perubahan markup PHP→blok memerlukan pemeriksaan visual dan fungsi ulang; desain dapat dipertahankan tanpa menjanjikan kesetaraan implementasi satu-banding-satu.

## 5. Inventaris aset dan batas distribusi

Hitungan filesystem saat audit: **37 gambar** dalam `public/assets/images/`: 5 gereja, 31 kegiatan, 1 logo. Klasifikasi visual foto gereja mengikuti inventaris terdahulu; audit ini tidak mengidentifikasi orang atau mengklaim mengaudit visual seluruh foto kegiatan.

| Kelompok / lokasi sumber | Bukti penggunaan/status | Keputusan migrasi |
| --- | --- | --- |
| `images/gereja/Gereja-tampak-depan.jpeg` | 1600×1200, 185.937 byte menurut inventaris; aktif di hero. Konfirmasi izin dicatat inventaris, bukan diverifikasi ulang. | Kandidat pertama Media Library; simpan sumber izin pada register internal, alt dan caption; pertahankan gambar utuh. Konfirmasi cakupan izin untuk situs tujuan. |
| `Gereja-tampak-samping.jpeg` | 720×900; aktif di login TASK-006I. Izin belum terverifikasi. | Tahan dari paket publik/Blueprint sampai izin jelas; tidak perlu untuk MVP halaman publik. |
| `Gambar-MajelisJemaat.jpeg`, `Gambar-suasanaIbadah.jpeg`, `WhatsApp Image 2026-10-25 at 19.20.20.jpeg` | Foto orang/ibadah; inventaris menandai anak pada dua foto ibadah. Izin/konteks belum lengkap. | Jangan impor otomatis; hanya foto terpilih setelah izin publikasi, konteks, tanggal, dan persetujuan yang diperlukan tercatat. |
| `images/kegiatan/` — 31 JPEG bernama WhatsApp | Tidak dirujuk empat view publik aktif; belum ada register izin/konteks per file yang dibuktikan audit ini. | Tidak masuk paket awal. Nama file bukan bukti tanggal acara atau hak pakai. Seleksi dan optimasi setelah izin. |
| `images/logo/Logo_Gereja_Protestan_Maluku.jpg` | 283×275 menurut TASK-006I, dipakai login; file masih untracked pada awal audit. | Keberadaan bukan bukti hak pakai/identitas disahkan. Kandidat Site Logo/Media Library setelah izin; tidak menggambar ulang. |
| `fonts/` — 4 WOFF2 + 2 OFL + README | Total font 66.088 byte, Fontsource 5.3.0; provenance/hash di README. | Dapat dipakai ulang dalam tema beserta dua lisensi. Font bukan konten Media Library. |
| Bootstrap CSS/JS | 5.3.8 lokal, lisensi MIT pada header. | Tidak dibawa secara default; ganti struktur dengan blok/CSS scoped untuk menghindari konflik. Simpan atribusi bila ada bagian vendor yang benar-benar dipakai ulang. |
| `css/publik.css`, `js/publik.js` | Token dan pola interaksi sudah diterapkan. | Ekstrak/adaptasi; JS memerlukan selector baru dan count/teks dinamis, bukan salin literal “jadwal contoh” ke produksi. |
| `admin-login.css/js` | Khusus form CI; foto/logo dan toggle password. | Referensi opsional untuk branding login WP setelah auth inti berjalan; tidak diperlukan untuk peluncuran awal. |
| `docs/references/landing-inspirasi.jpg`, `.taste-skill/` | Referensi/screenshot/laporan internal. | Tidak dibundel ke tema/Media Library/Blueprint. |
| Workbook, dokumen organisasi, ZIP/foto mentah root atau `resources/private/` | Saat audit workbook dan DOCX terdaftar di `docs/`; inventaris lama menyebut root. Lokasi historis tidak menjadi instruksi memindahkan file. | **Dikecualikan**, tanpa membaca isi/mengekstrak. Tidak menyalin `docs/` atau root repo sebagai paket situs. |

WordPress uploads biasanya dapat diakses langsung. Menaruh gambar di artikel draft/private **tidak menjadikan berkas unggahan privat**. Hanya aset yang sudah boleh publik yang masuk Media Library, termasuk lingkungan preview yang dibagikan. `noindex` juga bukan kontrol akses. Jangan membuat paket dari seluruh `public/assets/images/`.

## 6. Matriks CodeIgniter → WordPress

Keterangan: **U** = gunakan ulang setelah pemeriksaan izin/lisensi; **T** = tulis/adaptasi ulang; **X** = tidak dimigrasikan. Semua target berikut adalah usulan, belum dibuat.

| Sumber / kemampuan CI | Target WordPress | Perlakuan dan alasan | Verifikasi utama |
| --- | --- | --- | --- |
| `/`, view beranda | Page Beranda + `front-page.html`, `show_on_front=page`, `page_on_front` dari ID hasil pembuatan | T: layout blok dengan pattern; feed berita terbit dan block sorotan jadwal. | CTA, `#warta`, dua berita, kosong, foto utuh. |
| `/profil`, placeholder | Page slug `profil`, template halaman/pattern bagian | T: isi dapat diedit operator; profil awal tetap placeholder/draft sampai disahkan. | Bagian profil tetap ada; tidak mengarang Majelis/kontak. |
| `/berita`, array berita | Page Berita sebagai **Posts page** (`page_for_posts`), template `home.html`; Posts untuk artikel | T: jangan mengandalkan isi editor Posts page muncul otomatis. Intro/arsip diletakkan pada template. | `/berita/` memuat daftar; single article berfungsi. |
| Berita inline `details`, anchor | `single.html`, URL kandidat `/berita/{slug}/`; anchor lama dapat dipertahankan pada baris arsip | T/U: desain baris dipertahankan; artikel penuh menjadi halaman sendiri. Bukan route lama yang sudah ada. | Link lama `#persekutuan` tetap menemukan baris pada demo. |
| Kategori berita | Taxonomy kategori bawaan Posts | T: hanya kategori terpakai; jangan menyamakan internal/eksternal dengan kategori kegiatan. | Query dan label benar. |
| `/jadwal-ibadah`, filter statis | **Page** `jadwal-ibadah` berisi block dinamis; CPT **`leahari_ibadah`** menyimpan setiap acara | T: satu pemilik URL; `has_archive=false`, single publik tidak diperlukan P0. CPT bukan Page dengan nama sama. | Tidak ada konflik rewrite; semua jadwal publik tersedia tanpa JS. |
| Field jadwal | Registered post meta + UI formulir berlabel dalam plugin proyek | T: typed field, sanitasi, otorisasi, validasi waktu; bukan kolom tabel CI yang disalin. | Save via UI/REST tidak melewati validasi atau status publikasi. |
| Navbar/footer/layout PHP | Block Theme: `parts/header.html`, `parts/footer.html`, templates/patterns | T: ganti `base_url`, `site_url`, `esc`, include/section dengan API/markup WP yang sesuai. | Empat tujuan, fokus, skip link, fallback. |
| Palet/typography/spacing | `theme.json` + CSS komponen tema | U/T: nilai desain dipakai ulang, konfigurasi ditulis sesuai versi schema WP yang dipilih. | Editor/frontend konsisten, tidak menimpa peran font. |
| Foto terpilih | Media Library + attachment metadata/featured image, Image/Gallery blocks | U bersyarat: hanya allowlist berizin; ukuran turunan, alt/caption dan korelasi sumber dicatat. | Tidak ada foto privat/tanpa izin atau URL lokal lama. |
| `PublicSampleContent` | Fixture opsional terpisah, berlabel | X dari produksi; struktur sebagai referensi field, bukan layanan PHP WP. | Pencarian seluruh fixture sebelum rilis, termasuk feed/API. |
| Auth controller/filter/session/command | Login/logout/password reset/Users WordPress; roles/capabilities | T untuk kebijakan akses; X untuk kode/tabel/hash/sesi CI. Akun baru per operator, tanpa registrasi publik. | Unauthorized UI/REST ditolak; reset, logout dan pembatasan login diuji di hosting. |
| Dashboard placeholder | `wp-admin` + menu Jadwal; dashboard tidak perlu dibuat ulang | T: gunakan UI inti, Bahasa Indonesia, panduan tugas Media Center. | Operator dapat terbitkan berita/jadwal tanpa edit kode. |
| Rencana berita eksternal | Posts + field jenis/sumber/URL, atau opsi terpisah di §7 | T: belum ada importer CI untuk dipindahkan. | URL aman, atribusi, fallback sumber mati. |
| Migration CI, database/config framework, Composer/system/vendor | Database WordPress baru dikelola API inti | X: tidak mengimpor dump CI/MySQL atau menjalankan migration CI di WP. | Tidak ada koneksi tulis silang atau berbagi database. |
| `.env`, akun, log, session, cache, `.git`, tes/alat audit, private resources | Tidak ada target publik | X: bukan aset situs atau data seed. | Audit isi ZIP/ekspor berbasis allowlist. |

### URL dan tautan

Empat tujuan tetap `/`, `/profil/`, `/berita/`, `/jadwal-ibadah/`; normalisasi trailing slash diuji. Candidate permalink berita `/berita/%postname%/` harus diuji konflik rewrite/pagination pada WordPress tujuan. Jika URL lama memakai `index.php`, catat URL nyata sebelum cutover dan buat aturan redirect eksplisit di hosting; jangan membuat wildcard yang menelan `/wp-admin/` atau REST API.

Fragment `#persekutuan` tidak dikirim ke server, sehingga redirect server tidak dapat memilih tujuan berdasarkan fragment. Untuk fixture cukup pertahankan ID pada arsip. Bila kelak ada artikel resmi dengan anchor lama, petakan identitas nyata dan pertahankan baris/anchor penghubung; jangan membuat redirect SEO untuk contoh yang tidak pernah diterbitkan.

## 7. Opsi berita eksternal

Keputusan produk terbaru mendukung internal dan eksternal; implementasinya belum ada. Ini pilihan rancangan, bukan daftar plugin yang sudah dipasang.

| Opsi | Cara kerja / dependensi | Risiko dan keputusan |
| --- | --- | --- |
| **A — rekomendasi P0: kurasi tautan manual** | Posts inti: judul, ringkasan asli singkat yang disetujui, field `jenis_berita=eksternal`, `sumber_nama`, `sumber_url`. Halaman artikel lokal menampilkan label eksternal dan tombol “Baca di sumber”; daftar tetap satu pola. | Tanpa plugin feed. Media Center memeriksa hak kutip, tanggal dan URL HTTPS; tidak menyalin isi/foto pihak lain tanpa izin. Sumber mati tidak merusak ringkasan lokal. |
| B — sematan per sumber | Core Embed block hanya bila provider/URL tertentu terbukti didukung; fallback tautan biasa. | Dukungan Facebook/sumber lain tidak diasumsikan. Perlu audit privasi, permintaan pihak ketiga dan layout; bukan syarat MVP. |
| C — RSS/Atom kurasi | Jika sumber menyediakan feed sah, pengembang dapat memakai API feed WP dengan cache, timeout, deduplikasi dan antrean draft. | P1 sesudah verifikasi endpoint, izin, jadwal refresh dan hasil sanitasi. Jangan mengambil sembarang URL atau auto-publish; uji jaringan/cron hosting. |
| D — plugin agregator | Belum dipilih atau diverifikasi; evaluasi terpisah bila C terbukti perlu. | Tidak ada nama/slug/rekomendasi instalasi yang dikarang. Audit pemeliharaan, lisensi, hak akses, biaya, SQLite/hosting dan mekanisme impor dahulu. |

Jika fetch server diperlukan nanti, gunakan API HTTP aman dengan allowlist host dan batas redirect/respons; validasi URL saja tidak memberi hak untuk mengambil dan menerbitkan konten. API `wp_safe_remote_get` memeriksa URL untuk mengurangi SSRF. [Referensi HTTP resmi](https://developer.wordpress.org/reference/functions/wp_safe_remote_get/).

## 8. Gap P0/P1 dan risiko yang dibawa ke roadmap

| Area PRD | Kondisi sekarang | Pekerjaan WP yang tetap diperlukan |
| --- | --- | --- |
| FR-01/02/09 | UI contoh + dasar aksesibilitas tersedia. | Halaman WP, konten resmi, navigasi/fallback dan verifikasi responsif ulang. |
| FR-03/06/07/08 | Daftar berita ada; editor, upload dan lifecycle konten belum ada. | Editor blok, featured image/galeri artikel, draft→publish→tarik, validasi media dan uji bocor draft. |
| FR-04 | Daftar/filter fixture tersedia. | CPT/field/form, validasi, sorting waktu, empty/past state dan kebijakan pembatalan. |
| FR-05 | Fondasi auth CI ada, bukti E2E belum lengkap. | Identitas WP baru, role, recovery, rate limit/HTTPS/backup dan uji akses pada hosting. |
| FR-10 | Tujuan operasional ada; tidak ada bukti handover/restore/UAT selesai. | Panduan, pemilik akun/biaya, latihan operator, restore nyata dan berita acara. |
| EN-01/02/03/04/06 | Riwayat Majelis, statistik, draft revisi terisolasi, master pelayan, ACL/audit UI lengkap belum terbukti. | Tetap P1/keputusan terbuka. Tidak dijanjikan otomatis oleh WordPress/revisions. |

Risiko utama: drift dokumen terhadap kode; izin foto/logo; fixture tersaji sebagai resmi; kehilangan fallback saat markup berubah; sorting jadwal yang keliru; editor mengubah konten terbit tanpa memahami dampaknya; media draft tetap publik; kegagalan runtime Studio/hosting; kurangnya operator/pemelihara. Mitigasi, pemilik dan acceptance criteria ada di BLUEPRINT.md.

## 9. Verifikasi audit ini

Dilakukan: pembacaan sumber dan dokumen, enumerasi route/file/aset, pemeriksaan sumber resmi WordPress, serta pemeriksaan akhir JSON contoh, tautan lokal, whitespace dan batas perubahan dokumen sebagaimana dicatat pada BLUEPRINT.md. Angka pengujian TASK-006 di atas adalah **hasil historis**, tidak dijalankan ulang.

Tidak dijalankan: PHPUnit/tes aplikasi, server/browser CI atau WP, login, database/migration, impor media/konten, instalasi WordPress/tema/plugin, Blueprint runtime, deploy, atau perubahan DESIGN.md. Tidak ada klaim migrasi/kompatibilitas produksi telah lulus.
