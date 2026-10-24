<?php

namespace App\Libraries;

/** Static demonstration content. No item represents an official announcement. */
final class PublicSampleContent
{
    public static function news(): array
    {
        return [
            ['id' => 'persekutuan', 'title' => 'Ruang untuk bersekutu', 'category' => 'Kegiatan', 'date' => '24 Oktober 2026', 'summary' => 'Contoh berita tentang pertemuan warga jemaat dan kebersamaan lintas generasi.', 'body' => 'Naskah contoh ini memperlihatkan ruang untuk laporan kegiatan: tujuan pertemuan, rangkaian acara, dan pesan bagi warga jemaat. Isi, tanggal, serta dokumentasi perlu diganti dengan informasi yang telah disetujui sebelum dipublikasikan.'],
            ['id' => 'pelayanan', 'title' => 'Bersama dalam pelayanan', 'category' => 'Pelayanan', 'date' => '22 Oktober 2026', 'summary' => 'Contoh berita tentang persiapan kegiatan pelayanan bersama.', 'body' => 'Pada berita resmi, bagian ini dapat memuat penjelasan kegiatan, pihak yang terlibat dengan izin publikasi, serta tindak lanjut. Tidak ada kegiatan nyata yang dinyatakan berlangsung melalui contoh ini.'],
            ['id' => 'informasi', 'title' => 'Membaca warta jemaat', 'category' => 'Informasi', 'date' => '20 Oktober 2026', 'summary' => 'Contoh pengumuman untuk membantu warga menemukan informasi jemaat.', 'body' => 'Gunakan ruang ini untuk pengumuman yang sudah diverifikasi. Cantumkan tanggal yang tepat, informasi lengkap, dan kontak resmi jika telah tersedia serta diizinkan untuk publik.'],
        ];
    }

    public static function schedules(): array
    {
        return [
            ['type' => 'minggu', 'name' => 'Ibadah Minggu', 'day' => 'Minggu, 25 Oktober 2026', 'date' => '2026-10-25', 'time' => '09.00', 'location' => 'Lokasi contoh A', 'note' => 'Contoh jadwal ibadah umum.'],
            ['type' => 'keluarga', 'name' => 'Ibadah Keluarga', 'day' => 'Rabu, 28 Oktober 2026', 'date' => '2026-10-28', 'time' => '19.00', 'location' => 'Lokasi contoh B', 'note' => 'Contoh jadwal persekutuan keluarga.'],
            ['type' => 'minggu', 'name' => 'Ibadah Minggu', 'day' => 'Minggu, 1 November 2026', 'date' => '2026-11-01', 'time' => '09.00', 'location' => 'Lokasi contoh A', 'note' => 'Waktu dan tempat hanya untuk demonstrasi.'],
        ];
    }
}
