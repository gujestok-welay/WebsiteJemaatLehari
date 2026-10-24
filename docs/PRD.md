# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Website Informasi dan Publikasi Digital Jemaat Leihari
### Program KKN Tematik Kelompok 13 — Fakultas Ilmu Komputer, Universitas Kristen Indonesia Maluku (UKIM)

| Atribut | Keterangan |
|---|---|
| Versi | **3.0 — Draft penyelarasan dengan konteks KKN Tematik Kelompok 13** |
| Disusun | 24 Oktober 2026 |
| Status | **DRAFT UNTUK VALIDASI** — bukan persetujuan final mitra, bukan instruksi perubahan kode/database |
| Mitra/pemilik kebutuhan | Jemaat Leihari (konfirmasi penanggung jawab resmi diperlukan) |
| Tim pelaksana | KKN Tematik Kelompok 13, FILKOM UKIM |
| Mitra operasional | Media Center Jemaat Leihari; hubungan koordinasi dengan sekretariat/Majelis perlu disepakati |
| Sasaran produk | Website publik + dashboard pengelolaan konten oleh Media Center |
| Stack usulan | **CodeIgniter 4, PHP, MySQL, Bootstrap**, HTML/CSS/JavaScript (menunggu keputusan tim dan audit aset yang telah ada) |
| Metode kerja | Analisis kebutuhan → desain → implementasi bertahap → pengujian bersama mitra → pelatihan → serah terima |
| Sumber penyusunan | PRD Leihari v2.1 (22 Oktober 2026); penuturan hasil wawancara Ketua Majelis dalam percakapan proyek; arahan umum Template Proposal KKNT FILKOM 2026 |

> **Aturan interpretasi:** (T) = temuan yang dilaporkan dari wawancara/pembicaraan dengan pengguna; (D) = keputusan/usulan perancangan Kelompok 13 yang belum disahkan mitra; (K) = kebutuhan atau fakta yang masih harus dikonfirmasi. PRD v2.1 adalah rancangan awal, sehingga rincian teknis di dalamnya **tidak otomatis** menjadi keputusan akhir proyek ini.

---

## 1. Ringkasan Eksekutif

Jemaat Leihari saat ini menggunakan WhatsApp dan Facebook untuk menyampaikan informasi seperti berita kegiatan dan jadwal ibadah **(T)**. Berdasarkan penuturan hasil wawancara awal dengan Ketua Majelis, pembaruan Facebook tidak selalu konsisten dan sebagian warga jemaat yang tinggal di luar wilayah mengalami kesulitan memperoleh informasi terbaru **(T)**. Media Center jemaat telah ada dan direncanakan sebagai pihak yang mengelola website setelah KKN **(T, kebutuhan tata kelolanya perlu dikonfirmasi)**.

Kelompok 13 mengusulkan pengembangan **Website Informasi dan Publikasi Digital Jemaat Leihari** sebagai kanal informasi resmi tambahan, bukan pengganti WhatsApp/Facebook. Website memungkinkan pengunjung melihat profil, berita dan dokumentasi kegiatan, serta jadwal ibadah tanpa login; Media Center memperbaruinya lewat dashboard yang mudah digunakan tanpa mengedit kode atau database secara manual **(D)**.

**Visi produk (usulan Kelompok 13):** *Mewujudkan pusat informasi digital Jemaat Leihari yang mudah diakses, terpercaya, konsisten diperbarui, serta dapat dikelola secara mandiri dan berkelanjutan oleh Media Center jemaat.*

**Visi pengabdian/KKN (rumusan kerja yang perlu disahkan kelompok dan mitra):** *Tidak berhenti pada penyerahan website, melainkan melakukan alih pengetahuan, pelatihan admin, dan penyiapan tata kelola agar layanan informasi tetap berjalan setelah KKN berakhir.*

## 2. Dasar Masalah dan Bukti Kebutuhan

### 2.1 Temuan awal

| ID | Temuan/indikasi | Dasar | Status |
|---|---|---|---|
| EV-01 | Informasi jemaat dibagikan melalui WhatsApp dan Facebook | Penuturan hasil wawancara dari tim KKN | T — perlu dicatat dalam berita acara/notulen |
| EV-02 | Facebook tidak selalu diperbarui karena pengelolaan kurang aktif | Penuturan hasil wawancara dari tim KKN | T — perlu konfirmasi frekuensi dan penyebab |
| EV-03 | Sebagian warga jemaat di luar wilayah kesulitan memperoleh informasi terkini | Penuturan hasil wawancara Ketua Majelis melalui tim | T — perlu contoh kasus atau verifikasi pengguna |
| EV-04 | Jemaat memiliki Media Center yang dituju sebagai pengelola website | Penuturan tim KKN | T — perlu konfirmasi personel, akses, perangkat, dan kewenangan |
| EV-05 | Pengunjung kemungkinan lebih sering menggunakan HP; sebagian membutuhkan antarmuka ramah lansia | Asumsi desain dari PRD v2.1 | D/K — validasi melalui observasi/tes pengguna |

### 2.2 Rumusan masalah

1. Saluran informasi digital yang digunakan saat ini belum menyediakan satu pusat informasi publik yang konsisten dan terstruktur **(analisis berdasarkan EV-01 s.d. EV-03)**.
2. Ketersediaan informasi terbaru bagi warga jemaat, khususnya yang berada di luar wilayah, belum optimal **(EV-03)**.
3. Proses pengelolaan berita dan jadwal perlu dibuat mudah agar tidak selalu bergantung pada satu orang yang memahami teknis website **(analisis; risiko yang perlu divalidasi)**.
4. Website harus dapat dikelola oleh Media Center setelah pendampingan KKN berakhir **(kebutuhan keberlanjutan)**.

