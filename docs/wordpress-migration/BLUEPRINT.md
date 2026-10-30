# WP-MIGRATION-001 — Blueprint Migrasi WordPress Studio

Tanggal: 26 Oktober 2026. Status: **rencana teknis untuk ditinjau**, bukan migrasi yang sudah berjalan. Audit bukti, inventaris, pemetaan field/aset/URL dan batas reuse: [MIGRATION-MAP.md](MIGRATION-MAP.md).

## 1. Keputusan arsitektur yang diusulkan

Gunakan **WordPress inti + satu block theme khusus proyek + satu plugin fungsional kecil milik proyek**. Studio menjadi lingkungan pengembangan lokal; hosting permanen dipilih terpisah. Gunakan Pages untuk Beranda/Profil/Jadwal, Posts untuk berita, dan Media Library hanya untuk foto yang boleh publik. Editor blok bawaan menjadi editor awal; tidak memerlukan page builder, framework Bootstrap, plugin custom field, atau plugin kalender pada baseline.

Nama kerja tema `leahari` dan plugin `leahari-content` di dokumen ini adalah **artefak yang diusulkan untuk ditulis pada task mendatang**, bukan nama paket terverifikasi di direktori WordPress. Tidak ada slug instalasi WordPress.org untuk keduanya. Kode, ZIP dan URL unduhnya belum ada dan tidak dibuat dalam task ini.

| ID | Keputusan/usulan | Alasan dan konsekuensi |
| --- | --- | --- |
| A-01 | Block theme khusus, mempertahankan DESIGN.md dan CSS aktual sebagai referensi. | Tema generik tidak menjamin komposisi yang sudah disepakati proyek. Blok inti diprioritaskan; CSS khusus tetap diperlukan. Persetujuan identitas resmi mitra tetap terpisah. |
| A-02 | Konten/logika jadwal dan field berita eksternal berada pada satu plugin proyek biasa. | Data tidak bergantung pada tema aktif; bila plugin dimatikan data tidak dihapus, tetapi UI/render jadwal dapat berhenti. Plugin memerlukan pemelihara dan pengujian; “minim plugin” tidak berarti tanpa kode khusus. |
| A-03 | CPT `leahari_ibadah`, Page `/jadwal-ibadah/` sebagai satu-satunya daftar publik. | Tidak memakai archive CPT pada slug yang sama. Tidak membuat sistem event/recurrence/pendaftaran kompleks. |
| A-04 | Posts inti untuk berita internal maupun eksternal; eksternal dikurasi manual. | Ringkasan lokal dan tautan sumber berlabel; tidak ada scraper, API Facebook/WhatsApp atau auto-publish RSS pada MVP. |
| A-05 | `wp-admin` dan autentikasi WordPress; akun operator baru. | Tidak mengonversi sesi, hash, tabel admin atau middleware CI. Branding login ditunda; dashboard publik lama bukan template wp-admin. |
| A-06 | Operator berbasis Editor + capabilities jadwal, admin teknis terpisah. | Media Center dapat langsung menerbitkan sesuai keputusan produk; tidak mendapat instal plugin/tema/kelola user. Hak efektif wajib diuji, bukan hanya menyembunyikan menu. |
| A-07 | WordPress baru, situs/DB terpisah dari CI. | Tidak ada import SQL CI, shared auth, penggantian direktori lama atau cutover saat audit. Rollback situs lama tetap mungkin. |
| A-08 | Studio/Playground untuk demo; hosting untuk operasi Media Center. | Blueprint adalah resep setup, bukan backup, deployment, atau bukti kesamaan engine database/runtime. |

