<?php

namespace App\Controllers;

use App\Libraries\PublicSampleContent;
use App\Models\BeritaModel;
use App\Models\JadwalIbadahModel;

class Home extends BaseController
{
    public function index(): string
    {
        $schedules = $this->publicSchedules();
        $news = $this->publicNews();

        return view('publik/beranda', [
            'title' => 'Beranda',
            'activePage' => 'beranda',
            'isSample' => $this->usesSampleNews(),
            'news' => $this->normalizeNews(array_slice($news, 0, 2)),
            'schedules' => array_slice($schedules, 0, 4),
            'nextService' => $schedules[0] ?? null,
            'majelis' => [],
            'majelisPeriode' => null,
        ]);
    }

    public function profil(): string
    {
        $data = [
            'title' => 'Profil',
            'activePage' => 'profil',
            'batas' => [
                ['arah' => 'Utara', 'ikon' => 'bi-arrow-up-circle', 'nama' => 'Jemaat GPM Soya'],
                ['arah' => 'Timur', 'ikon' => 'bi-arrow-right-circle', 'nama' => 'Jemaat GPM Rutong'],
                ['arah' => 'Selatan', 'ikon' => 'bi-arrow-down-circle', 'nama' => 'Laut Banda'],
                ['arah' => 'Barat', 'ikon' => 'bi-arrow-left-circle', 'nama' => 'Jemaat GPM Hukurila'],
            ],
            'sektor' => [
                ['nama' => 'Koinonia', 'unit' => 3, 'luas' => 34.15],
                ['nama' => 'Diakonia', 'unit' => 3, 'luas' => 12.19],
                ['nama' => 'Marturia', 'unit' => 3, 'luas' => 6.78],
            ],
            'luas_total' => 53.12,
            'orbitasi' => [
                ['label' => 'Pusat Kecamatan', 'jarak' => '200 M'],
                ['label' => 'Pusat Kota', 'jarak' => '24 Km'],
                ['label' => 'Ibu Kota Provinsi', 'jarak' => '24 Km'],
                ['label' => 'RSUD Haulussy', 'jarak' => '29 Km'],
                ['label' => 'Pasar Passo', 'jarak' => '13 Km'],
                ['label' => 'Pantai Wisata Natsepa', 'jarak' => '19 Km'],
                ['label' => 'Pusat Perbelanjaan Mall', 'jarak' => '13 Km'],
                ['label' => 'Angkutan Umum (roda 4)', 'jarak' => '4 buah'],
            ],
            'usia' => [
                ['label' => '0-3', 'lk' => 12, 'pr' => 7],
                ['label' => '4-6', 'lk' => 11, 'pr' => 6],
                ['label' => '7-9', 'lk' => 8, 'pr' => 4],
                ['label' => '10-12', 'lk' => 13, 'pr' => 17],
                ['label' => '13-15', 'lk' => 12, 'pr' => 19],
                ['label' => '16-45', 'lk' => 171, 'pr' => 143],
                ['label' => '46-59', 'lk' => 61, 'pr' => 64],
                ['label' => '60-85', 'lk' => 35, 'pr' => 39],
                ['label' => '> 86', 'lk' => 2, 'pr' => 0],
            ],
            'total_kk' => 199,
            'ibadah' => [
                ['hari' => 'Minggu', 'nama' => 'Ibadah Minggu', 'tempat' => 'Gedung gereja', 'ket' => 'Dihadiri seluruh jemaat dari berbagai usia'],
                ['hari' => 'Minggu', 'nama' => 'Sekolah Minggu', 'tempat' => 'Gedung gereja', 'ket' => 'Dihadiri pengasuh dan anak SMTPI'],
                ['hari' => 'Minggu', 'nama' => 'Ibadah Pengasuh', 'tempat' => 'Rumah pengasuh', 'ket' => 'Sebulan 1x, sesuai jadwal'],
                ['hari' => 'Senin', 'nama' => 'Tunas Pekabaran Injil', 'tempat' => 'Rumah jemaat', 'ket' => 'Tiap jenjang SMTPI, bergiliran'],
                ['hari' => 'Senin', 'nama' => 'Batu Tungku', 'tempat' => 'Gereja, kantor negeri, sekolah', 'ket' => 'Sesuai jadwal'],
                ['hari' => 'Senin', 'nama' => 'Warga Gereja Senior', 'tempat' => 'Rumah WGS', 'ket' => 'Sesuai jadwal'],
                ['hari' => 'Selasa', 'nama' => 'Wadah Pelayanan Laki-laki', 'tempat' => 'Rumah anggota wadah', 'ket' => 'Dijadwalkan di tiap sektor'],
                ['hari' => 'Selasa', 'nama' => 'HIPPMAL', 'tempat' => 'Rumah anggota HIPPMAL', 'ket' => 'Himpunan Pemuda Pelajar asal Leahari'],
                ['hari' => 'Selasa / Rabu', 'nama' => 'Kemitraan', 'tempat' => 'Rumah jemaat', 'ket' => 'Dijadwalkan 2 bulan 1x'],
                ['hari' => 'Rabu', 'nama' => 'Wadah Pelayanan Perempuan', 'tempat' => 'Rumah anggota wadah', 'ket' => 'Dijadwalkan di tiap sektor'],
                ['hari' => 'Kamis', 'nama' => 'AMGPM', 'tempat' => 'Rumah anggota AMGPM', 'ket' => 'Sesuai jadwal'],
                ['hari' => 'Kamis', 'nama' => 'Keluarga MJ dan Tuagama', 'tempat' => 'Rumah MJ dan tuagama', 'ket' => 'Setiap bulan, minggu I'],
                ['hari' => 'Jumat', 'nama' => 'Ibadah Unit', 'tempat' => 'Rumah jemaat', 'ket' => 'Dilaksanakan di tiap sektor'],
            ],
        ];

        $totalJiwa = 0;
        foreach ($data['usia'] as $u) {
            $totalJiwa += $u['lk'] + $u['pr'];
        }

        $maxJumlah = max(array_map(fn($u) => $u['lk'] + $u['pr'], $data['usia']));
        foreach ($data['usia'] as &$u) {
            $u['jumlah'] = $u['lk'] + $u['pr'];
            $u['persen'] = $maxJumlah ? round($u['jumlah'] / $maxJumlah * 100) : 0;
        }
        unset($u);

        $data['total_jiwa'] = $totalJiwa;
        $data['luas_total'] = array_sum(array_column($data['sektor'], 'luas'));

        return view('publik/profil', $data);
    }