### 2.3 Tujuan produk

- Menyediakan profil jemaat dan kontak/lokasi resmi yang telah disetujui untuk publik.
- Menyediakan berita, kegiatan, dan foto dokumentasi dalam satu kanal yang dapat diakses kembali.
- Menyediakan jadwal ibadah terbaru dengan informasi waktu dan tempat yang jelas.
- Memungkinkan Media Center melakukan tambah, lihat, ubah, terbitkan, dan arsip konten melalui dashboard.
- Menghubungkan website dengan praktik berbagi tautan melalui WhatsApp/Facebook tanpa harus membuat integrasi API.
- Mendukung keberlanjutan melalui pelatihan, dokumentasi, pengelola cadangan, dan serah terima.

## 3. Pemangku Kepentingan dan Pengguna

| Aktor | Kebutuhan | Keterlibatan/otoritas sementara |
|---|---|---|
| Ketua Majelis Jemaat / perwakilan yang ditunjuk | Kesesuaian informasi dengan kebijakan jemaat | Validasi kebutuhan dan keputusan publikasi; kewenangan final perlu ditetapkan |
| Sekretaris Jemaat | Sumber profil, struktur, jadwal, berita, dan informasi resmi | Narasumber/penyedia data, belum otomatis administrator |
| Media Center Jemaat | Pembaruan konten tanpa memprogram | **Calon operator/admin**; peran dan jumlah orang perlu konfirmasi |
| Warga jemaat di wilayah maupun luar wilayah | Menemukan berita dan jadwal tanpa bergabung ke grup tertentu | Pengunjung tanpa login |
| Masyarakat umum | Memperoleh informasi publik tentang Jemaat Leihari | Pengunjung tanpa login |
| Kelompok 13 FILKOM UKIM | Perancangan, pengembangan, validasi, pengujian, pelatihan | Tim pelaksana selama masa KKN |
| DPL/pihak kampus | Kesesuaian kegiatan dengan ketentuan KKN dan luaran | Supervisi akademik sesuai aturan UKIM |

**Catatan:** Persona yang lebih spesifik (misalnya lansia, warga di luar daerah, admin pemula) adalah hipotesis desain sampai diwawancarai atau diuji.

## 4. Prinsip Produk

1. **Berangkat dari kebutuhan nyata**, bukan jumlah menu atau kerumitan teknologi.
2. **Mobile-first dan inklusif**, dengan keterbacaan, tombol jelas, dan navigasi sederhana.
3. **Website sebagai pusat informasi**, sedangkan WhatsApp/Facebook tetap menjadi kanal distribusi.
4. **Admin nonteknis bisa mandiri**, tanpa mengakses source code, phpMyAdmin, atau query SQL.
5. **Informasi publik harus sah dan akurat**; konten internal tidak terbuka melalui URL/API.
6. **MVP realistis untuk KKN**, dengan prioritas yang transparan dan pengendalian perubahan lingkup.
7. **Ada pemilik proses setelah serah terima**, termasuk backup dan pengelolaan akun.

## 5. Ruang Lingkup dan Prioritas

### 5.1 Skala prioritas

- **P0 — Minimum untuk rilis KKN:** tanpa ini website belum menyelesaikan masalah utama.
- **P1 — Target pengayaan jika waktu, kapasitas, dan data memungkinkan:** menambah manfaat namun tidak menghalangi rilis P0 jika mitra menyetujui.
- **P2 — Pengembangan berikutnya:** perlu validasi dan keputusan tersendiri.

> Perubahan dari fitur wajib pada PRD v2.1 menjadi P1/P2 di dokumen ini merupakan **usulan revisi ruang lingkup**, bukan penghapusan sepihak kebutuhan yang sudah disetujui. Penetapan P0 final harus dikonfirmasi kepada mitra dan DPL.

### 5.2 Fitur P0 — inti MVP

| ID | Modul | Minimum yang diharapkan |
|---|---|---|
| FR-01 | Beranda | Nama/identitas jemaat, pengantar singkat, tautan Profil/Berita/Jadwal, berita terbaru, jadwal ibadah terdekat jika ada data terbit |
| FR-02 | Profil Jemaat | Sejarah/deskripsi, alamat/kontak resmi, susunan Majelis yang sedang menjabat; foto pengurus opsional dan hanya jika berizin |
| FR-03 | Berita & Kegiatan | Daftar dan detail berita/kegiatan, tanggal terbit, isi, foto utama opsional, beberapa foto kegiatan dalam artikel bila diperlukan, URL mudah dibagikan |
| FR-04 | Jadwal Ibadah | Daftar jadwal, nama/jenis ibadah, tanggal, jam mulai, lokasi, keterangan opsional; jam selesai opsional |
| FR-05 | Autentikasi Admin | Login/logout admin, tidak ada registrasi publik, otorisasi server-side |
| FR-06 | Kelola Konten | Dashboard menambah, mengubah, melihat/pratinjau, menerbitkan, dan menarik/arsip berita; mengelola profil dan jadwal dengan formulir |
| FR-07 | Kontrol Publikasi | Data yang belum diterbitkan tidak tampak publik; aksi terbit/arsip memakai konfirmasi dan pesan hasil yang jelas |
| FR-08 | Unggah Media | Unggah foto resmi yang diperbolehkan, validasi tipe/ukuran, optimasi gambar, placeholder bila tidak ada foto |
| FR-09 | Aksesibilitas dasar | Desain responsif HP/desktop, label form, fokus keyboard, keterbacaan dan navigasi konsisten |
| FR-10 | Operasional dan Serah Terima | Backup/restore teruji, panduan admin, akun/pemilik operasional yang disepakati, pelatihan Media Center dan uji tugas |

