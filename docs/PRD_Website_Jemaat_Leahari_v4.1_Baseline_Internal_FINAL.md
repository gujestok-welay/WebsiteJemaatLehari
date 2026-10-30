# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## WEBSITE INFORMASI DAN PUBLIKASI DIGITAL JEMAAT LEAHARI

**KKN Tematik Kelompok 13 — Fakultas Ilmu Komputer, Universitas Kristen Indonesia Maluku (UKIM)**

| Informasi dokumen | Keterangan |
|---|---|
| Versi | **4.1 — FINAL BASELINE INTERNAL** |
| Tanggal penyusunan | **29 Oktober 2026** |
| Status | **Acuan kerja internal Kelompok 13 berdasarkan validasi A–H dan kesepakatan P0 secara lisan yang dilaporkan.** Tidak dimaksudkan sebagai bukti tanda tangan/pengesahan tertulis mitra. |
| Mitra | Jemaat Leahari (GPM) |
| Pelaksana | KKN Tematik Kelompok 13, FILKOM UKIM |
| Pengelola setelah KKN | Media Center Jemaat Leahari |
| DPL | Ibu Eka (hadir mendampingi wawancara, menurut laporan kelompok) |
| Sasaran rilis/demonstrasi/pelatihan/serah terima | **9 November 2026** (target kelompok) |
| Platform | Website publik dan panel administrasi |
| Teknologi yang sedang dipakai | PHP, CodeIgniter 4, MySQL, Bootstrap 5, HTML/CSS/JavaScript |
| Bahan penyusunan | PRD v3.0 tanggal 24 Oktober 2026; PRD v4.0 tanggal 28 Oktober 2026; jawaban validasi kebutuhan Bagian A–H yang diberikan Kelompok 13 |
| Pemakaian | Acuan pengembangan website, pengujian, demo, dan pemeriksaan kesesuaian proposal KKN |

> **Keputusan ruang lingkup:** fitur **P0 dinyatakan tetap (FIX) sebagai baseline internal**, mengikuti penegasan Kelompok 13 bahwa cakupan utama sudah disepakati secara lisan dengan pihak jemaat. **Tidak ada kewajiban mengonfirmasi ulang fitur P0 satu per satu.** Hal yang belum ditentukan (nama operator, kredensial, rincian hosting, kebijakan dukungan) dikelola sebagai keputusan operasional terpisah, bukan alasan membuka ulang ruang lingkup fitur. Dokumen ini tidak merekayasa tanda tangan atau klaim persetujuan tertulis.

### Kode status dan asal informasi

- **[M] Kesepakatan/keterangan mitra yang dilaporkan:** bersumber dari wawancara atau pernyataan pihak jemaat menurut Kelompok 13; kesepakatan awal bersifat lisan.
- **[T] Keputusan atau inisiatif Kelompok 13:** berlaku sebagai rancangan kerja internal, tidak otomatis merupakan permintaan mitra.
- **[R] Persyaratan teknis/rekomendasi implementasi:** untuk keamanan, operasional, atau kualitas website; tidak mengubah fitur bisnis yang disepakati.
- **[O] Keputusan operasional terbuka:** membutuhkan penanggung jawab dan waktu keputusan; tidak mengulang validasi seluruh P0.
- **[I] Status kode/pengujian lokal yang pernah dilaporkan:** bukan bukti aplikasi telah siap produksi.

---

## 1. Ringkasan eksekutif

### 1.1 Latar belakang

Informasi Jemaat Leahari saat ini tersebar melalui WhatsApp dan Facebook, sedangkan pembaruan Facebook tidak selalu konsisten [M]. Akibatnya, warga jemaat, termasuk yang tinggal di luar daerah, belum selalu mudah memperoleh informasi terbaru serta menelusuri berita dan jadwal ibadah sebelumnya [M]. Kelompok 13 mengusulkan website sebagai pusat informasi publik; usulan tersebut kemudian dibahas dan diterima secara lisan dalam wawancara dengan pimpinan jemaat [M].

### 1.2 Solusi

Website menyajikan **Beranda, Profil Jemaat, Berita/Kegiatan, dan Jadwal Ibadah** tanpa login. Panel admin memungkinkan **Media Center** mengelola konten setelah KKN tanpa mengubah kode maupun database secara manual. Website melengkapi WhatsApp/Facebook, bukan menggantikan keduanya.

### 1.3 Tujuan

1. Menyediakan pusat informasi Jemaat Leahari yang jelas dan dapat diakses publik, terutama lewat HP.
2. Menampilkan profil resmi, susunan dan periode Majelis, statistik agregat terverifikasi, berita, serta jadwal ibadah.
3. Membuat berita dan informasi historis mudah ditemukan kembali.
4. Memungkinkan Media Center menambah, membaca, mengubah, menerbitkan, dan mengarsipkan konten secara mandiri.
5. Menyelesaikan pengujian, pelatihan, backup, dokumentasi, dan serah terima pada target **9 November 2026**.

### 1.4 Indikator keberhasilan

