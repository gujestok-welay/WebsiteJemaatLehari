<?php

namespace App\Libraries;

use InvalidArgumentException;
use JsonException;

class PublicProfileContent
{
    public static function defaults(): array
    {
        return [
            'hero' => [
                'page_title' => 'Profil',
                'meta_description' => 'Pratinjau website informasi Jemaat Leahari. Sebagian informasi masih menunggu konfirmasi resmi.',
                'preview_notice_lead' => 'Pratinjau website informasi Jemaat Leahari.',
                'preview_notice_body' => 'Sebagian informasi masih menunggu konfirmasi resmi.',
                'badge' => 'Profil Jemaat',
                'title' => 'Jemaat GPM Leahari',
                'description' => 'Ibukota Kecamatan Leitimur Selatan, di pegunungan Kakusang, menghadap langsung ke Laut Banda, sebuah jemaat pesisir di sisi timur Pulau Ambon.',
                'stats_aria_label' => 'Ringkasan profil Jemaat',
                'map_link_label' => 'Google Maps',
                'links' => [
                    ['label' => 'Wilayah', 'target' => 'wilayah'],
                    ['label' => 'Data Jemaat', 'target' => 'jemaat'],
                    ['label' => 'Peribadahan', 'target' => 'ibadah'],
                ],
            ],
            'stats' => [
                ['icon' => 'bi-people', 'value' => '624', 'label' => 'Jiwa'],
                ['icon' => 'bi-house-heart', 'value' => '199', 'label' => 'Kepala Keluarga'],
                ['icon' => 'bi-diagram-3', 'value' => '3 Sektor · 9 Unit', 'label' => 'Wilayah Pelayanan'],
                ['icon' => 'bi-map', 'value' => '53.12 Ha', 'label' => 'Luas Wilayah'],
            ],
            'territory' => [
                'section_badge' => '2.1',
                'title' => 'Gambaran Umum Wilayah Pelayanan',
                'administration_title' => 'Status Administrasi',
                'administration_text' => 'Secara administratif pemerintahan, Jemaat GPM Leahari berada di pusat wilayah Kecamatan Leitimur Selatan dan menjadi ibukota kecamatan tersebut, masuk dalam wilayah Negeri Leahari, Kota Ambon.',
                'topography_title' => 'Topografi',
                'topography_text' => 'Jemaat berada di wilayah pegunungan Kakusang, di sebelah timur Pulau Ambon, pada posisi lintang 128°16′0″ dan bujur 3°42′48″, dengan kondisi wilayah yang relatif datar. Leahari merupakan salah satu jemaat di pinggiran Kota Ambon.',
                'community_text' => 'Selain warga GPM, terdapat pula warga gereja denominasi lain. Meski demikian, komunikasi dan relasi antaranggota tetap sangat baik, terlihat dari keterlibatan mereka dalam kegiatan jemaat.',
                'boundaries_title' => 'Batas Wilayah',
                'boundary_prefix' => 'Sebelah',
                'boundaries' => [
                    ['direction' => 'Utara', 'icon' => 'bi-arrow-up-circle', 'name' => 'Jemaat GPM Soya'],
                    ['direction' => 'Timur', 'icon' => 'bi-arrow-right-circle', 'name' => 'Jemaat GPM Rutong'],
                    ['direction' => 'Selatan', 'icon' => 'bi-arrow-down-circle', 'name' => 'Laut Banda'],
                    ['direction' => 'Barat', 'icon' => 'bi-arrow-left-circle', 'name' => 'Jemaat GPM Hukurila'],
                ],
                'sectors' => [
                    ['name' => 'Koinonia', 'units' => 3, 'area' => 34.15, 'area_unit' => 'Ha'],
                    ['name' => 'Diakonia', 'units' => 3, 'area' => 12.19, 'area_unit' => 'Ha'],
                    ['name' => 'Marturia', 'units' => 3, 'area' => 6.78, 'area_unit' => 'Ha'],
                ],
                'sector_prefix' => 'Sektor',
                'unit_suffix' => 'unit',
                'area_share_suffix' => '% dari total wilayah',
                'orbit_title' => 'Orbitasi',
                'orbitations' => [
                    ['label' => 'Pusat Kecamatan', 'value' => '200 M'],
                    ['label' => 'Pusat Kota', 'value' => '24 Km'],
                    ['label' => 'Ibu Kota Provinsi', 'value' => '24 Km'],
                    ['label' => 'RSUD Haulussy', 'value' => '29 Km'],
                    ['label' => 'Pasar Passo', 'value' => '13 Km'],
                    ['label' => 'Pantai Wisata Natsepa', 'value' => '19 Km'],
                    ['label' => 'Pusat Perbelanjaan Mall', 'value' => '13 Km'],
                    ['label' => 'Angkutan Umum (roda 4)', 'value' => '4 buah'],
                ],
            ],
            'seasons' => [
                'items' => [
                    [
                        'period' => 'April – Agustus',
                        'title' => 'Musim Timur',
                        'description' => 'Angin Timur Tenggara bertiup kuat disertai curah hujan tinggi dan gelombang laut yang besar karena Leahari berhadapan langsung dengan Laut Banda. Pada musim ini jemaat rentan terhadap bencana alam dan pola hidup warga ikut terpengaruh.',
                    ],
                    [
                        'period' => 'Oktober – Februari',
                        'title' => 'Musim Barat',
                        'description' => 'Angin Barat Laut bertiup, kadang hujan lebat dengan angin kencang, namun tidak sebesar Musim Timur. Warga dapat bekerja lebih leluasa di laut, yang berdampak pada pendapatan keluarga.',
                    ],
                ],
            ],
            'census' => [
                'section_badge' => '2.2.1',
                'title' => 'Jumlah Kepala Keluarga dan Jiwa',
                'description' => 'Rekapitulasi anggota jemaat berdasarkan kategori usia:',
                'household_count' => 199,
                'household_suffix' => ' KK dan ',
                'population_suffix' => ' jiwa. ',
                'legend_male' => 'Laki-laki',
                'legend_female' => 'Perempuan',
                'count_separator' => ': ',
                'age_unit' => 'th',
                'aria_age_prefix' => 'Jemaat usia ',
                'aria_age_suffix' => ' tahun',
                'aria_male_separator' => ': ',
                'aria_male_suffix' => ' laki-laki, ',
                'aria_female_suffix' => ' perempuan, total ',
                'aria_total_suffix' => ' orang',
                'table_age_heading' => 'Usia (th)',
                'table_male_heading' => 'Lk',
                'table_female_heading' => 'Pr',
                'table_total_heading' => 'Jumlah',
                'table_total_label' => 'Total jiwa',
                'age_rows' => [
                    ['label' => '0-3', 'male' => 12, 'female' => 7],
                    ['label' => '4-6', 'male' => 11, 'female' => 6],
                    ['label' => '7-9', 'male' => 8, 'female' => 4],
                    ['label' => '10-12', 'male' => 13, 'female' => 17],
                    ['label' => '13-15', 'male' => 12, 'female' => 19],
                    ['label' => '16-45', 'male' => 171, 'female' => 143],
                    ['label' => '46-59', 'male' => 61, 'female' => 64],
                    ['label' => '60-85', 'male' => 35, 'female' => 39],
                    ['label' => '> 86', 'male' => 2, 'female' => 0],
                ],
            ],
            'leadership' => [
                'title' => 'Susunan Majelis dan kontak',
                'members_title' => 'Majelis Jemaat',
                'members_empty' => 'Susunan Majelis aktif belum tersedia untuk publikasi.',
                'members_permission_note' => 'Nama, jabatan, dan periode pelayanan akan ditampilkan setelah data serta izin publikasinya dikonfirmasi.',
                'show_members' => false,
                'period_prefix' => 'Periode',
                'location_title' => 'Lokasi dan kontak',
                'address_title' => 'Alamat lengkap',
                'address_fallback' => 'Menunggu konfirmasi resmi.',
                'contact_title' => 'Kontak dan media sosial',
                'contact_fallback' => 'Belum tersedia. Tidak ada nomor pribadi yang ditampilkan.',
            ],
            'worship' => [
                'section_badge' => '2.2.2',
                'title' => 'Pelayanan Peribadahan',
                'description' => 'Beragam jenis ibadah sepanjang pekan menumbuhkan iman dan memperkuat persekutuan. Beberapa di antaranya merupakan kekhasan Jemaat Leahari.',
                'items' => [
                    ['day' => 'Minggu', 'name' => 'Ibadah Minggu', 'place' => 'Gedung gereja', 'note' => 'Dihadiri seluruh jemaat dari berbagai usia'],
                    ['day' => 'Minggu', 'name' => 'Sekolah Minggu', 'place' => 'Gedung gereja', 'note' => 'Dihadiri pengasuh dan anak SMTPI'],
                    ['day' => 'Minggu', 'name' => 'Ibadah Pengasuh', 'place' => 'Rumah pengasuh', 'note' => 'Sebulan 1x, sesuai jadwal'],
                    ['day' => 'Senin', 'name' => 'Tunas Pekabaran Injil', 'place' => 'Rumah jemaat', 'note' => 'Tiap jenjang SMTPI, bergiliran'],
                    ['day' => 'Senin', 'name' => 'Batu Tungku', 'place' => 'Gereja, kantor negeri, sekolah', 'note' => 'Sesuai jadwal'],
                    ['day' => 'Senin', 'name' => 'Warga Gereja Senior', 'place' => 'Rumah WGS', 'note' => 'Sesuai jadwal'],
                    ['day' => 'Selasa', 'name' => 'Wadah Pelayanan Laki-laki', 'place' => 'Rumah anggota wadah', 'note' => 'Dijadwalkan di tiap sektor'],
                    ['day' => 'Selasa', 'name' => 'HIPPMAL', 'place' => 'Rumah anggota HIPPMAL', 'note' => 'Himpunan Pemuda Pelajar asal Leahari'],
                    ['day' => 'Selasa / Rabu', 'name' => 'Kemitraan', 'place' => 'Rumah jemaat', 'note' => 'Dijadwalkan 2 bulan 1x'],
                    ['day' => 'Rabu', 'name' => 'Wadah Pelayanan Perempuan', 'place' => 'Rumah anggota wadah', 'note' => 'Dijadwalkan di tiap sektor'],
                    ['day' => 'Kamis', 'name' => 'AMGPM', 'place' => 'Rumah anggota AMGPM', 'note' => 'Sesuai jadwal'],
                    ['day' => 'Kamis', 'name' => 'Keluarga MJ dan Tuagama', 'place' => 'Rumah MJ dan tuagama', 'note' => 'Setiap bulan, minggu I'],
                    ['day' => 'Jumat', 'name' => 'Ibadah Unit', 'place' => 'Rumah jemaat', 'note' => 'Dilaksanakan di tiap sektor'],
                ],
            ],
        ];
    }