### 5.3 Fitur P1 — tergantung validasi dan kapasitas

| ID | Fitur | Alasan status P1 |
|---|---|---|
| EN-01 | Riwayat/demisioner Majelis beserta periode dan bagan hierarki | Memerlukan struktur dan data historis resmi serta desain UI tambahan; menjadi P0 bila mitra menegaskan kebutuhan mendesak |
| EN-02 | Statistik agregat jemaat (L/P/KK/sektor), snapshot dan riwayat | Tercantum di PRD v2.1 tetapi belum dikonfirmasi ulang sebagai prioritas wawancara KKN; memerlukan validasi dan kebijakan publikasi data |
| EN-03 | Draft revisi terpisah dari versi publik untuk semua modul | Penting untuk integritas, tetapi kompleks; wajib dirancang dan diuji bila dipilih, tanpa mengubah publik secara diam-diam |
| EN-04 | Master pelayan ibadah dan penugasan banyak pelayan | Memerlukan data/aturan penugasan resmi; untuk P0 nama pelayan dapat bersifat opsional bila diizinkan mitra |
| EN-05 | Filter atau pencarian berita | Bermanfaat ketika volume konten bertambah |
| EN-06 | Multi-admin dengan hak akses granular dan audit UI lengkap | Bergantung pada struktur dan jumlah operator; audit aksi kritis tetap direkomendasikan pada P0 |

### 5.4 Di luar lingkup KKN awal (P2)

Database pribadi anggota/keluarga, pendataan sakramen, surat menyurat, keuangan jemaat, portal/login publik, impor massal data anggota, approval bertingkat, notifikasi otomatis, integrasi WhatsApp API, live streaming, sistem penjadwalan kompleks, dan modul galeri foto/video mandiri. Dokumentasi foto kegiatan terintegrasi dalam artikel berita; keputusan ini dipertahankan dari PRD v2.1.

### 5.5 Perubahan ruang lingkup

Setiap permintaan tambahan harus dicatat dengan: pemohon, kebutuhan, alasan, prioritas, estimasi dampak waktu/biaya, keputusan, dan penanggung jawab. Permintaan tanpa persetujuan tidak otomatis masuk MVP.

## 6. Kebutuhan Fungsional dan Kriteria Penerimaan

### FR-01 — Beranda publik

- Menampilkan pengantar jemaat dan akses langsung ke berita/jadwal; tidak menampilkan konten draft.
- Jika belum ada berita/jadwal, tampilkan keadaan kosong yang informatif, bukan error atau data fiktif.
- **Terima jika:** pengguna HP dapat menemukan halaman jadwal dan berita dari beranda; seluruh tautan aktif.

### FR-02 — Profil Jemaat

- Memuat deskripsi/sejarah, lokasi dan kontak resmi yang disetujui, serta daftar pengurus Majelis aktif dengan nama dan jabatan.
- Foto, sektor/unit, bagan organisasi, periode lama, dan statistik mengikuti prioritas/konfirmasi data.
- Tidak menerbitkan nomor pribadi tanpa kewenangan atau izin yang memadai.
- **Terima jika:** pengunjung melihat data Majelis aktif yang disahkan; admin dapat memperbarui data tanpa edit kode; tampilan tetap layak ketika foto tidak tersedia.

### FR-03 — Berita & dokumentasi kegiatan

- Admin mengelola judul, ringkasan opsional, isi, tanggal publikasi, foto sampul opsional, serta beberapa gambar di isi artikel bila dibutuhkan.
- Draft tidak dapat ditemukan lewat URL publik. Berita terbit mempunyai slug/URL stabil; berita yang ditarik tidak membocorkan isi.
- Editor harus mudah digunakan, mengamankan rich text dan unggahan file.
- **Terima jika:** admin non-IT dapat membuat, memeriksa, dan menerbitkan satu berita dengan foto; pengunjung membacanya pada HP; tautan dapat dibagikan melalui WhatsApp/Facebook secara manual.

### FR-04 — Informasi ibadah

- Data wajib: nama/jenis ibadah, tanggal, jam mulai, dan tempat. Jam selesai dan keterangan opsional. Pelayan/pemimpin opsional untuk P0 sampai dikonfirmasi.
- Waktu ditampilkan sesuai zona waktu resmi lokasi jemaat; format tanggal mudah dipahami.
- Jadwal dibedakan antara mendatang dan telah berlangsung menurut kebijakan tampilan; **status batal/perubahan mendadak perlu keputusan mitra**.
- **Terima jika:** admin dapat memperbarui jadwal; pengunjung melihat tanggal, waktu, tempat, dan informasi paling relevan melalui HP tanpa login.

### FR-05–FR-08 — Admin dan publikasi