- Pengunjung dapat membuka semua halaman publik dan menemukan berita/jadwal dari perangkat HP.
- Informasi resmi yang tampil telah diverifikasi sumbernya; tidak ada data dummy yang disajikan sebagai fakta pada rilis.
- Operator Media Center mampu menyelesaikan tugas administrasi inti melalui dashboard tanpa programmer.
- Konten draft/arsip tidak tampil publik; akun admin terlindungi.
- Backup dapat dipulihkan, dan paket serah terima tersedia.

## 2. Dasar keputusan: rangkuman validasi A–H

| Bagian | Fakta/keputusan relevan | Klasifikasi |
|---|---|---|
| A — Dasar proyek | Informasi tersebar di WhatsApp/Facebook; website diusulkan Kelompok 13; Ketua Majelis mengharapkan akses informasi bagi warga dan diaspora. Wawancara dihadiri 10 dari 11 anggota dan DPL. | [M] |
| B — Halaman utama | Beranda, Profil, Berita, Jadwal Ibadah; sejarah, visi–misi, struktur Majelis, sektor/unit, statistik; periode aktif saat awal, riwayat ditambahkan bertahap. | [M] |
| C — Berita | Media Center sudah membuat konten sosial; berita internal dikelola Media Center; konfirmasi publikasi dari Ketua/Wakil/Sekretaris di luar sistem; pencarian/filter dan arsip diperlukan. | [M] |
| C — Berita eksternal | Liputan dari Sinode GPM/media lain dengan ringkasan mandiri, sumber dan tautan asli adalah ide Kelompok 13, belum menjadi kebutuhan mitra yang disepakati. | [T] |
| D — Profil | Sekretaris Jemaat sebagai sumber/verifikator data; nama/jabatan/foto Majelis dilaporkan diizinkan; statistik hanya yang sudah valid; riwayat periode bisa dilengkapi Media Center kemudian. | [M] |
| E — Admin | Media Center ditugaskan mengelola website; seluruh operator diharapkan mampu CRUD; jumlah personel dan admin utama belum ditentukan; persetujuan di luar website; perubahan berita terbit langsung tampil. | [M]/[T]/[O] |
| F — Desain | Palet ungu/krem/gading dan referensi website lain dipilih kelompok; logo GPM dan foto gereja diberikan serta diizinkan Sekretaris; rancangan belum didemokan. | [T]/[M] |
| G — Keberlanjutan | Domain/hosting disediakan fakultas; hosting ditanggung satu tahun, sesudahnya jemaat memutuskan perpanjangan; pelatihan direncanakan sebelum akhir KKN. | [M]/[T] |
| H — Persetujuan & tenggat | Fitur dibahas dan disetujui lisan; rekaman wawancara tersedia sebagian; DPL hadir dan mengetahui proyek; target demo, pelatihan dan serah terima 9 November 2026; penerimaan bersama Media Center dan Majelis. | [M]/[T] |

**Batas bukti:** persetujuan awal dilaporkan terjadi secara lisan. Rekaman dimulai dari pembicaraan Wakil Ketua Majelis; bagian percakapan Ketua Majelis sebelumnya tidak seluruhnya terekam. Tidak ada klaim bahwa seluruh butir dokumen telah ditandatangani secara formal.

## 3. Pengguna, kewenangan, dan proses editorial

| Pihak | Peran |
|---|---|
| Pengunjung umum, warga jemaat, diaspora | Membaca halaman publik tanpa membuat akun; mencari berita dan memeriksa jadwal. |
| Media Center | Operator konten: CRUD profil, berita, struktur/periodisasi Majelis, statistik, dan jadwal berdasarkan informasi resmi. |
| Sekretaris Majelis | Menyediakan/memeriksa data resmi seperti sejarah, visi–misi, kontak, struktur, statistik, dan dokumen pendukung. |
| Ketua/Wakil/Sekretaris Majelis | Pihak yang dapat memberikan konfirmasi publikasi konten; konfirmasi berlangsung di luar website. |
| Ketua, Wakil, Sekretaris Majelis bersama Media Center | Pihak yang direncanakan melakukan penilaian/penerimaan website saat serah terima. |
| KKN Tematik Kelompok 13 | Analisis, perancangan, pembangunan, pengujian, dokumentasi, demo, dan pelatihan. |
| DPL/Fakultas | Pendampingan akademik dan penyediaan infrastruktur sesuai ketentuan fakultas. |

**Aturan publikasi P0:** Media Center menyiapkan konten → menyimpan draft → melakukan pratinjau → mendapatkan konfirmasi yang diperlukan dari pimpinan di luar website → menerbitkan. Persetujuan elektronik bertingkat **tidak diperlukan**. Edit berita terbit dapat langsung terlihat publik setelah disimpan; perubahan substansial tetap mengikuti tata cara konfirmasi isi [R].

**Akun admin:** jangan memakai satu password untuk banyak orang atau membatasi login menjadi bergantian. Solusi teknis yang direkomendasikan adalah **akun individual** bagi setiap operator yang ditunjuk, dengan akses CRUD konten setara bila itu keputusan Media Center; pengelolaan akun hanya untuk admin utama [R]. Jumlah akun serta nama admin utama/cadangan **masih [O]**. Rilis minimum memerlukan setidaknya satu akun admin aktif yang diserahkan secara aman; jika ada beberapa operator, gunakan akun masing-masing.

## 4. Ruang lingkup — P0 tetap (FIX)