    public static function fromJson(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return self::defaults();
        }

        $values = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($values)) {
            throw new InvalidArgumentException('Konten profil yang tersimpan memiliki format tidak valid.');
        }

        return self::mergeForDisplay(self::defaults(), $values);
    }

    public static function fromProfile(array $profile): array
    {
        $defaults = self::defaults();
        $content = self::fromJson($profile['page_content'] ?? null);

        $territory = self::decodeModule($profile['territory_content'] ?? null, 'wilayah');
        if ($territory !== null) {
            $content['territory'] = self::mergeForDisplay($defaults['territory'], $territory['territory'] ?? []);
            $content['seasons'] = self::mergeForDisplay($defaults['seasons'], $territory['seasons'] ?? []);
        }

        $census = self::decodeModule($profile['census_content'] ?? null, 'data jemaat');
        if ($census !== null) {
            $content['census'] = self::mergeForDisplay($defaults['census'], $census);
        }

        return $content;
    }

    private static function decodeModule(?string $json, string $label): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        try {
            $values = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Data modul ' . $label . ' tersimpan dengan format tidak valid.', 0, $exception);
        }

        if (! is_array($values)) {
            throw new InvalidArgumentException('Data modul ' . $label . ' tersimpan dengan format tidak valid.');
        }

        return $values;
    }

    public static function sectionLabels(): array
    {
        return [
            'hero' => 'Judul dan pengantar',
            'stats' => 'Ringkasan angka',
            'territory' => 'Wilayah pelayanan',
            'seasons' => 'Musim',
            'census' => 'Data jemaat',
            'leadership' => 'Majelis dan kontak',
            'worship' => 'Pelayanan peribadahan',
        ];
    }

    public static function fieldLabel(string $key): string
    {
        $labels = [
            'page_title' => 'Judul tab browser',
            'meta_description' => 'Deskripsi mesin pencari',
            'preview_notice_lead' => 'Judul pemberitahuan pratinjau',
            'preview_notice_body' => 'Isi pemberitahuan pratinjau',
            'stats_aria_label' => 'Label aksesibilitas ringkasan angka',
            'map_link_label' => 'Teks tautan Google Maps',
            'badge' => 'Label kecil',
            'title' => 'Judul',
            'description' => 'Paragraf pengantar',
            'links' => 'Tautan bagian',
            'label' => 'Teks tautan',
            'target' => 'Bagian tujuan tautan',
            'stats' => 'Ringkasan angka',
            'icon' => 'Ikon Bootstrap',
            'value' => 'Nilai',
            'section_badge' => 'Label bagian',
            'administration_title' => 'Judul status administrasi',
            'administration_text' => 'Uraian status administrasi',
            'topography_title' => 'Judul topografi',
            'topography_text' => 'Uraian topografi',
            'community_text' => 'Uraian kehidupan masyarakat',
            'boundaries_title' => 'Judul batas wilayah',
            'boundary_prefix' => 'Awalan arah batas',
            'boundaries' => 'Batas wilayah',
            'direction' => 'Arah',
            'name' => 'Nama',
            'sectors' => 'Sektor pelayanan',
            'units' => 'Jumlah unit',
            'area' => 'Luas',
            'area_unit' => 'Satuan luas',
            'sector_prefix' => 'Awalan nama sektor',
            'unit_suffix' => 'Satuan unit',
            'area_share_suffix' => 'Keterangan persentase luas',
            'orbit_title' => 'Judul orbitasi',
            'orbitations' => 'Jarak orbitasi',
            'period' => 'Rentang waktu',
            'items' => 'Daftar item',
            'household_count' => 'Jumlah kepala keluarga',
            'household_suffix' => 'Teks setelah jumlah kepala keluarga',
            'population_suffix' => 'Teks setelah jumlah jiwa',
            'legend_male' => 'Legenda laki-laki',
            'legend_female' => 'Legenda perempuan',
            'count_separator' => 'Pemisah jumlah pada grafik',
            'age_unit' => 'Satuan usia',
            'aria_age_prefix' => 'Aksesibilitas: awalan usia',
            'aria_age_suffix' => 'Aksesibilitas: akhiran usia',
            'aria_male_separator' => 'Aksesibilitas: pemisah',
            'aria_male_suffix' => 'Aksesibilitas: akhiran laki-laki',
            'aria_female_suffix' => 'Aksesibilitas: akhiran perempuan',
            'aria_total_suffix' => 'Aksesibilitas: akhiran jumlah',
            'table_age_heading' => 'Judul kolom usia',
            'table_male_heading' => 'Judul kolom laki-laki',
            'table_female_heading' => 'Judul kolom perempuan',
            'table_total_heading' => 'Judul kolom jumlah',
            'table_total_label' => 'Label total jiwa',
            'age_rows' => 'Rentang usia',
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            'show_members' => 'Tampilkan anggota Majelis di publik',
            'members_title' => 'Judul Majelis',
            'members_empty' => 'Teks saat daftar anggota kosong',
            'members_permission_note' => 'Keterangan izin publikasi anggota',
            'period_prefix' => 'Awalan periode',
            'location_title' => 'Judul lokasi dan kontak',
            'address_title' => 'Judul alamat',
            'address_fallback' => 'Teks alamat saat belum tersedia',
            'contact_title' => 'Judul kontak',
            'contact_fallback' => 'Teks kontak saat belum tersedia',
            'day' => 'Hari',
            'place' => 'Tempat',
            'note' => 'Keterangan',
        ];

        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public static function mergeForDisplay(array $defaults, array $values): array
    {
        if (array_is_list($defaults)) {
            if (! array_is_list($values)) {
                return $defaults;
            }

            return array_map(
                static fn (mixed $item): mixed => is_array($item) && isset($defaults[0]) && is_array($defaults[0])
                    ? self::mergeForDisplay($defaults[0], $item)
                    : $item,
                $values,
            );
        }

        foreach ($defaults as $key => $default) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            if (is_array($default) && is_array($values[$key])) {
                $defaults[$key] = self::mergeForDisplay($default, $values[$key]);
            } elseif (is_scalar($default) && (is_scalar($values[$key]) || $values[$key] === null)) {
                $defaults[$key] = $values[$key];
            }
        }

        return $defaults;
    }

    public static function normalize(array $input): array
    {
        return self::normalizeNode(self::defaults(), $input, 'content');
    }

    private static function normalizeNode(array $template, array $input, string $path): array
    {
        if (array_is_list($template)) {
            if ($input === []) {
                return [];
            }

            if (count($input) > 100) {
                throw new InvalidArgumentException('Daftar pada ' . $path . ' maksimal 100 baris.');
            }

            $rowTemplate = $template[0] ?? null;
            if (! is_array($rowTemplate)) {
                throw new InvalidArgumentException('Struktur daftar pada ' . $path . ' tidak valid.');
            }

            $rows = [];
            foreach ($input as $index => $row) {
                if (! is_array($row)) {
                    throw new InvalidArgumentException('Baris ' . ($index + 1) . ' pada ' . $path . ' tidak valid.');
                }

                $rows[] = self::normalizeNode($rowTemplate, $row, $path . '[' . ($index + 1) . ']');
            }

            return $rows;
        }

        $normalized = [];
        foreach ($template as $key => $default) {
            $fieldPath = $path . '.' . $key;
            if (! array_key_exists($key, $input)) {
                if (is_array($default)) {
                    $normalized[$key] = self::normalizeNode($default, [], $fieldPath);
                    continue;
                }

                if (is_bool($default)) {
                    $normalized[$key] = false;
                    continue;
                }

                throw new InvalidArgumentException('Kolom ' . self::fieldLabel((string) $key) . ' belum terkirim.');
            }

            $value = $input[$key];
            if (is_array($default)) {
                if (! is_array($value)) {
                    throw new InvalidArgumentException('Isi ' . self::fieldLabel((string) $key) . ' tidak valid.');
                }

                $normalized[$key] = self::normalizeNode($default, $value, $fieldPath);
                continue;
            }

            if (is_bool($default)) {
                if (! is_bool($value) && ! in_array((string) $value, ['0', '1'], true)) {
                    throw new InvalidArgumentException('Pilihan ' . self::fieldLabel((string) $key) . ' tidak valid.');
                }

                $normalized[$key] = $value === true || (string) $value === '1';
                continue;
            }

            if (is_int($default) || is_float($default)) {
                if (! is_scalar($value) || ! is_numeric((string) $value) || (float) $value < 0) {
                    throw new InvalidArgumentException(self::fieldLabel((string) $key) . ' harus berupa angka nol atau lebih.');
                }

                $number = (float) $value;
                if (is_int($default) && (floor($number) !== $number || $number > PHP_INT_MAX)) {
                    throw new InvalidArgumentException(self::fieldLabel((string) $key) . ' harus berupa bilangan bulat.');
                }

                $normalized[$key] = is_int($default) ? (int) $number : $number;
                continue;
            }

            if (! is_scalar($value)) {
                throw new InvalidArgumentException('Isi ' . self::fieldLabel((string) $key) . ' tidak valid.');
            }

            $text = trim((string) $value);
            if ($key === 'target' && ! in_array($text, ['wilayah', 'jemaat', 'ibadah'], true)) {
                throw new InvalidArgumentException('Tujuan tautan harus mengarah ke bagian halaman yang tersedia.');
            }

            if (mb_strlen($text) > 3000) {
                throw new InvalidArgumentException(self::fieldLabel((string) $key) . ' maksimal 3.000 karakter.');
            }

            $normalized[$key] = $text;
        }

        return $normalized;
    }
}