- Login menggunakan mekanisme autentikasi yang aman; semua aksi edit/terbit/arsip dilindungi otorisasi di server.
- Admin mendapat menu sederhana: Ringkasan, Profil, Berita, Jadwal, Pengaturan Akun (sesuai izin).
- Tindakan **Simpan Draft**, **Terbitkan**, dan **Arsipkan/Tarik Publikasi** berbeda secara jelas; jika versi revisi belum diimplementasikan, **perubahan pada konten terbit harus dijelaskan secara eksplisit kepada admin** sebelum tersimpan dan kebijakan tersebut harus disetujui mitra.
- Form memberikan pesan validasi yang jelas; isi formulir tidak hilang karena satu kesalahan kecil.
- Unggahan file dibatasi dan diproses dengan aman; file privat/draft tidak dapat diunduh melalui jalur publik yang tidak sah.
- **Terima jika:** pengunjung tak dapat mengakses halaman admin atau konten draft; admin dapat menyelesaikan CRUD inti tanpa source code/database manual; kegagalan form tidak menghilangkan input tanpa penjelasan.

### FR-09 — UX responsif

- Ukuran dasar teks sekitar 16–18 px dan kontras diuji; tombol utama mudah disentuh dan mempunyai label tekstual.
- Seluruh halaman penting diuji setidaknya di lebar 320, 360, 390, 430, 768, dan 1440 px; tanpa horizontal overflow tak disengaja.
- Desain mempertahankan arah identitas awal **ungu #663F72, emas #D8BD72, putih #FFFFFF** sebagai *usulan visual PRD v2.1*, yang harus disetujui pihak jemaat; kombinasi tetap wajib memenuhi kontras.
- **Terima jika:** tugas mencari jadwal, membaca berita, dan menerbitkan berita dapat dilakukan di perangkat yang ditentukan tanpa elemen rusak.

### FR-10 — Pelatihan dan serah terima

- Minimal satu sesi pelatihan terstruktur dengan Media Center, termasuk praktik tambah/edit/arsip berita dan jadwal, unggah foto, login/logout, serta tindakan ketika ada masalah.
- Panduan admin dengan tangkapan layar, panduan backup/restore, dan informasi penanggung jawab pemeliharaan diserahkan.
- **Terima jika:** setidaknya satu operator Media Center (idealnya operator utama dan cadangan) menyelesaikan skenario UAT tanpa intervensi pengembang; jumlah peserta dan ambang keberhasilan final disepakati sebelum pelatihan.

## 7. Alur Pengguna dan Tata Kelola Konten

### 7.1 Alur pengunjung

Beranda → pilih Berita/Jadwal/Profil → baca informasi terbit → bila perlu bagikan tautan melalui aplikasi pesan/media sosial. Tidak membutuhkan akun.

### 7.2 Alur Media Center — berita

Login → pilih Berita → Tambah Berita → isi judul/isi/foto → Simpan Draft → Pratinjau → Terbitkan → sistem memvalidasi → muncul di publik → bagikan tautan melalui WhatsApp/Facebook bila diperlukan.

**Cabang:** jika validasi gagal, tampilkan penjelasan dan kembali ke formulir; jika menarik publikasi, minta konfirmasi; jika koneksi putus, berikan status hasil penyimpanan yang tidak menyesatkan.

### 7.3 Alur sumber dan verifikasi informasi (perlu keputusan operasional)

Sekretariat/Majelis atau sumber resmi → menyerahkan informasi → pemeriksaan ketepatan oleh pihak berwenang → Media Center memublikasikan → Media Center memantau perubahan/koreksi. **Siapa pemeriksa/pemberi izin dan apakah persetujuan dilakukan di luar sistem belum dipastikan.** Tidak diasumsikan perlu fitur approval bertingkat pada MVP.

### 7.4 Rekomendasi pembagian tanggung jawab

- **Penanggung jawab isi:** perwakilan Majelis/sekretariat yang ditunjuk.
- **Admin utama:** Media Center yang ditunjuk.
- **Admin cadangan:** personel pengganti agar pembaruan tidak terhenti.
- **Pemeliharaan teknis:** ditetapkan bersama mitra sebelum handover; dukungan KKN tidak diasumsikan berlangsung tanpa batas.

Model ini adalah **usulan**, bukan penetapan jabatan personel.

## 8. Kebutuhan Non-Fungsional

| ID | Aspek | Kebutuhan / cara verifikasi |
|---|---|---|
| NFR-01 | Keamanan | HTTPS produksi; hashing password; proteksi CSRF; validasi input; otorisasi server-side; pembatasan percobaan login |
| NFR-02 | Privasi | Hanya informasi resmi berizin; kontak personal dan data individu bukan konten publik otomatis |
| NFR-03 | Kinerja | Optimasi gambar dan pagination untuk daftar; target waktu akses ditetapkan setelah pengukuran pada hosting/jaringan mitra |
| NFR-04 | Responsivitas | Uji viewport dan perangkat nyata bila tersedia, tanpa konten terpotong |
| NFR-05 | Aksesibilitas | Target WCAG 2.2 AA untuk komponen inti, perlu diuji; bukan klaim lulus otomatis |
| NFR-06 | Keandalan | Validasi transaksi publikasi, penanganan error, log kesalahan tanpa data sensitif |
| NFR-07 | Pemulihan | Backup database dan unggahan; latihan restore dan dokumentasi sebelum produksi |
| NFR-08 | Pemeliharaan | Kode terstruktur, Git, migrations, petunjuk setup, dan kepemilikan akun/hosting jelas |
| NFR-09 | Kesesuaian | Website publik tersedia tanpa login; halaman/admin privat tidak terindeks atau terbaca secara tidak sah |
| NFR-10 | Usability | Operator mitra menyelesaikan tugas UAT yang disepakati tanpa bergantung pada pengembang |