**Definisi P0:** kemampuan yang menjadi target pengerjaan dan pengujian untuk serah terima. Data historis/angka yang belum diterima tidak boleh direkayasa; **fungsi mengelolanya tetap harus tersedia** agar Media Center dapat mengisi setelah KKN.

| ID | Modul P0 | Kemampuan minimum yang wajib tersedia |
|---|---|---|
| **FR-01** | Beranda | Identitas/hero jemaat, pengantar, akses ke Profil/Berita/Jadwal, berita terbaru, ringkasan jadwal mendatang jika ada. |
| **FR-02** | Profil Jemaat | Sejarah, visi–misi, alamat dan Google Maps, kontak resmi, sektor/unit bila datanya sah; informasi dapat diperbarui dari admin. |
| **FR-03** | Struktur dan periode Majelis | Tampil susunan aktif (nama/jabatan/foto berizin); publik dapat melihat periode sebelumnya **bila data tersedia**; admin dapat menambah/mengubah periode dan anggota untuk pengisian setelah KKN. |
| **FR-04** | Statistik jemaat | Tampil hanya angka agregat yang sudah divalidasi; admin dapat memperbarui angka, label, dan periode datanya; bila belum tersedia, bagian tersebut disembunyikan/diberi keadaan kosong yang jujur. |
| **FR-05** | Berita dan kegiatan internal | Daftar, kartu berita, detail artikel dengan URL sendiri, tanggal, ringkasan, gambar berizin, arsip yang tetap dapat ditemukan lewat pencarian/filter sesuai aturan tampilan. |
| **FR-06** | Pencarian/filter berita | Cari judul/isi yang relevan dan filter status/jenis/kategori seperlunya; berita lama yang masih layak publik tetap ditemukan, tanpa membocorkan draft atau berita ditarik. |
| **FR-07** | Jadwal Ibadah | Tampilkan/kelola **jenis, tanggal, waktu, lokasi**; **nama pelayan/pemimpin ibadah opsional**; catatan/perubahan bila diperlukan. |
| **FR-08** | Admin dan autentikasi | Login, logout, proteksi sesi dan rute admin, tidak ada registrasi publik; operator sah dapat membuka dashboard. |
| **FR-09** | CMS/CRUD terpadu | Media Center mengelola berita, profil, Majelis/periodenya, statistik, dan jadwal melalui formulir tanpa menyunting source code atau phpMyAdmin. |
| **FR-10** | Status publikasi | Draft, pratinjau, terbit, arsip/tarik publikasi; status ditampilkan jelas; konten yang tidak layak publik tidak terbuka melalui URL langsung. |
| **FR-11** | Media/gambar | Unggah dan pilih gambar yang diizinkan, validasi tipe/ukuran, fallback, dan pengamanan penyimpanan. |
| **FR-12** | UX, responsif, aksesibilitas | Halaman mudah dipakai di HP, teks jelas, keyboard/fokus bisa digunakan, tidak ada overflow penting, animasi tidak menghalangi isi. |
| **FR-13** | Kesiapan produksi & serah terima | Pengujian keamanan dan konten, deployment, panduan operator, pelatihan Media Center, backup+uji restore, inventaris akses, dan UAT bersama mitra. |

### 4.1 Batas minimal data ketika rilis

- **Profil dan struktur aktif:** hanya informasi dan foto yang sudah diberikan/diizinkan Sekretaris Jemaat; tidak membuat nama atau jabatan fiktif.
- **Periode Majelis:** periode aktif ditampilkan dahulu; periode historis boleh belum terisi. Namun admin **harus dapat memasukkan periode lama** tanpa perlu modifikasi kode.
- **Statistik:** hanya angka agregat tervalidasi yang sudah tersedia; kolom lain dapat diisi Media Center kemudian. Tidak menampilkan jumlah rekaan, data pribadi, atau data sensitif.
- **Berita dan jadwal:** hanya entri yang benar dan disahkan; bila belum ada, tampilkan keadaan kosong yang informatif.
- **Konten contoh:** boleh dipakai dalam pengembangan, harus berlabel dummy dan tidak boleh tampil sebagai data resmi saat publikasi.

### 4.2 P1 — pengembangan bersyarat setelah P0 stabil

- **Berita eksternal:** kurasi berita dari Sinode GPM atau media lain, hanya judul, ringkasan buatan sendiri, nama sumber, dan tautan ke artikel asli; izin gambar diperiksa. **Belum disetujui sebagai P0.**
- Pengayaan histori, misalnya import massal, bagan struktur tingkat lanjut, atau perbandingan detail antarperiode.
- Grafik statistik/riwayat data yang lebih kompleks serta filter statistik lanjutan.
- Audit aktivitas lengkap, riwayat revisi konten, dan penanganan konflik edit yang lebih canggih (pengamanan minimum tetap P0).
- Pembagian peran penulis/editor/admin yang berbeda jika organisasi Media Center nantinya memerlukannya.
- Modul master pelayan ibadah dan penjadwalan kompleks.

### 4.3 P2 — di luar proyek awal

Sistem data pribadi anggota/KK, pendataan sakramen pribadi, keuangan jemaat, surat-menyurat administratif, akun login publik, WhatsApp API/pesan otomatis, live streaming bawaan, sistem perizinan/approval online bertingkat, dan modul galeri mandiri. Foto kegiatan dapat ditempatkan dalam berita.