Block theme memakai `style.css` dan `templates/index.html` sebagai fondasi pengenalan tema; template parts, patterns, CSS/aset dan `theme.json` membentuk sistemnya. [Struktur tema resmi](https://developer.wordpress.org/themes/core-concepts/theme-structure/).

## 2. Pembagian konten, tema dan plugin

| Lapisan | Rancangan tanggung jawab |
| --- | --- |
| WordPress core | Pages/Posts/kategori, editor blok, revisions konten, Media Library, Users/login, permalink, status draft/publish/private/trash sesuai hak akses. |
| Tema | `front-page.html`, `page.html`, `home.html` (Posts page), `single.html`, `404.html`, `index.html`, parts header/footer, patterns profil/hero, CSS responsif/fokus/cetak. `theme.json` mengatur preset warna, typography, font lokal dan spacing yang relevan. |
| Plugin proyek | CPT/field/form jadwal, validasi dan capabilities, block dinamis daftar/sorotan jadwal; field jenis berita/sumber/URL dan tampilan atribusi; kebijakan prototipe berlabel bila demo memerlukannya. Tidak membundel data pribadi atau kode auth CI. |
| Konten database | Empat entry Pages; Berita ditetapkan sebagai Posts page. Artikel, jadwal, navigasi, attachment dan metadata terpilih. Site Editor overrides dicatat/ditinjau agar tidak diam-diam mengalahkan template berkas. |
| Operasional | Hosting/HTTPS, update, pembatasan login, pengiriman reset password, backup/offsite, restore, domain/biaya dan pelatihan. Ditangani penyedia/pengelola; plugin tambahan hanya bila kebutuhan dan paketnya sudah diverifikasi. |

`theme.json` menghubungkan pengaturan dan gaya tema dengan editor. Ini bukan format untuk menyimpan rekord jadwal, mendaftarkan CPT atau menggantikan seluruh CSS/media query. [Global Settings and Styles](https://developer.wordpress.org/themes/global-settings-and-styles/).

Template/pattern memakai blok inti Group, Columns, Heading, Paragraph, Image, Buttons dan Query Loop sejauh memenuhi desain. Daftar/sorotan jadwal menggunakan **block dinamis server-rendered** dalam plugin: query dan format waktu tidak diduplikasi sebagai markup statis di halaman. Registration mengikuti metadata `block.json` dan API WordPress yang sesuai versi target; editor menampilkan preview/data yang konsisten. [Registrasi blok resmi](https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/).

Bagian Profil dikelola sebagai satu Page dengan pola struktur terkunci seperlunya; tidak menambah CPT Majelis/statistik untuk placeholder. Operator mengubah naskah dan foto, bukan shell/desain global. Lock editor adalah pagar UX, **bukan** pengganti capabilities dan validasi server.

## 3. Model jadwal dan custom fields

Nama teknis berikut adalah kontrak usulan; belum terdaftar pada situs mana pun. CPT `leahari_ibadah` didaftarkan pada `init`, UI admin aktif, dukungan title/revisions/custom-fields, REST sesuai kebutuhan editor, capabilities eksplisit dengan pemetaan object-level. Untuk P0: `has_archive=false`, `publicly_queryable=false`; publik membaca jadwal terbit melalui block daftar Page. UI/REST harus tetap membatasi draft dan pengguna anonim. Parameter API dan batas nama CPT mengikuti [register_post_type](https://developer.wordpress.org/reference/functions/register_post_type/).

| Field usulan | Tipe/aturan | Makna publik |
| --- | --- | --- |
| `post_title` | String wajib, panjang wajar dan pesan validasi jelas. | Nama ibadah/acara. |
| `leahari_jenis` | String enum awal `minggu`, `keluarga`; daftar jenis dikonfirmasi operator sebelum rilis. | Label/filter, tidak mengambil sembarang nilai dari request. Tambah jenis lewat perubahan terencana; taxonomy baru belum perlu. |
| `leahari_mulai_utc` | Integer timestamp wajib; UI meminta tanggal/jam WIT, dikonversi ke UTC dengan API waktu WP. | Satu sumber waktu mulai; label hari/tanggal/jam diturunkan saat render dengan zona situs. |
| `leahari_selesai_utc` | Integer opsional; bila diisi tidak sebelum mulai. | Jam selesai opsional; jangan menampilkan `00.00` sebagai pengganti kosong. |
| `leahari_lokasi` | String wajib; hanya tempat yang boleh diumumkan. | Lokasi ibadah, bukan alamat keluarga pribadi dari Excel. |
| `leahari_keterangan` | String opsional; plain text atau markup terbatas yang disanitasi. | Informasi tambahan; tidak menyalin catatan privat. |

Nama pelayan, master penugasan, pola berulang dan status batal belum masuk field wajib. Jika pembatalan dibutuhkan untuk P0, pemilik keputusan menentukan apakah jadwal tetap terlihat dengan label batal atau ditarik; jangan menghapusnya dari daftar tanpa kebijakan yang jelas. Jangan menambahkan recurrence engine untuk tiga jenis field sederhana.

Metadata didaftarkan melalui `register_post_meta`: `type`, `single`, sanitasi, `auth_callback`, dan schema REST eksplisit. Ekspos REST hanya field yang memang boleh publik; prefix nama tidak memberi privasi. Aktifkan dukungan revisions untuk meta yang perlu pemulihan pada versi target yang mendukungnya; uji restore field, tidak hanya judul. [register_post_meta](https://developer.wordpress.org/reference/functions/register_post_meta/), [register_meta dan revisions](https://developer.wordpress.org/reference/functions/register_meta/).

UI formulir berlabel dibuat dalam plugin dengan mekanisme editor inti yang kompatibel; operator tidak mengedit pasangan key/value mentah. Validasi semantik dijalankan pada semua jalur simpan/publikasi yang didukung (admin, REST, impor fixture), lengkap dengan nonce dan capabilities sesuai jalurnya. Input invalid tidak boleh mengubah jadwal publik; status publish harus ditolak bila field wajib kosong. Sanitasi bukan pengganti validasi lintas field.

Usulan zona situs `Asia/Jayapura` (WIT, UTC+9) mengikuti UI saat ini; konfirmasi dengan mitra sebelum rilis. Query sorotan: hanya `publish`, mulai >= waktu sekarang, urut mulai menaik, ambil satu. Jadwal yang sudah dimulai tanpa jam selesai diperlakukan sebagai telah mulai; kebijakan menahan acara sedang berlangsung perlu keputusan tambahan. Daftar memisahkan mendatang/lampau sesuai kebijakan yang disahkan; jangan mengandalkan tanggal publikasi post untuk urutan ibadah. Filter dan count dihitung dari hasil render; daftar server tetap terbaca tanpa JS dan cetak mencakup data yang semula tersembunyi oleh filter.

## 4. Berita, publikasi dan akun Media Center

Untuk Posts: judul/isi/tanggal/ringkasan/kategori/featured image menggunakan core. Field tambahan usulan: `leahari_jenis_berita` enum internal/eksternal, `leahari_sumber_nama`, `leahari_sumber_url`. Jenis eksternal mewajibkan sumber+URL valid HTTPS; internal tidak memakai tautan eksternal sebagai pengganti isi. Field dan atribusi ditangani plugin yang sama; opsi feed ada di MIGRATION-MAP §7.

Alur awal: buat draft → preview → terbitkan langsung oleh operator yang berwenang → koreksi atau tarik ke draft. “Arsipkan” pada kebutuhan PRD berarti menarik akses publik; **archive WordPress adalah daftar konten, bukan status penarikan**. Gunakan draft/private sesuai kebijakan dan uji URL lama, REST, pencarian, feed, sitemap serta cache. Trash adalah tahap penghapusan, bukan tombol arsip default. Revisions membantu pemulihan tetapi tidak otomatis menyediakan draft revisi terisolasi dari versi terbit. Jika DEC-05 mewajibkan revisi terpisah, lakukan task khusus/evaluasi solusi sebelum menyatakan FR-07 selesai.

Editor bawaan menjadi baseline untuk dipraktikkan bersama Media Center; keputusan rinci editor pada PRODUCT masih ditunda. Pengguna harus memahami bahwa Update pada post terbit dapat langsung mengubah versi publik. UAT menguji draft, publish, koreksi dan penarikan, bukan hanya berhasil menyimpan teks.

Usulan dua akun operator bernama individu, berbasis role Editor ditambah capability jadwal, dan satu administrator teknis terpisah. Editor inti dapat mengelola Posts/Pages/media tetapi bukan instalasi plugin atau administrasi situs; capability CPT harus diberikan dan diuji tersendiri. Jika Editor terlalu luas setelah UAT, buat role Media Center yang lebih terbatas dalam plugin, bukan membagi akun admin. [Roles and Capabilities resmi](https://wordpress.org/documentation/article/roles-and-capabilities/).

Registrasi publik dimatikan; akun dan password baru dibuat melalui jalur privat. Tidak menyalin email/hash admin CI ke JSON/WXR/ZIP, tidak memakai autologin demo untuk produksi, dan tidak menganggap core WP menjamin pembatasan lima kegagalan/900 detik yang sama. Rencana operasi wajib memilih mekanisme rate limit (hosting atau solusi terverifikasi), menguji HTTPS, reset password/email, logout dan akses tanpa sesi. Branding login bukan alasan menulis ulang autentikasi WordPress.

**Gap terhadap FR-08:** PRD meminta file privat/draft tidak dapat diunduh secara tidak sah. Media Library standar tidak memenuhi kebutuhan kerahasiaan file draft hanya dengan mengubah status artikelnya. Baseline membatasi upload pada aset yang memang sudah diizinkan untuk dapat diakses publik sejak diunggah. Pemilik kebutuhan harus menegaskan apakah pembatasan ini memenuhi kebutuhan mereka; bila foto sebelum terbit wajib rahasia, rancang penyimpanan berkas terlindungi dan endpoint berotorisasi pada task tersendiri, lalu uji URL langsung sebelum menerima FR-08. Jangan menganggap keputusan ini otomatis mengurangi requirement draft PRD.

Untuk aksi terbit/tarik, alur harus memberi konfirmasi dan status hasil yang jelas; uji apa yang sudah disediakan editor inti dan tambahkan hanya kekurangan yang terbukti. Catatan minimum aksi kritis (aktor, waktu, objek, aksi terbit/tarik) dipisahkan dari audit UI lengkap P1. Verifikasi apakah log penyedia sudah mencakupnya; jika tidak, rancang pencatatan terbatas pada plugin proyek, dengan retensi dan akses admin yang disepakati. Revisions konten dan log error bukan otomatis audit semua aksi publikasi. Tidak menyimpan password, isi privat atau data anggota pada log.

## 5. Konsep Blueprint JSON dan batas resmi

Studio menggunakan format Blueprint Playground. Dokumen resmi menyebut runner Studio memakai native PHP dan networking selalu aktif pada versi yang didokumentasikan; v2 masih disebut dalam pengembangan, dengan v1 tetap didukung. **Pilih format v1** untuk rancangan ini, walau schema Playground yang ditinjau sudah memuat deklarasi v1/v2. Format bersama tidak membuktikan perilaku identik. Catat versi Studio yang benar-benar dipakai saat validasi berikutnya. [Panduan custom Blueprints Studio](https://developer.wordpress.com/docs/guides/how-to-create-custom-blueprints/).

### 5.1 Skeleton JSON yang dapat ditinjau sekarang

Berikut **contoh JSON di dalam dokumen**, bukan berkas Blueprint yang sudah dijalankan. Hanya menata opsi prototipe; tidak memasang tema/plugin, membuat empat halaman, memasukkan fixture, atau menghasilkan desain Leahari. Instalasi default WordPress mungkin masih memiliki konten bawaan; itu bukan konten resmi dan harus ditangani pada tahap fixture berikutnya.

```json
{
  "$schema": "https://playground.wordpress.net/blueprint-schema.json",
  "landingPage": "/",
  "login": false,
  "preferredVersions": {
    "php": "8.3",
    "wp": "latest"
  },
  "steps": [
    {
      "step": "setSiteOptions",
      "options": {
        "blogname": "Jemaat Leahari — PROTOTIPE CONTOH",
        "blogdescription": "Pratinjau lokal dengan konten contoh; bukan situs resmi.",
        "blog_public": "0",
        "users_can_register": "0",
        "timezone_string": "Asia/Jayapura",
        "permalink_structure": "/berita/%postname%/",
        "default_comment_status": "closed",
        "default_ping_status": "closed"
      }
    },
    {
      "step": "setSiteLanguage",
      "language": "id_ID"
    }
  ]
}
```

Nama field tingkat atas diperiksa terhadap [schema resmi](https://playground.wordpress.net/blueprint-schema.json). `setSiteOptions` menerima object `options`; `setSiteLanguage` menerima `language` dan mengunduh terjemahan. Referensi [steps resmi](https://developer.wordpress.org/playground/blueprints/steps/) dan [Options API](https://developer.wordpress.org/apis/options/) menjadi dasar bentuk tersebut. Nilai opsi di atas adalah pilihan proyek, bukan setting khusus Studio yang dikarang.

PHP 8.3 adalah kandidat awal yang terdapat dalam contoh resmi; bukan klaim versi terbaik atau requirement final semua dependensi. `latest` sengaja hanya untuk eksplorasi: sebelum baseline reproduksi, ganti dengan versi WP yang benar-benar diuji, catat versi PHP/Studio, schema tanggal/hash serta versi artefak. Jangan menetapkan nomor versi rilis yang belum diperiksa. `login=false` menghindari permintaan autologin dari Blueprint; bukan hardening akun demo. `blog_public=0` meminta mesin pencari tidak mengindeks, **tidak membuat situs privat** dan tidak menjamin persis `noindex,nofollow` lama. Tema demo kelak harus mempertahankan banner/label serta robots yang disyaratkan proyek; staging juga dilindungi akses.

### 5.2 Rangkaian Blueprint penuh setelah artefak tersedia

Urutan ini adalah **spesifikasi dependensi**, bukan JSON dengan URL palsu. Tambahkan step hanya ketika artefak nyata sudah tersedia dan diperiksa; Blueprint final tidak boleh menyisakan placeholder URL atau slug.

| Urut | Step/API resmi kandidat | Input nyata yang diperlukan / hasil |
| --- | --- | --- |
| 1 | `setSiteOptions`, `setSiteLanguage` | Opsi demo seperti skeleton; terjemahan memerlukan jaringan. Settings bahasa/prototipe diterapkan sebelum fixture. |
| 2 | `installPlugin` | ZIP **plugin proyek yang sudah ditulis dan diaudit**; `pluginData` resource `url` dengan URL HTTPS nyata, `options.activate=true`. CPT/field/block harus tersedia sebelum seed. |
| 3 | `installTheme` | ZIP tema yang sudah diaudit; `themeData` resource `url`, `options.activate=true`. Jangan memakai key lama `themeZipFile`/`pluginZipFile` untuk rancangan baru. |
| 4 | `runPHP` atau `importWxr` setelah evaluasi | Seed aman menggunakan API WP atau WXR tersanitasi. Buat/temukan Pages menurut slug; simpan ID hasilnya; set `show_on_front`, `page_on_front`, `page_for_posts` dari ID tersebut. Tidak hardcode ID 1/2/3. |
| 5 | API WP di seed yang ditinjau | Hubungkan template, navigasi empat tujuan, block jadwal dan fixture berlabel; import hanya media allowlist. Tidak menjadikan import WXR sebagai konverter CI otomatis. |
| 6 | `setSiteOptions`/API WP untuk konfigurasi akhir | Periksa kembali opsi noindex/registrasi, permalink dan zona waktu. Flush rewrite hanya pada setup/perubahan relevan, bukan setiap request. Landing ke `/` untuk review publik; akses admin mengikuti lingkungan lokal. |

Bentuk `installTheme`/`installPlugin`, parameter resource, `runPHP` dan `importWxr` harus dicocokkan lagi pada versi runner yang akan dipakai. Rujukan parameter: [Steps Playground](https://developer.wordpress.org/playground/blueprints/steps/). Kode seed tidak ditulis dalam audit ini. Pilihan `runPHP` dengan API WP lebih cocok untuk fixture kecil terstruktur; penggunaan WXR perlu uji attachment/metadata dan dependensi importer yang benar-benar digunakan runner.

Manifest internal artefak kelak mencatat nama file, versi/commit, SHA-256, lisensi, izin aset, pemilik dan URL unduh yang dapat diakses runner. Hash diverifikasi sebelum eksekusi oleh proses validasi; **jangan menambahkan properti `sha256` ke JSON resource tanpa dukungan schema**. Jangan membuat ZIP dari seluruh repository. Resource URL publik hanya berisi artefak aman publik, tanpa token privat atau kredensial di query string. Jika artefak tidak boleh publik, pilih alur pemasangan lokal yang didukung versi Studio yang diuji; jangan mengasumsikan path `D:\...` dapat diakses Playground browser.

Seed harus gagal dengan pesan jelas bila CPT/tema tidak tersedia dan tidak membuat item ganda jika diulang pada lingkungan uji. Pengulangan bukan izin menimpa konten operator: tahap otomatis hanya untuk situs uji baru, dengan identitas fixture eksplisit dan tanpa reset/delete massal. Tidak memakai `runSql`, `resetData`, atau import dump CI untuk membangun data WordPress.

### 5.3 Dependensi dan keterbatasan

| Hal | Implikasi / gate |
| --- | --- |
| Studio belum diperiksa lokal | Audit tidak memeriksa apakah Studio terpasang atau versi apa. Task implementasi mencatat versi dan menguji import custom Blueprint dari UI Studio pada situs baru. Alur resmi tersedia pada [Blueprints Studio](https://developer.wordpress.com/docs/developer-tools/studio/blueprints/). |
| Engine database | Studio secara default memakai SQLite; dukungan MySQL kustom juga didokumentasikan. Hosting kemungkinan MySQL/MariaDB, tetapi belum dipilih. Gunakan API WP; uji sorting meta, revisions, upload, export/restore pada engine hosting target. Jangan menyalin SQLite sebagai database CI/MySQL. [FAQ Studio](https://developer.wordpress.com/docs/developer-tools/studio/frequently-asked-questions/). |
| Playground vs Studio | Browser Playground dan runner Studio berbeda; filesystem, network, redirect, path dan runtime PHP dapat berbeda. Lulus schema/Playground tidak cukup untuk menyatakan Studio/hosting lulus. |
| Network | Terjemahan, ZIP dan media remote perlu koneksi/URL valid; Playground browser mungkin menghadapi CORS/limit respons. Studio networking bukan kontrol untuk melindungi data privat. |
| Media/izin | Import WXR tidak menjamin unduhan media sukses; attachment draft bukan penyimpanan privat. Tidak ada foto kegiatan/logo otomatis dalam skeleton. |
| Deploy dan backup | Studio adalah lokal; Preview Sites bukan hosting permanen. Ekspor/Blueprint bukan bukti restore lengkap. Pilih host dan uji restore files+DB+uploads pada staging terpisah. [FAQ publikasi Studio](https://developer.wordpress.com/docs/developer-tools/studio/frequently-asked-questions/). |
| Fitur WP | Query Loop umum tidak otomatis menyusun CPT menurut waktu ibadah atau memvalidasi field. Revisions bukan approval workflow. Rate limiting dan email delivery perlu verifikasi operasional. |
| Pemeliharaan | Tema/plugin proyek perlu penanggung jawab update dan regression test. Jangan menambah plugin keamanan/backup/SEO/forms sebelum kebutuhan spesifik serta paket dan dukungan host diverifikasi. |

## 6. Rencana validasi berikutnya

Semua eksekusi WP di bawah **belum dijalankan** pada WP-MIGRATION-001.

1. Ekstrak skeleton/final JSON pada task implementasi terpisah, parse JSON dan validasi dengan schema resmi yang dicatat versi/tanggal/hashnya. Periksa juga nilai opsi WordPress; schema tidak membuktikan nama/nilai opsi masuk akal. Tolak unknown keys dan placeholder. Tinjau semua `runPHP` sebagai kode yang dieksekusi.
2. Pastikan ZIP allowlist tidak berisi `.env`, hash/email akun lama, database dump, logs/session, workbook, DOCX privat, ZIP mentah, `resources/private`, `.git`, screenshot atau materi tanpa izin. Verifikasi hash/lisensi dan struktur folder tema/plugin sebelum menjalankan.
3. Jalankan pada situs Playground baru untuk memeriksa step/resource, lalu pada **situs Studio baru terpisah dari CI**. Rekam versi runner/WP/PHP/engine, step logs, hasil, screenshot, keterbatasan dan kegagalan. Jangan menjalankan pada situs berisi konten operator.
4. Periksa empat route, template aktif, front page/Posts page ID, nav, anchor, artikel, jadwal, zona waktu, empty state, filter dan count tanpa JS. Jangan menerima hasil yang masih memakai tema default sebagai selesai tema Leahari.
5. Uji berita/jadwal draft vs publish vs tarik dari akses anonim pada HTML, REST, pencarian, feed, sitemap dan cache. Validasi UI/REST input salah: field wajib, tanggal/jam tak valid, selesai sebelum mulai, jenis tak dikenal, URL eksternal tidak aman, unggahan tidak diizinkan. Uji operator yang tidak berhak dan akun non-admin.
6. Bandingkan visual empat halaman dengan baseline desain pada 375/768/1440px; lanjutkan 320/360/390/430px. Uji keyboard/fokus, target sentuh, root teks 200% serta zoom browser tersendiri, reduced motion, tanpa JS, fallback font, cetak dan teks panjang. Screenshot lama/task E–I adalah referensi, bukan hasil lulus WP.
7. Di staging hosting target, uji HTTPS, login gagal/valid, pembatasan percobaan, reset email, logout, status draft dan upload lagi. Uji backup→restore lengkap dan UAT operator; pin baseline hanya setelah kriteria tahap terkait lulus. Kegagalan download/tema diperiksa memakai [panduan troubleshooting resmi](https://developer.wordpress.org/playground/blueprints/troubleshoot-and-debug/).

## 7. Roadmap kecil, satu task per tahap

Setiap baris adalah task terpisah; tidak menjalankan beberapa tahap sebagai efek samping. Pekerjaan implementasi berikutnya dilakukan pada lokasi WP terisolasi. CI tetap tersedia sampai gate cutover disahkan.

| Tahap | Deliverable / dependensi | Acceptance criteria | Risiko dan mitigasi |
| --- | --- | --- | --- |
| **WP-002 — Tema dan Beranda** | Setelah keputusan memakai arah WP dicatat: workspace WP baru, versi Studio/WP/PHP tercatat, block theme minimum dan token/font/pattern hero. Gunakan hanya foto depan yang cakupan izinnya dikonfirmasi atau placeholder jujur. | `/` menampilkan komposisi terang/foto utuh, heading/body sesuai DESIGN, CTA dan empty state; 375/768/1440 bebas overflow; banner/label/noindex aktif. CI tidak tersentuh. | Drift desain/izin; bandingkan screenshot dan register aset. Data berita/jadwal boleh belum ada, empty state wajib benar. |
| **WP-003 — Shell dan Profil** | Header/footer, Page Profil, empat tujuan navigasi dan halaman placeholder berlabel untuk tujuan yang belum diimplementasi. | Profil mempertahankan empat bagian; tidak ada sejarah/pengurus/kontak rekaan; skip link, aktif nav, keyboard dan tanpa JS lulus. Endpoint belum selesai disebut jelas, bukan link mati. | Navigation block bergantung JS; verifikasi fallback, pakai markup semantik sederhana bila perlu. |
| **WP-004 — Berita internal** | Posts page `/berita/`, template daftar/single, kategori/featured image/galeri inti; peran uji sementara hanya lokal. | Buat draft, preview, publish, ubah, tarik; anonim tidak melihat draft/isi ditarik. Layout baris dan permalink bekerja; 0/1/banyak artikel, teks panjang dan tanpa foto layak. | Posts page mengabaikan isi Page; intro berada pada template. Media hanya berizin, jangan mengandalkan privasi draft. |
| **WP-005 — Berita eksternal manual** | Plugin proyek mulai memuat field jenis/sumber/URL dan atribusi; tidak ada agregator. | Operator membuat post eksternal berlabel dengan ringkasan sendiri, tautan sumber valid dan fallback saat sumber mati; input tidak aman ditolak. | Hak kutip/foto dan broken link; kurasi manual, audit sumber, tanpa auto-fetch. |
| **WP-006 — Jadwal** | Plugin yang sama diperluas CPT/meta/form/block daftar+highlight. Keputusan zona, jenis, lampau/batal dicatat sebelum fitur terkait diterima. | Tanggal/jam/lokasi wajib, selesai opsional; validasi lintas field UI/REST; mendatang terurut benar; sorotan bukan item pertama; kosong/lampau/filter/count/cetak/tanpa JS lulus; draft tidak bocor. | UTC/WIT salah, konflik slug, meta tidak tersimpan; satu sumber timestamp, Page pemilik URL, uji engine target. |
| **WP-007 — Admin dan keamanan operasional** | Sempurnakan capability operator/admin, menu sederhana, akun terpisah, reset email, rate limit; auth inti sudah dipakai sejak setup, tidak ditunda penanganan keamanannya. Putuskan gap media FR-08 dan pencatatan aksi kritis. | Operator dapat kelola konten yang disepakati dan tidak dapat instal plugin/kelola akun; unauthorized UI/REST gagal; login/logout/reset/pembatasan login benar; konfirmasi terbit/tarik dan log minimum teruji; dampak Update dijelaskan. FR-08 belum diterima sampai kebijakan media atau proteksi yang diperlukan disepakati dan diuji. | Editor terlalu luas, revision workflow disalahpahami; UAT hak efektif dan keputusan DEC-05. Jangan klaim parity mekanisme CI. |
| **WP-008 — Blueprint reproduksi dan staging** | ZIP tema/plugin final berizin, manifest versi/hash, seed hanya dummy; final JSON divalidasi dan diuji pada situs baru Studio/Playground; host staging dipilih. | Dua pembuatan situs uji baru menghasilkan struktur/konten yang diharapkan; tidak ada fixture ganda dalam uji ulang seed; tidak ada secret/aset privat; empat halaman, artikel dan jadwal lulus pada staging target. | Runner/SQLite berbeda dan URL artefak mati; pin baseline, uji host dan simpan paket rilis aman. |
| **WP-009 — Konten resmi dan serah terima** | Konten allowlist disahkan, fixture dibersihkan dari kandidat produksi, backup/restore, panduan, UAT Media Center, rencana URL/cutover/rollback dan pemilik hosting/biaya. | Operator utama dan cadangan menyelesaikan tugas berita/jadwal tanpa kode; restore DB+uploads+tema/plugin terbukti; tidak ada dummy di HTML/API/feed/media publik; pengesahan konten dan kesiapan tercatat sebelum banner/robots demo dilepas dan domain dialihkan. | Data salah, operator tunggal, rilis tanpa rollback; latihan tugas, sign-off dan salinan CI tetap tersedia. |

Cutover nanti: inventaris URL nyata, bekukan perubahan konten sementara sesuai kesepakatan, ambil backup yang dapat dipulihkan, validasi kandidat WP, pasang redirect yang sudah diuji, lalu ubah routing/domain pada task deployment tersendiri. Catat waktu pengamatan dan pemilik rollback. Jika gagal, arahkan kembali ke situs CI yang dipertahankan; rekonsiliasi konten WP yang masuk setelah cutover secara terkontrol. Jangan mematikan/menghapus CI pada tahap tema/Blueprint.

## 8. Asumsi, keputusan terbuka dan pemilik

| Asumsi/keputusan | Status dan pemilik yang diusulkan | Dampak |
| --- | --- | --- |
| Stack WordPress menggantikan CI | Usulan audit; Kelompok 13/DPL + mitra mencatat keputusan menggantikan arah stack draft DEC-02. | WP-002 dilakukan sebagai implementasi terpisah setelah arah dicatat; audit ini tidak menginstal apa pun. |
| Desain publik dipertahankan | Instruksi pengguna dan baseline proyek; persetujuan identitas mitra DEC-09 masih terpisah. | Tidak mengubah DESIGN.md atau mendesain ulang hero. |
| Konten/foto/logo resmi | Sekretariat/Majelis + Media Center, DEC-04/08/09. Izin foto depan tercatat, lainnya belum lengkap. | Allowlist per aset; tidak memindahkan foto/konten privat ke publik untuk melengkapi demo. |
| Operator menerbitkan langsung | Keputusan produk terbaru; personel dan batas kewenangan DEC-03 tetap perlu penetapan. | Editor + capability CPT diuji; tidak menambah approval bertingkat sebagai syarat rekaan. |
| Revisi konten terbit | DEC-05, Media Center + tim. | Core revisions tidak memenuhi isolasi revisi secara otomatis; dapat menambah task jika diwajibkan. |
| Zona/jenis/pembatalan jadwal/pelayan | WIT sudah dipakai UI; kebijakan detail oleh sekretariat + Media Center, termasuk DEC-06. | Query dan UI disesuaikan keputusan; tanpa master pelayan/recurrence pada baseline. |
| Host, domain, update, backup/email/rate limit | DEC-07, Majelis + tim/pengelola teknis. | Tidak menjanjikan plugin/konfigurasi host sebelum layanan dipilih dan diverifikasi. |
| UAT, jadwal dan handover | DEC-10, Media Center + Kelompok 13/DPL. | Tanggal akademik PRD bukan janji target migrasi; durasi ditentukan setelah gate dan kapasitas nyata. |
| P1 statistik/riwayat Majelis/audit UI | DEC-01/PRD, mitra + tim. | Tidak membaca/impor workbook anggota untuk memenuhi fitur di luar lingkup. |

## 9. Langkah pertama yang dapat dieksekusi

Task lanjutan yang disarankan adalah **WP-002 — Tema dan Beranda di lingkungan WordPress terisolasi**. Inputnya dokumen audit ini, DESIGN.md, CSS publik, empat font beserta lisensi, dan izin foto depan yang dicatat. Awali dengan mencatat keputusan stack dan versi Studio/WP/PHP serta memilih lokasi baru yang tidak berada di implementasi CI; kemudian kerjakan tema/beranda dan validasi acceptance criteria WP-002 saja. Jangan sekaligus mengimpor seluruh gambar, membuat akun produksi, memasang agregator, atau mengalihkan domain.

Rencana ini tidak menjanjikan kompatibilitas satu-banding-satu antara CI dan WordPress. Yang dipertahankan adalah tujuan halaman, informasi yang boleh publik, desain dan kontrak interaksi; kode framework, autentikasi dan penyimpanan dibangun ulang dengan API WordPress.

## 10. Hasil pemeriksaan WP-MIGRATION-001

Tahap ini hanya menghasilkan BLUEPRINT.md dan MIGRATION-MAP.md. Pemeriksaan dokumen mencakup parse skeleton JSON, resolusi tautan lokal, pemeriksaan whitespace, serta pembandingan daftar/hash file kerja sebelum/sesudah dengan dua dokumen baru dikecualikan. Detail hasil akhir dilaporkan bersama penyerahan dokumen.

Hasil pemeriksaan aktual: **parse satu blok JSON lulus**, 19 target tautan lokal ditemukan, encoding UTF-8/fence Markdown/trailing whitespace lulus, dan `git diff --check` exit 0. Git hanya memperingatkan normalisasi LF→CRLF pada file login yang sudah berubah sebelum task ini. Daftar 839 file lama yang dilacak/terlihat Git memiliki digest gabungan SHA-256 identik sebelum/sesudah (dua dokumen ini dikecualikan); perubahan login, aset, dokumen TASK-006I dan file runtime yang sudah ada tetap dipertahankan. Pemeriksaan hash mencakup berkas yang dilaporkan `git ls-files --cached --others --exclude-standard`, bukan bukti inspeksi seluruh berkas ignored atau kondisi database.

Tidak menjalankan WordPress/Blueprint, schema validator otomatis, PHPUnit, browser, database, migration, login atau deployment. Bentuk JSON dibandingkan dengan schema/steps resmi secara manual; **belum dinyatakan lulus validasi schema otomatis atau runtime Studio/Playground**. Hasil TASK-006 yang dikutip adalah catatan historis, bukan tes audit ini.