## 9. Rancangan Informasi, Halaman, dan UI

### 9.1 Sitemap usulan — publik

- **Beranda** `/`
- **Profil Jemaat** `/profil` (sejarah, kontak resmi, susunan Majelis aktif; subhalaman riwayat/statistik hanya bila P1 disetujui)
- **Berita & Kegiatan** `/berita`, `/berita/{slug}` (foto kegiatan berada di artikel; tidak ada galeri mandiri pada MVP)
- **Jadwal Ibadah** `/jadwal-ibadah` dan halaman detail bila diperlukan

### 9.2 Sitemap usulan — internal

- `/admin/login`
- `/admin/dashboard`
- `/admin/profil`
- `/admin/berita` (daftar/tambah/edit/pratinjau)
- `/admin/jadwal` (daftar/tambah/edit)
- `/admin/akun` (hak akses terbatas)

Route final mengikuti rancangan CodeIgniter 4 setelah keputusan teknis, bukan menyalin route Laravel lama.

### 9.3 Daftar template layar minimum untuk wireframe dan prototype

| ID | Layar | Desktop | Mobile | Kondisi yang wajib diperlihatkan |
|---|---|---|---|---|
| UI-01 | Beranda | Ya | Ya | Konten ada dan kosong |
| UI-02 | Profil Jemaat | Ya | Ya | Foto tersedia/tidak tersedia |
| UI-03 | Daftar berita | Ya | Ya | Banyak berita, kosong |
| UI-04 | Detail berita | Ya | Ya | Artikel panjang, beberapa foto |
| UI-05 | Jadwal ibadah | Ya | Ya | Jadwal mendatang, tanggal/jam panjang |
| UI-06 | Login admin | Ya | Ya | Login salah/berhasil |
| UI-07 | Dashboard admin | Ya | Ya | Ringkasan dan navigasi |
| UI-08 | Daftar kelola berita | Ya | Ya | Draft/terbit/arsip |
| UI-09 | Form berita | Ya | Ya | Error validasi, upload, pratinjau |
| UI-10 | Kelola jadwal | Ya | Ya | Tambah/edit, validasi waktu |
| UI-11 | Kelola profil | Ya | Ya | Edit informasi dan pengurus |
| UI-12 | Konfirmasi & status | Ya | Ya | Sukses/gagal/arsip/publikasi |

**Artefak UI:** information architecture, sitemap, low-fidelity wireframe, mini design system (warna, font, tombol, form, card, status), mockup high-fidelity, prototype alur utama, evaluasi bersama Media Center.

## 10. Rancangan Sistem dan Database (Untuk Diturunkan ke ERD)

### 10.1 Arsitektur usulan

Browser pengunjung/admin → server web HTTPS → **CodeIgniter 4 (MVC)** → **MySQL** untuk data terstruktur dan storage untuk file media. Bootstrap membantu antarmuka responsif. Gunakan version control Git, environments terpisah, dan migrations untuk perubahan skema.

**Keputusan bersyarat:** PRD v2.1 menyebut *repository Laravel + Livewire/Flux UI + SQLite lokal*. Jika proyek Laravel tersebut nyata dan telah berisi fungsionalitas yang signifikan, lakukan inventarisasi/audit sebelum keputusan pindah stack; **jangan melakukan migrasi otomatis atau menghapus kode/database**.

### 10.2 Entitas kandidat P0

- `users` — akun admin.
- `roles` atau mekanisme setara — hak akses; penyederhanaan satu peran admin dapat dipakai jika sesuai kebutuhan.
- `site_profiles` / `site_settings` — informasi profil dan kontak publik.
- `majelis_members` — nama/jabatan/foto opsional pengurus aktif; model periode bila P1 dipilih.
- `posts` — berita dan kegiatan, status publikasi, slug, isi, penulis, waktu terbit.
- `media_files` — identitas file aman, relasi dengan berita/profil bila diperlukan.
- `service_schedules` — nama ibadah, tanggal, jam mulai, jam selesai nullable, lokasi, deskripsi, status.
- `audit_logs` (minimal peristiwa penting) — pencatatan aktivitas perubahan/publikasi.

### 10.3 Entitas kandidat P1

`majelis_periods`, `majelis_positions`, `statistics_snapshots`, `sector_statistics`, `sectors`, `service_types`, `servants`, `service_assignments`, serta tabel versi publikasi apabila versioning lengkap dipilih. Nama tabel merupakan kandidat, **bukan keputusan skema final**.

### 10.4 Aturan data penting

1. Data demo berupa dummy/sintetis dan diberi label; jangan memasukkan data identitas nyata ke repository publik.
2. Data draft/arsip tidak boleh tersedia melalui query/route API publik maupun direct file URL.
3. Status dan waktu publikasi dicatat; tindakan terbit/arsip sebaiknya diaudit.
4. Jika riwayat periode Majelis diimplementasikan, hanya satu periode boleh ditetapkan aktif; hubungan jabatan harus mendukung lebih dari satu pemegang.
5. Jika statistik diimplementasikan, validasi `total = laki-laki + perempuan = jumlah per sektor` hanya berlaku setelah kebijakan satu orang tepat satu sektor diverifikasi; kepala keluarga metrik terpisah.
6. Jika master pelayan/jenis ibadah diimplementasikan, nonaktif/perubahan master tidak boleh merusak makna jadwal historis.
7. Versioning draft vs terbit untuk tabel induk dan relasi media/penugasan dirancang terlebih dahulu bila EN-03 disetujui.
8. Finalisasi PK/FK/indeks, tipe kolom, constraint, dan cascade/soft-delete dilakukan dalam ERD, data dictionary, dan rancangan migration.