**Aturan perubahan:** P1/P2 tidak otomatis menjadi kewajiban proyek. Penambahan modul harus mempertimbangkan dampak pada target 9 November 2026.

## 5. Detail fungsional dan kriteria penerimaan

### FR-01 Beranda — `/`

- Pengunjung langsung memahami identitas Jemaat Leahari dan menemukan tombol/menu menuju Profil, Berita, dan Jadwal.
- Berita terbaru diambil dari entri yang telah terbit; jadwal terdekat dari jadwal yang valid.
- Bagian tanpa data memiliki *empty state* yang aman; tidak ada berita dummy dipresentasikan sebagai berita nyata.
- **Lulus:** halaman HP dapat dipakai, tautan benar, konten tidak ada tidak membuat error.

### FR-02 Profil — `/profil`

- Menampilkan sejarah, visi–misi, alamat, peta Google Maps, dan kontak resmi yang disetujui.
- Sektor/unit dan unsur profil lain tampil hanya jika data telah divalidasi.
- Admin memperbarui data resmi melalui formulir, dan perubahan tersimpan pada database.
- **Lulus:** profil publik sesuai data dari Sekretaris dan dapat diperbarui tanpa mengedit kode.

### FR-03 Struktur dan periode Majelis

- Pada halaman profil tersedia struktur Majelis periode aktif dengan nama, jabatan, dan foto berizin jika tersedia.
- Admin dapat mengelola data periode (misalnya nama periode, tanggal/tahun mulai–akhir sesuai data resmi, status aktif) dan anggota per periode.
- Hanya satu periode ditetapkan aktif pada satu waktu; jika periode sebelumnya belum ada, jangan membuat entri sejarah palsu.
- Pengunjung dapat memilih/melihat riwayat periode ketika sudah diisi Media Center.
- **Lulus:** operator membuat periode baru dengan anggota uji, menampilkannya pada area riwayat, dan mengubah periode aktif tanpa kehilangan data periode sebelumnya.

### FR-04 Statistik jemaat

- Statistik bersifat agregat; kategori angka mengikuti data resmi yang benar-benar diberikan Sekretaris.
- Simpan label angka, nilai, keterangan/periode data, dan waktu pembaruan untuk mengurangi kesalahpahaman.
- Jangan memublikasikan daftar individu, rincian keluarga, atau kategori sensitif tanpa keputusan terpisah.
- **Lulus:** operator menambah/mengubah statistik resmi; pengunjung hanya melihat data valid dengan konteks periode yang tepat.

### FR-05 dan FR-06 Berita internal, arsip, dan pencarian — `/berita` dan `/berita/{slug}`

- Berita internal memiliki judul, isi, tanggal, ringkasan, status publikasi, slug yang unik; foto utama opsional.
- Kartu berita menampilkan sumber internal secara jelas, judul, tanggal, ringkasan, dan tautan ke detail.
- Pencarian menemukan berita historis yang tetap publik. Filter sederhana dapat menggunakan kategori/periode sesuai data yang tersedia; tidak wajib membuat taksonomi rumit.
- Bedakan **berita lama yang masih terbit** (bisa ditemukan) dari **berita ditarik/arsip administratif** (tidak bisa diakses publik). Istilah “arsip” pada tampilan publik berarti kumpulan berita lama yang masih sah, bukan akses ke berita ditarik.
- **Lulus:** mencari judul berita lama menemukan artikel yang masih terbit; draft dan berita yang ditarik tidak muncul dalam hasil maupun saat URL langsung dibuka.

### FR-07 Jadwal — `/jadwal-ibadah`

- Data wajib: jenis/nama ibadah, tanggal, waktu, dan lokasi; pelayan/pemimpin ibadah opsional.
- Admin dapat membuat, memperbarui, membatalkan/menarik entri bila perlu; informasi perubahan jadwal tidak boleh disembunyikan secara menyesatkan.
- Tampilkan waktu sesuai lokasi Jemaat Leahari (WIT) dan format tanggal Indonesia.
- **Lulus:** jadwal tanpa nama pelayan tetap dapat disimpan dan terbaca jelas di HP.

### FR-08 s.d. FR-11 Admin, CMS, publikasi, media

- Pintu masuk admin adalah `/admin`; bila belum login diarahkan ke login, bila sudah login diarahkan ke dashboard. Implementasi lokal bisa menyertakan `/index.php/` sesuai konfigurasi.
- Admin melakukan login/logout aman, melihat daftar konten, menambah, melihat, memperbarui, menerbitkan, dan menarik konten sesuai modul.
- Konten baru disimpan sebagai draft → pratinjau → setelah konfirmasi pimpinan di luar website → terbit. Edit konten terbit dapat langsung memengaruhi publik; UI memberi peringatan sebelum menyimpan.
- Penghapusan konten penting memakai konfirmasi; utamakan arsip/tarik publikasi daripada penghapusan permanen untuk mencegah kehilangan data.
- Unggah media memeriksa tipe file asli, ukuran, nama aman, dan hak akses; tidak menerima file yang dapat dieksekusi.
- Jika operator lebih dari satu, setiap orang memiliki akun sendiri [R], tanpa perlu saling menunggu logout. Pengaturan admin utama diselesaikan sebelum penyerahan akses [O].
- **Lulus:** sesi anonim tidak dapat membuka halaman/admin API; CRUD normal dan skenario kesalahan berhasil diuji; konten draft tidak bocor; operator dapat menyelesaikan alur tanpa programmer.