    public function berita(): string
    {
        return view('publik/berita', [
            'title' => 'Berita',
            'activePage' => 'berita',
            'news' => $this->publicNews(),
            'isSample' => $this->usesSampleNews(),
        ]);
    }

    public function jadwal(): string
    {
        return $this->page('jadwal', 'Jadwal Ibadah');
    }

    private function page(string $page, string $title): string
    {
        return view('publik/' . $page, [
            'title' => $title,
            'activePage' => $page,
            'news' => PublicSampleContent::news(),
            'schedules' => $this->publicSchedules(),
        ]);
    }

    private function publicNews(): array
    {
        if ($this->usesSampleNews()) {
            return PublicSampleContent::news();
        }

        $model = new BeritaModel();
        $rows = $model->findPublishedByTerm();

        return array_map(fn (array $row): array => [
            'id' => $row['id'],
            'title' => $row['title'],
            'category' => null,
            'date' => $this->formatNewsDate($row['created_at'] ?? null),
            'summary' => (string) ($row['summary'] ?? ''),
            'url' => site_url('berita') . '#berita-' . $row['id'],
        ], $rows);
    }

    private function usesSampleNews(): bool
    {
        $database = config('Database');
        $connection = $database->{$database->defaultGroup};

        return empty($connection['database']);
    }

    private function formatNewsDate(?string $date): string
    {
        if ($date === null || trim($date) === '') {
            return 'Tanggal belum tersedia';
        }

        $value = new \DateTimeImmutable($date);
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $value->format('j') . ' ' . $months[(int) $value->format('n')] . ' ' . $value->format('Y');
    }

    /** Bentuk seragam untuk kartu berita di Beranda. */
    private function normalizeNews(array $rows): array
    {
        return array_map(static fn(array $row): array => [
            'id' => $row['id'],
            'title' => $row['title'],
            'summary' => $row['summary'],
            'date' => $row['date'],
            'category' => $row['category'] ?? null,
            'url' => $row['url'] ?? site_url('berita') . '#berita-' . $row['id'],
            'image' => null,
        ], $rows);
    }

    private function publicSchedules(): array
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $timezone = new \DateTimeZone('Asia/Jayapura');
        $model = new JadwalIbadahModel();

        return array_map(static function (array $row) use ($days, $months, $timezone): array {
            $date = new \DateTimeImmutable((string) $row['tanggal'], $timezone);
            $dateLabel = $days[(int) $date->format('w')] . ', '
                . $date->format('j') . ' '
                . $months[(int) $date->format('n')] . ' '
                . $date->format('Y');
            $minister = trim((string) ($row['pelayan'] ?? ''));
            return [
                'name' => $row['jenis_ibadah'],
                'date' => $date->format('Y-m-d'),
                'date_label' => $dateLabel,
                'day_label' => $dateLabel,
                'dow' => mb_substr($days[(int) $date->format('w')], 0, 3),
                'dom' => $date->format('j'),
                'time' => str_replace(':', '.', substr((string) $row['waktu'], 0, 5)),
                'location' => $row['lokasi'],
                'minister' => $minister !== '' ? $minister : null,
                'type' => 'umum',
                'note' => '',
                'cancelled' => false,
            ];
        }, $model->findPublicSchedules());
    }
}