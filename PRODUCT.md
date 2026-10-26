# Website Informasi dan Publikasi Digital Jemaat Leahari

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- Warga Jemaat Leahari di dalam maupun di luar wilayah yang ingin memperoleh informasi jemaat dan jadwal ibadah.
- Masyarakat umum yang ingin mengenal jemaat dan mengakses informasi publik tanpa login.
- Media Center Jemaat Leahari sebagai calon pengelola konten. Penetapan personel, kewenangan, dan tata kelola masih memerlukan konfirmasi mitra.

## Product Purpose

Menyediakan pusat informasi publik untuk profil jemaat, berita dan dokumentasi kegiatan, serta jadwal ibadah. Website menjadi kanal tambahan yang mendukung distribusi informasi melalui WhatsApp dan Facebook.

Keberhasilan yang dituju adalah informasi yang mudah ditemukan, akurat, konsisten diperbarui, dan dapat dikelola secara mandiri setelah serah terima KKN. Ini merupakan tujuan produk, bukan klaim hasil yang sudah tercapai.

## Operating Context

Proyek dikembangkan dalam konteks KKN Tematik Kelompok 13, Fakultas Ilmu Komputer, Universitas Kristen Indonesia Maluku (UKIM). Media Center merupakan calon pengelola setelah pendampingan; koordinasi dengan Majelis/sekretariat dan tanggung jawab operasional belum disahkan.

Menurut temuan wawancara yang dilaporkan dalam PRD, jemaat menggunakan WhatsApp dan Facebook, pembaruan Facebook belum konsisten, dan sebagian warga di luar wilayah kesulitan mendapatkan informasi terkini. Temuan tersebut belum boleh dipresentasikan sebagai riset yang telah diverifikasi secara independen.

## Capabilities and Constraints

- Implementasi saat ini merupakan prototipe empat halaman publik: `/`, `/profil`, `/berita`, dan `/jadwal-ibadah`.
- Stack implementasi saat ini: CodeIgniter 4.7.5, PHP Views, Bootstrap 5, CSS, JavaScript, dan MySQL melalui XAMPP. Koneksi ke database `jemaat_leahari` telah berhasil. Database belum memiliki tabel fitur.
- Berita dan jadwal bersumber dari data contoh di `app/Libraries/PublicSampleContent.php`. Berita dapat dibuka melalui elemen `details`; jadwal memiliki filter jenis ibadah dan tetap tersedia tanpa JavaScript.
- Keempat halaman publik masih memakai data contoh. Belum ada dashboard atau autentikasi admin; keduanya tetap rencana implementasi. Koneksi database sudah tersedia, tetapi tabel fitur dan persistence konten belum dibuat.
- Keputusan implementasi terbaru: admin Media Center dapat menerbitkan konten secara langsung. Berita internal dan eksternal akan didukung. Rincian Rich Text Editor ditunda.
- Stack di atas menjelaskan kondisi implementasi terkini. Status draft PRD, persetujuan mitra, rincian fitur admin lainnya, prioritas rilis, domain/hosting, dan tata kelola tetap mengikuti proses validasi; jangan menganggapnya sebagai keputusan final.
- Bahasa antarmuka adalah Bahasa Indonesia. Waktu ibadah menggunakan WIT.
- Pengunjung mengakses informasi publik tanpa login. Kemudahan penggunaan lintas usia menjadi batasan produk.
- Konten prototipe harus tetap dikenali sebagai contoh, bukan informasi resmi. Pemberitahuan dummy dan `noindex, nofollow` tidak boleh dihapus sebelum konten dan kesiapan publikasi diperiksa.

## Brand Commitments

Nama yang digunakan dalam produk adalah **Jemaat Leahari**, mengikuti dokumentasi dan antarmuka, meskipun nama folder repositori berbeda.

Gunakan Bahasa Indonesia yang jelas dan mudah dipahami. Jangan mengarang sejarah, statistik, identitas pengurus, kontak, jadwal resmi, atau klaim persetujuan mitra.

## Evidence on Hand

- `docs/PRD.md`: PRD versi 3.0, berstatus **draft untuk validasi**. Memisahkan temuan yang dilaporkan (T), usulan desain (D), dan kebutuhan konfirmasi (K); bukan persetujuan final mitra.
- `docs/UI-PUBLIK.md`: catatan lingkup prototipe, struktur implementasi, cara menjalankan, hasil pemeriksaan yang pernah dilaporkan, serta prasyarat publikasi. Hasil pemeriksaan tersebut merupakan catatan historis, bukan verifikasi ulang pada setiap perubahan.
- `app/Views/publik/` dan `app/Libraries/PublicSampleContent.php`: implementasi serta konten contoh, bukan bukti berita atau jadwal aktual.
- Dokumentasi implementasi menyatakan logo, foto berizin, sejarah, susunan Majelis, alamat, dan kontak resmi belum tersedia. Lengkapi dari materi yang disahkan mitra; jangan menggantinya dengan fakta atau bukti sosial rekaan.

## Product Principles

1. Dahulukan kemudahan menemukan berita, profil, dan jadwal bagi pengunjung tanpa akun.
2. Pisahkan informasi resmi, contoh prototipe, temuan yang dilaporkan, dan usulan yang belum disahkan.
3. Dukung pengelolaan mandiri oleh Media Center melalui fitur dan pelatihan yang realistis, setelah lingkupnya disepakati.
4. Pertahankan peran WhatsApp/Facebook sebagai kanal distribusi yang melengkapi website.
5. Jaga lingkup sesuai kebutuhan mitra dan keberlanjutan setelah KKN, tanpa menganggap seluruh PRD sudah disetujui.

## Accessibility & Inclusion

Antarmuka harus mudah dibaca dan digunakan lintas usia, termasuk melalui HP. Pertahankan navigasi sederhana, fokus keyboard yang terlihat, skip link, serta akses konten inti tanpa JavaScript. Implementasi saat ini menyediakan dasar tersebut; kepatuhan terhadap standar aksesibilitas tertentu dan pengujian pengguna/perangkat nyata belum dinyatakan selesai.

Tidak ada batasan produk tambahan yang ditetapkan pengguna pada inisialisasi ini. Persona lansia secara khusus dan dominasi penggunaan HP tetap hipotesis PRD yang perlu divalidasi.