### FR-12 Responsif dan aksesibilitas

- Palet awal ungu, krem dan gading; tipografi proyek saat ini Cormorant Garamond/DM Sans. Keduanya pilihan desain kelompok, bukan warna yang telah disahkan mitra.
- Logo GPM dan foto gereja yang diberikan Sekretaris dapat dipakai sesuai izin; sumber gambar lain wajib jelas.
- Referensi GPM Silo dan Sinode GPM hanya inspirasi, bukan bukti persetujuan atas tampilan.
- Uji lebar 320, 360, 390, 430, 768, dan 1440 piksel, navigasi keyboard, fokus, warna/kontras, serta kondisi error form.
- Hindari animasi berat; hormati `prefers-reduced-motion`; informasi tetap terlihat tanpa animasi/JavaScript tambahan.
- **Lulus:** navigasi dan form tidak terpotong, teks terbaca, ukuran tombol memadai, tanpa overflow horizontal yang tidak disengaja.

### FR-13 Serah terima

- Website dideploy di infrastruktur yang disediakan fakultas; pastikan pengaturan produksi dan akses HTTPS sesuai fasilitas sebenarnya.
- Pelatihan Media Center mencakup login, berita, jadwal, profil, struktur/riwayat Majelis, statistik, dan tindakan dasar saat terjadi kesalahan.
- Paket serah terima mencakup dokumentasi, daftar akun dan pihak penerima (tanpa menuliskan password di dokumen terbuka), backup database+media, langkah restore, serta kontak penanggung jawab.
- UAT melibatkan Media Center dan perwakilan Ketua/Wakil/Sekretaris Majelis sesuai pengaturan mereka.
- **Lulus:** operator dapat menyelesaikan tugas inti dan backup uji berhasil dipulihkan; catatan hasil demo/UAT serta serah terima tersedia.

## 6. Kebutuhan nonfungsional (NFR)

| ID | Area | Kriteria minimum |
|---|---|---|
| NFR-01 | Keamanan | Password di-hash, login dibatasi percobaan berulang, CSRF pada aksi perubahan, session aman, pemeriksaan otorisasi di server, validasi input dan upload, HTTPS pada hosting produksi. |
| NFR-02 | Privasi dan izin | Hanya informasi yang boleh dipublikasikan, statistik agregat; foto berizin; tidak memuat data pribadi atau dokumen sensitif tanpa alasan dan izin. |
| NFR-03 | Kinerja | Gambar dioptimalkan, tidak memaksa unduh gambar besar, tampilan utama ringan di HP; ukuran dan waktu muat diperiksa di hosting tujuan. |
| NFR-04 | Reliabilitas | Kesalahan input dijelaskan; operasi database tidak merusak konten lain; migrasi non-destruktif dan rollback/backup tersedia. |
| NFR-05 | Aksesibilitas | Sasaran praktik baik WCAG 2.2 AA untuk alur inti; lakukan pengujian dan jangan mengklaim sertifikasi tanpa audit. |
| NFR-06 | Backup | Backup database serta file upload, uji restore sebelum serah terima, petunjuk lokasi dan jadwal cadangan. |
| NFR-07 | Operasional | Dokumentasi setup/deploy dan panduan Media Center tersedia; kepemilikan akun/hosting tercatat dan password diserahkan melalui saluran aman. |
| NFR-08 | Kejujuran konten | Data dummy tidak tampil seperti informasi resmi; status draft/ditarik benar-benar terlindungi; berita eksternal, bila dikembangkan kelak, diberi atribusi. |
| NFR-09 | Pemeliharaan kode | Proyek memakai standar dan migrasi CodeIgniter 4; setiap task dicatat di `docs/dokumentasi-task/`; perubahan tidak merusak autentikasi yang sudah berjalan. |

## 7. Sitemap dan alur inti

**Publik:** `/` → `/profil` (termasuk Struktur/Periode dan Statistik yang tersedia), `/berita` → `/berita/{slug}`, `/jadwal-ibadah`. Navigasi dan bentuk UI rinci boleh berbeda selama semua kebutuhan P0 terpenuhi.

**Admin:** `/admin` → `/admin/login` atau `/admin/dashboard` → kelola Profil, Majelis/Periode, Statistik, Berita, Jadwal, Media, dan akun bila diperlukan. Logout menggunakan aksi aman yang dilindungi CSRF.

**Alur berita:** Media Center login → isi draft → pratinjau → konfirmasi pimpinan di luar aplikasi → terbit → publik dapat membaca/mencari → koreksi atau tarik bila perlu.

**Alur data resmi:** Sekretaris Jemaat memberikan/memverifikasi data → Media Center mengelola → pimpinan memberi konfirmasi jika diperlukan → konten tampil publik.

## 8. Model data konseptual — acuan ERD, bukan perintah langsung membuat tabel