## 11. Rencana Artefak Perancangan

| Urut | Dokumen/artefak | Tujuan |
|---|---|---|
| 1 | Catatan wawancara dan keputusan kebutuhan | Menjaga bukti masalah nyata |
| 2 | PRD tervalidasi | Menetapkan apa dan mengapa |
| 3 | SRS ringan + daftar kebutuhan ID FR/NFR | Merinci fungsi dan kondisi penerimaan |
| 4 | Use Case Diagram dan deskripsi use case kritis | Memetakan aktor dan tugas |
| 5 | User Flow / Activity Diagram | Memetakan langkah dan cabang kegagalan |
| 6 | Sitemap & information architecture | Menentukan hierarki halaman |
| 7 | Arsitektur sistem + matriks role/permission | Menetapkan batas subsistem dan akses |
| 8 | ERD + skema + data dictionary | Menentukan struktur penyimpanan |
| 9 | Wireframe → mockup → prototype | Menguji rancangan UI sebelum coding |
| 10 | Test case / UAT / hasil pengujian | Membuktikan kebutuhan terpenuhi |
| 11 | Panduan admin, deployment, backup dan handover | Menjamin keberlanjutan |

**Prinsip traceability:** setiap kebutuhan FR mempunyai minimal satu layar/alur dan satu test case yang dapat memverifikasi implementasinya.

## 12. Rencana Pelaksanaan dan Urutan Pengembangan

| Fase | Fokus | Kriteria keluar |
|---|---|---|
| 0 — Validasi kebutuhan | Kumpulkan data resmi; konfirmasi lingkup dan operator | PRD disetujui pihak yang berwenang |
| 1 — Desain | Use case, flow, ERD, sitemap, wireframe, prototype | Desain inti ditinjau tim dan Media Center |
| 2 — Fondasi | CodeIgniter 4, database, auth/admin, UI Bootstrap | Login aman, CRUD dasar dan migration diuji |
| 3 — Konten P0 | Profil, berita/kegiatan, jadwal, beranda | Semua alur P0 berjalan dengan data dummy |
| 4 — Integrasi & QA | Uji keamanan, responsivitas, fungsional, backup/restore | Tidak ada bug kritis; UAT siap |
| 5 — Pelatihan & serah terima | Masukkan data resmi terverifikasi, pelatihan, panduan | Media Center dapat mengelola konten; dokumen dan akses diserahkan sesuai tata kelola |

**Rujukan jadwal akademik:** Template Proposal KKNT FILKOM 2026 menyebut tahap kerja kampus **29 Oktober–8 November 2026** dan tahap lapangan II **9–12 November 2026**. Tanggal tersebut dipakai sebagai referensi awal penyusunan jadwal, **bukan jaminan semua kegiatan Kelompok 13 sudah disahkan untuk tanggal itu**. Penjadwalan terperinci harus dikonfirmasi dengan DPL/mitra.

## 13. Pengujian dan Definisi Selesai

### 13.1 Test case minimum

| ID | Skenario | Hasil yang diterima |
|---|---|---|
| TC-01 | Pengunjung membuka website di HP | Menu, beranda, berita, jadwal dapat dibaca tanpa login |
| TC-02 | Pengunjung mencoba URL admin tanpa login | Ditolak/diarahkan ke login tanpa membocorkan konten |
| TC-03 | Admin menambah berita dengan foto | Draft tersimpan; publik hanya melihat ketika telah diterbitkan |
| TC-04 | Admin mengunggah file tidak diizinkan | Ditolak dengan pesan aman dan jelas |
| TC-05 | Admin mengarsipkan berita | Konten tidak terbaca dari URL publik lama; internal tetap sesuai kebijakan |
| TC-06 | Admin menambah jadwal tanpa jam selesai | Jadwal dapat diterbitkan dan hanya jam mulai ditampilkan |
| TC-07 | Admin memasukkan input tidak valid | Validasi jelas; tidak ada perubahan publik yang keliru |
| TC-08 | HP 320–430 px membuka artikel panjang | Tidak ada overflow horizontal tak disengaja |
| TC-09 | Database/file dipulihkan dari backup uji | Data dan gambar kunci dapat diakses lagi di lingkungan uji |
| TC-10 | Operator Media Center menjalankan tugas UAT | Dapat mengubah berita/jadwal tanpa mengedit kode atau database |

### 13.2 Definition of Done untuk fitur

Selesai hanya jika: memenuhi FR terkait; diuji untuk jalur normal dan gagal; aman menurut akses peran; berfungsi pada HP; tidak memakai data fiktif sebagai data resmi; mempunyai dokumentasi yang diperlukan; dan diuji dengan perwakilan Media Center untuk alur admin kritis.

### 13.3 Indikator keberhasilan KKN (target diusulkan, perlu baseline)

- Seluruh fitur **P0 yang disetujui** tersedia dan dapat diakses publik/privat sesuai izin.
- Setidaknya satu operator Media Center berhasil membuat/mengedit/menerbitkan berita dan memperbarui jadwal dalam UAT tanpa bantuan teknis; upayakan dua operator untuk keberlanjutan.
- Tersedia panduan operasional, backup yang **pernah diuji restore**, dan berita acara/daftar periksa serah terima.
- Perwakilan mitra mengonfirmasi kebenaran isi awal dan kesesuaian alur pengelolaan.
- Setelah peluncuran, konsistensi pembaruan informasi **perlu dipantau oleh mitra**; keberhasilan jangka panjang tidak dapat diklaim hanya dari demo atau penyerahan software.