| Entitas konseptual | Fungsi utama |
|---|---|
| Akun admin yang sudah ada | Login, identitas operator, keamanan dan hak akses. Jangan membuat tabel duplikat jika skema sudah ada. |
| Profil jemaat | Sejarah, visi–misi, alamat, peta, kontak, dan atribut profil resmi. |
| Periode Majelis | Nama/label periode, rentang waktu, status aktif, urutan tampilan. |
| Anggota Majelis | Hubungan ke periode, nama, jabatan, foto berizin, urutan tampil. |
| Statistik agregat | Label metrik, nilai, satuan/keterangan, tanggal atau periode data, status validasi. |
| Berita | Judul, slug, ringkasan, isi, gambar, tanggal, kategori jika ada, status, identitas pengelola. |
| Jadwal ibadah | Jenis, tanggal, waktu, lokasi, pelayan opsional, keterangan, status. |
| Media file | Nama aman, tipe, ukuran, lokasi, sumber/izin jika perlu. |
| Log aktivitas minimum | Penanda siapa dan kapan melakukan operasi penting; tanpa menyimpan password/token. |

**Aturan pengembangan:** sesuaikan nama tabel dan struktur dengan codebase yang ada. Susun migration aman/non-destruktif setelah ERD dan data dictionary singkat siap. Hindari penghapusan tabel, reset database, atau perubahan login tanpa alasan dan pengujian.

## 9. Matriks pengujian dan penerimaan (UAT)

| ID Uji | Skenario | Hasil yang wajib terlihat | FR |
|---|---|---|---|
| UAT-01 | Akses halaman publik di HP | Menu, profil, berita, jadwal bisa dibuka dan tidak terpotong. | 01,02,05,07,12 |
| UAT-02 | Profil resmi | Sejarah, visi–misi, alamat/peta/kontak sesuai data resmi. | 02 |
| UAT-03 | Struktur aktif dan periode lama | Struktur aktif tampil; tambah periode lama lewat admin dan lihat publik setelah diterbitkan. | 03,09 |
| UAT-04 | Statistik | Operator mengubah angka agregat dan periode; data yang tampil hanya yang valid. | 04,09 |
| UAT-05 | Terbitkan berita | Draft tersembunyi; sesudah terbit memiliki halaman detail dan bisa dibaca publik. | 05,09,10 |
| UAT-06 | Cari berita lama | Judul lama yang masih terbit ditemukan; berita ditarik/draft tidak muncul. | 05,06,10 |
| UAT-07 | CRUD jadwal | Tambah/edit jadwal dengan 4 field wajib; pelayan kosong tidak menimbulkan error. | 07,09 |
| UAT-08 | Keamanan admin | Pengunjung anonim ditolak; login/logout bekerja; akses lama tidak tetap terbuka setelah logout. | 08,10 |
| UAT-09 | Media upload | Gambar aman dapat diunggah; file salah ditolak; gambar kosong punya fallback. | 11 |
| UAT-10 | Layout dan form | Tampilan mobile/desktop, keyboard, tombol, dan pesan error tetap bisa dipakai. | 12 |
| UAT-11 | Pelatihan operator | Operator sendiri menyelesaikan alur berita, jadwal, struktur, dan statistik. | 09,13 |
| UAT-12 | Backup/restore & penyerahan | Data/media uji bisa dipulihkan, dokumentasi dan penerima akses ditetapkan. | 13 |

**Definition of Done:** fitur bekerja untuk jalur normal dan gagal; tidak membocorkan konten privat; diuji di HP; data sah; dampak pada login lama diuji; dan hasil kerja/pengujian dicatat. Rilis produksi mensyaratkan semua skenario UAT kritis **lulus** atau mempunyai pengecualian tertulis yang disetujui pihak penerima.

## 10. Status implementasi yang diketahui per 29 Oktober 2026

| Area | Status yang dilaporkan | Catatan |
|---|---|---|
| Beranda, Profil, Berita, Jadwal | Struktur halaman dan UI awal sudah dibuat [I] | Sebagian memakai konten dummy; belum boleh dianggap selesai produksi. |
| Login/logout admin dan proteksi akses | Telah diuji lokal dan dilaporkan berfungsi [I] | Jangan membangun ulang tanpa alasan; uji regresi setiap perubahan. |
| Akses `/admin` | Shortcut login/dashboard sudah dibuat dan diuji lokal [I] | Pertahankan perilakunya. |
| Dashboard admin | Kerangka dasar ada [I] | Modul CRUD konten masih harus dibuktikan selesai melalui UAT. |
| Modul profil, Majelis/periode, statistik, berita, jadwal dinamis | Penyelesaian penuh belum dibuktikan | Prioritaskan implementasi P0 dan pengujian. |
| Keamanan produksi, backup/restore, deployment, pelatihan | Belum dibuktikan tuntas | Wajib diperiksa sebelum 9 November 2026. |
| Responsivitas kondisi error login pada lebar 390 px | Pernah dilaporkan overflow [I] | Bug harus diverifikasi dan diperbaiki sebelum UAT responsif selesai. |

Status ini hanya merangkum laporan proyek; bukan audit kode terkini ataupun klaim aplikasi telah dideploy.

## 11. Pelaksanaan dan pembagian peran Kelompok 13