## 14. Keamanan, Privasi, dan Publikasi Data

- Terapkan HTTPS, autentikasi aman, CSRF protection, server-side authorization, validation, password hashing, pembatasan login, dan prinsip hak akses minimum.
- Sanitasi rich text dan validasi MIME/ukuran file; jangan mengeksekusi file unggahan. Terapkan storage permission aman.
- Tampilkan hanya data yang mendapat izin publikasi; khususnya foto pengurus, foto anak, kontak pribadi, dan informasi internal.
- Jangan mengumpulkan data individu jemaat bila tidak diperlukan untuk profil/berita/jadwal.
- Tetapkan prosedur koreksi, penghapusan, backup, perubahan pengelola, dan retensi data bersama pihak berwenang.
- Jangan menyimpan kredensial produksi di dokumen publik, chat grup, repository, atau bahan pelatihan.
- Jika menggunakan penyedia hosting/domain, pencatatan akun pengelola, biaya perpanjangan, dan kewenangan akses harus jelas sebelum handover.

## 15. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Lingkup meluas karena modul historis/statistik kompleks | Target KKN tidak selesai | Tetapkan P0/P1/P2, perubahan tertulis |
| Salah memilih stack tanpa audit aset | Pekerjaan ulang dan kehilangan fitur | Inventaris repo Laravel lama, putuskan CI4/MySQL/Bootstrap secara sadar |
| Data/foto resmi terlambat diserahkan | Website kosong saat UAT | Minta sejak awal, pakai dummy berlabel sampai validasi |
| Media Center tetap bergantung pada mahasiswa | Website tidak diperbarui | Latihan berbasis tugas, panduan, admin cadangan, handover |
| Koneksi HP lambat | Informasi sulit diakses | Optimasi gambar dan ukuran halaman |
| Berita/jadwal tidak diverifikasi | Informasi publik keliru | Tetapkan alur penyetujuan konten di luar/di dalam sistem |
| Akun/admin disalahgunakan | Konten tidak sah/kebocoran | Hak akses minimum, autentikasi, audit, prosedur reset |
| Website berjalan tetapi hosting kedaluwarsa | Layanan berhenti | Konfirmasi domain, hosting, biaya, penanggung jawab |
| Data hilang saat deployment/perubahan | Kehilangan informasi | Backup, uji restore, migration non-destruktif dan staging |
| Tidak tersedia data statistik/riwayat lengkap | Fitur P1 tidak akurat | Jadikan P1 sampai tersedia data dan persetujuan |

## 16. Luaran KKN dan Keberlanjutan

### 16.1 Luaran teknis

1. Website publik Jemaat Leihari sesuai fitur P0 yang disetujui.
2. Dashboard admin untuk Media Center.
3. Kode sumber dan skema database/migration yang terdokumentasi.
4. Desain perancangan (use case, flow, ERD, sitemap, wireframe/prototype), hasil pengujian, serta panduan deployment/backup.

### 16.2 Luaran pemberdayaan mitra

1. Panduan praktis admin Media Center.
2. Sesi pelatihan dan lembar hasil UAT.
3. Daftar penanggung jawab, mekanisme pembaruan konten, akun/akses, backup, dan kanal dukungan.
4. Berita acara serah terima serta rencana keberlanjutan yang disetujui bersama.

### 16.3 Aspek akademik KKN

Dokumentasikan permasalahan dan hasil wawancara, perencanaan kegiatan, pelaksanaan, capaian, hambatan, evaluasi manfaat, dan keberlanjutan agar dapat dipakai pada proposal serta laporan KKN sesuai ketentuan UKIM. Hubungan dengan SDGs, bila diwajibkan proposal, **harus dipilih dan dijustifikasi berdasarkan dampak nyata**; jangan menetapkan nomor SDG tanpa pembahasan tim/DPL.

**Aset/hak pakai:** Template Proposal KKNT FILKOM menyebut pengaturan hak cipta dan hak pakai produk dalam format proposal. Sebelum serah terima, pastikan ketentuan resmi kampus, izin pihak mitra, hak akses domain/hosting, dan kepemilikan akun tercatat; PRD ini tidak menetapkan kepemilikan hukum secara mandiri.

## 17. Data dan Keputusan yang Wajib Dikonfirmasi

### 17.1 Data yang diminta dari sekretaris / Majelis

- Nama resmi jemaat, ringkasan sejarah, alamat, kontak resmi, logo dan foto gedung gereja berizin.
- Susunan Majelis aktif: nama/jabatan dan izin foto; daftar periode lama bila EN-01 akan diambil.
- Contoh 3–5 berita/kegiatan beserta judul, narasi, tanggal, dan foto yang boleh dipublikasikan.
- Jadwal ibadah terkini, jenis, jam, lokasi, pemimpin/pelayan jika ingin ditampilkan.
- Kanal Facebook dan kontak publik untuk informasi serta kebijakan penggunaan materi dari WhatsApp.
- Statistik agregat resmi (hanya bila EN-02 disetujui), beserta tanggal acuan dan rincian sektor.

### 17.2 Keputusan terbuka (dengan pemilik keputusan)

| ID | Keputusan | Usulan pemilik keputusan |
|---|---|---|
| DEC-01 | Konfirmasi kebutuhan P0/P1 dan cakupan riwayat Majelis/statistik | Majelis + Kelompok 13 |
| DEC-02 | Pilih stack final: CI4/MySQL/Bootstrap atau kelanjutan Laravel setelah audit | Kelompok 13 + DPL |
| DEC-03 | Nama personel Media Center, admin utama/cadangan, kebutuhan perangkat | Media Center + Majelis |
| DEC-04 | Sumber informasi resmi dan siapa yang menyetujui konten | Sekretariat + Majelis |
| DEC-05 | Apakah edit konten terbit harus memakai draft revisi terisolasi pada rilis awal | Media Center + Kelompok 13 |
| DEC-06 | Apakah nama pelayan/pemimpin pada jadwal wajib dan siapa sumbernya | Sekretariat + Media Center |
| DEC-07 | Domain, hosting, biaya tahunan, teknis pemeliharaan | Majelis + Kelompok 13 |
| DEC-08 | Aturan izin foto, kontak, retensi dan koreksi data | Majelis + Media Center |
| DEC-09 | Desain identitas visual, logo, palet, dan label menu | Media Center + Majelis |
| DEC-10 | Tanggal pelatihan, peserta UAT, kriteria lulus dan serah terima | Kelompok 13 + Media Center + DPL |

## 18. Matriks Keterlacakan Awal

| Requirement | Alur / layar utama | Data kandidat | Test |
|---|---|---|---|
| FR-01 | UI-01 | posts, service_schedules, site_profiles | TC-01 |
| FR-02 | UI-02, UI-11 | site_profiles, majelis_members | TC-01, TC-07 |
| FR-03 | UI-03, UI-04, UI-08, UI-09 | posts, media_files | TC-03, TC-04, TC-05, TC-08 |
| FR-04 | UI-05, UI-10 | service_schedules | TC-06, TC-07 |
| FR-05–FR-07 | UI-06 s.d. UI-12 | users, roles, audit_logs | TC-02, TC-03, TC-05 |
| FR-08 | UI-09 | media_files | TC-04 |
| FR-09 | UI-01 s.d. UI-12 | — | TC-01, TC-08 |
| FR-10 | Alur pelatihan/handover | backup dan dokumentasi | TC-09, TC-10 |

## 19. Kriteria Persetujuan PRD

Dokumen dapat dinaikkan dari **Draft** menjadi **Baseline Disetujui** setelah:

1. Hasil wawancara utama dicatat dan diperiksa oleh perwakilan mitra.
2. P0/P1/P2 dan keputusan kunci DEC-01 s.d. DEC-10 diselesaikan atau ditetapkan pemilik/target waktunya.
3. Stack dan status repository yang sudah ada diputuskan secara sadar.
4. Peran Media Center, sumber informasi, dan mekanisme publikasi disetujui.
5. Batas deliverable, skenario UAT, keamanan minimum, backup, hosting dan keberlanjutan dinyatakan jelas.
6. Nama/tanggal persetujuan Kelompok 13, DPL bila perlu, dan pihak mitra dicatat dalam dokumen terpisah atau lembar pengesahan.

---

## Lampiran A — Perubahan Utama dari PRD v2.1

| Area | PRD v2.1 | Draft v3.0 |
|---|---|---|
| Sasaran proyek | Aplikasi Leihari dengan developer tunggal | Program kerja **KKN Tematik Kelompok 13** bersama mitra |
| Teknologi | Laravel + Livewire/Flux + Blade/Tailwind + SQLite lokal | **CI4 + MySQL + Bootstrap sebagai usulan**, dengan audit aset Laravel lama lebih dulu |
| Dasar masalah | Terutama kanal Facebook dan kebutuhan pusat informasi | Memasukkan wawancara awal: **WhatsApp, Facebook, kendala pembaruan, warga di luar wilayah** |
| Keberhasilan | Kualitas teknis dan kelengkapan modul | Kualitas teknis **serta pelatihan, kemampuan admin, serah terima, keberlanjutan** |
| Keluasan MVP | Periode/riwayat Majelis, statistik historis, versioning kompleks semuanya di MVP | **P0/P1/P2 diusulkan**, fitur historis/kompleks menunggu validasi mitra |
| Operasional | Admin umum | **Media Center** sebagai calon pengelola dengan tata kelola jelas |
| Galeri | Foto di artikel; bukan modul mandiri | **Dipertahankan** |
| Validitas data | Tidak mengarang data resmi | Dipertahankan dan diperjelas dengan register bukti serta keputusan |

## Lampiran B — Prinsip Membaca Draft Ini

- **Telah dilaporkan oleh tim:** penggunaan WhatsApp/Facebook, masalah pembaruan, kebutuhan warga luar wilayah, keberadaan Media Center.
- **Diusulkan dalam dokumen:** visi produk, prioritas P0/P1/P2, struktur menu, stack CI4/MySQL/Bootstrap, alur tata kelola, metrik UAT.
- **Belum diverifikasi:** susunan pengurus/lembaga, statistik resmi, perilaku admin sebenarnya, ketersediaan domain/hosting, kepemilikan repo Laravel lama, persetujuan foto, jadwal final KKN, dan ketentuan serah terima.

**Dokumen ini tidak menyatakan website sudah selesai, fitur sudah berjalan, mitra sudah menyetujui, atau pelatihan sudah dilakukan.**