| Tim | Anggota | Luaran |
|---|---|---|
| **Proposal** | Gabriel R. Van Harling; Billy Donni Vianes Latumahina; Veronika V. Futwembun; Jeva Soplanit | Proposal gabungan yang selaras dengan PRD, jadwal, dan luaran KKN. |
| **Demo/Presentasi** | Adelina W. Romkeny; Ledy C. Ririhena; Rivanola K. U. Akihary; Winanda Jotlely; Alvaro A. Patty | Skenario demonstrasi, materi presentasi, pendampingan uji pengguna, dan pelatihan. |
| **Pengembangan & testing** | Gujestok J. Welay; Gamaliel D. G. Pariama | Implementasi, pengujian kode dan UAT, dokumentasi teknis, persiapan deploy dan backup. |

**Pembagian di atas berdasarkan pembagian tugas terbaru dari Kelompok 13.** Kolaborasi lintas tim tetap diperlukan; tidak berarti hanya dua orang yang boleh membantu pengujian. Koordinasi kelompok bertugas menjaga konsistensi PRD, proposal, dan implementasi.

### 11.1 Milestone

| Tahap | Target/ketentuan |
|---|---|
| Finalisasi PRD internal | 29 Oktober 2026; menjadi acuan pemeriksaan proposal dan coding. |
| Penggabungan proposal | Direncanakan pada pertemuan kelompok setelah PRD ini; tanggal final mengikuti kelompok. |
| Penyelesaian P0 dan pengujian | Dilakukan bertahap sebelum agenda serah terima; hindari menunda UAT sampai hari terakhir. |
| Demo, pelatihan, serah terima | **9 November 2026** (target yang dinyatakan kelompok). |
| Tahun pertama hosting | Dibiayai fakultas menurut keterangan kelompok; tanggal mulai/akhir layanan perlu dicatat dari penyedia. |

## 12. Keberlanjutan dan operasional

1. **Penyediaan infrastruktur:** domain dan hosting berasal dari fakultas menurut informasi kelompok. Fakultas menanggung hosting satu tahun; setelahnya Jemaat Leahari memutuskan perpanjangan. Tidak mengasumsikan otomatis bahwa biaya domain atau SSL ditanggung pada periode yang sama tanpa bukti rincian paket.
2. **Penanggung jawab konten:** Media Center yang ditugaskan Ketua Majelis. Jumlah anggota yang mengelola dan penerima akun utama disepakati sebelum serah terima.
3. **Pelatihan:** kegiatan terakhir sebelum selesai KKN; wajib ada praktik mandiri dan panduan sederhana.
4. **Dukungan pasca-KKN:** Kelompok 13 menyatakan bersedia membantu; **durasi, saluran komunikasi, waktu respons, dan batas pekerjaan** belum diputuskan. Jangan menuliskan komitmen tanpa batas waktu.
5. **Backup:** database dan gambar dicadangkan, disimpan aman, dan prosedur restore diuji setidaknya satu kali sebelum penyerahan.
6. **Kepemilikan akses:** akses hosting, admin, domain, dan repositori/arsip kode diserahkan sesuai otorisasi yang ditetapkan; hindari mencantumkan kredensial pada lampiran publik.

## 13. Register keputusan operasional terbuka — tanpa membuka ulang P0

| Kode | Keputusan yang masih perlu dicatat | Batas penyelesaian yang disarankan | Pemilik keputusan |
|---|---|---|---|
| O-01 | Jumlah admin Media Center, nama admin utama dan cadangan, dan penerima akun per orang. | Sebelum pembuatan akun akhir/pelatihan. | Majelis + Media Center |
| O-02 | Penggunaan akun individual dan hak CRUD setara, serta siapa berwenang mengelola akun. | Sebelum UAT multi-operator/serah terima. | Media Center + tim teknis |
| O-03 | Detail hosting: penyedia/panel, domain, SSL, kuota, tanggal mulai/berakhir tahun pertama, backup fasilitas. | Sebelum deployment dan penyerahan. | Fakultas + tim teknis |
| O-04 | Penerima inventaris akun, dokumentasi, backup, dan catatan serah terima. | Sebelum 9 November. | Majelis + Media Center |
| O-05 | Durasi dan mekanisme pendampingan teknis setelah akhir KKN. | Sebelum serah terima. | Kelompok 13 + mitra |
| O-06 | Tanggal/susunan sesi demonstrasi dan petugas UAT pada 9 November. | Sebelum agenda pelatihan. | Kelompok 13 + mitra |
| O-07 | Format pencatatan konfirmasi berita dan koreksi penting di luar website. | Sebelum konten produksi dikelola operator. | Pimpinan Majelis + Media Center |

**O-01 sampai O-07 bukan permintaan untuk menyetujui kembali Beranda, Profil, Berita, Jadwal, Statistik, atau Struktur Majelis.** Jika jawaban operasional mengubah fitur bisnis, dampaknya baru dicatat melalui perubahan versi.

## 14. Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Waktu menuju 9 November terbatas | Prioritaskan semua fungsi P0 dan tes penerimaan; jangan mulai fitur P1 sebelum P0 stabil. |
| Proposal tidak sesuai implementasi | Gunakan matriks kecocokan PRD–proposal; hilangkan janji fitur P1/P2 dari luaran wajib. |
| Data resmi belum lengkap | Pakai data tersedia/terverifikasi dan CMS untuk pengisian bertahap; jangan menciptakan data historis. |
| Beberapa operator memakai akun bersama | Terapkan akun individual, hak minimum, dan pencatatan operasi penting. |
| Banyak admin menyunting konten yang sama | Terapkan minimal peringatan pada penyimpanan; bila memungkinkan gunakan pemeriksaan waktu pembaruan untuk menghindari *lost update*. |
| Kesalahan/unggahan berbahaya | Uji tipe file, akses, sanitasi, dan restore data. |
| Hosting satu tahun berakhir | Serahkan data jatuh tempo dan prosedur backup/migrasi agar mitra dapat memutuskan perpanjangan. |
| Perubahan berita tanpa konfirmasi | Tegaskan prosedur persetujuan di luar website, terutama perubahan substansial. |
| Data dummy tampak resmi | Periksa seluruh halaman sebelum publikasi dan nonaktifkan indeksasi situs selama belum siap rilis. |

## 15. Matriks kesesuaian PRD dengan Proposal KKN

Matriks ini digunakan ketua/koordinator saat tim proposal menggabungkan draf.

| Bagian proposal | Isi yang harus konsisten dengan PRD |
|---|---|
| Latar belakang | Informasi jemaat tersebar melalui WhatsApp/Facebook; warga/diaspora memerlukan pusat informasi yang mudah dicari. |
| Rumusan masalah | Ketiadaan kanal informasi terpusat dan pengelolaan pembaruan yang berkelanjutan. |
| Tujuan | Website informasi dan publikasi digital Jemaat Leahari yang dapat dikelola Media Center. |
| Sasaran/penerima manfaat | Jemaat Leahari, warga di luar daerah, masyarakat umum, dan operator Media Center. |
| Program kerja/fitur | Sebut cakupan P0: Beranda, Profil, Struktur/Periode Majelis, Statistik, Berita/Pencarian, Jadwal, dan CMS/Admin. |
| Metode | Wawancara → analisis/PRD → desain → coding → testing/UAT → pelatihan → serah terima. |
| Teknologi | CodeIgniter 4/PHP, MySQL, Bootstrap 5 sesuai implementasi nyata; jangan menjanjikan integrasi yang belum ada. |
| Luaran | Website berfungsi, dokumentasi pengguna/teknis, pelatihan Media Center, backup, serah terima. |
| Jadwal | Target demo/pelatihan/serah terima **9 November 2026**. |
| Anggaran/keberlanjutan | Fakultas menyediakan domain dan hosting; hosting ditanggung satu tahun; keputusan perpanjangan oleh jemaat. Jangan mengarang nominal biaya. |
| Evaluasi keberhasilan | UAT sesuai Bab 9 dan kemampuan Media Center menjalankan alur admin tanpa programmer. |

**Aturan pencocokan:** bila proposal memuat fitur P1/P2 sebagai janji wajib, tandai sebagai *tidak selaras*. Bila proposal menghilangkan modul P0 yang disepakati, lengkapi ringkasan programnya. PRD lebih rinci daripada proposal: proposal tidak harus menyalin seluruh aturan teknis, tetapi tidak boleh bertentangan dengannya.

## 16. Kendali versi dan penyelesaian dokumen

### 16.1 Status final internal

**PRD v4.1 ini dinyatakan selesai sebagai dokumen baseline kerja internal berdasarkan instruksi koordinator dalam percakapan proyek.** Status tersebut berarti ruang lingkup P0 menjadi acuan pengerjaan dan penilaian proposal; **bukan** berarti website telah selesai dibuat, semua UAT telah lulus, atau dokumen telah ditandatangani mitra/fakultas.

### 16.2 Riwayat versi

| Versi | Tanggal | Perubahan |
|---|---|---|
| v3.0 | 24 Oktober 2026 | Draft kebutuhan awal untuk validasi. |
| v4.0 | 28 Oktober 2026 | Usulan baseline internal dan spesifikasi teknis/pengujian. |
| **v4.1** | **29 Oktober 2026** | Menyatukan validasi A–H, menetapkan P0 menurut kesepakatan yang dilaporkan, menaikkan pencarian berita, statistik, struktur/riwayat dan CMS terkait ke P0; memindahkan berita eksternal ke P1; menetapkan target 9 November dan matriks proposal. |

### 16.3 Tanda penerimaan saat serah terima (diisi pada waktunya)

| Pihak | Nama/Jabatan | Keputusan / tanggal |
|---|---|---|
| Perwakilan Ketua/Wakil/Sekretaris Majelis | Diisi saat acara | Belum diisi |
| Perwakilan Media Center | Diisi saat acara | Belum diisi |
| Perwakilan KKN Tematik Kelompok 13 | Diisi saat acara | Belum diisi |
| DPL (sesuai prosedur fakultas) | Diisi jika diperlukan | Belum diisi |

**Ketentuan akhir:** setelah P0 ditetapkan, perubahan tidak dilakukan hanya karena muncul ide desain atau fitur baru. Catat permintaan perubahan secara singkat (pemohon, alasan, dampak, keputusan) lalu perbarui PRD, proposal, dan test case jika memang disetujui.

---

**Dokumen ini adalah acuan bersama untuk Tim Proposal, Tim Pengembangan & Testing, dan Tim Demo/Presentasi Kelompok 13.**
