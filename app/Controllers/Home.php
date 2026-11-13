<?php

namespace App\Controllers;

use App\Libraries\PublicSampleContent;
use App\Libraries\PublicProfileContent;
use App\Libraries\ProfileDashboardModules;
use App\Models\BeritaModel;
use App\Models\KegiatanMediaModel;
use App\Models\KegiatanModel;
use App\Models\JadwalIbadahModel;
use App\Models\MajelisAnggotaModel;
use App\Models\MajelisPeriodeModel;
use App\Models\ProfilJemaatModel;

class Home extends BaseController
{
    public function index(): string
    {
        $schedules = $this->publicSchedules();
        $upcomingSchedules = $this->upcomingSchedules($schedules);
        $news = $this->publicNews();
        $databaseConfigured = ! $this->usesSampleNews();
        $profile = $databaseConfigured ? ((new ProfilJemaatModel())->first() ?? []) : [];
        $profileContent = PublicProfileContent::fromProfile($profile);
        $majelisPeriode = $databaseConfigured ? (new MajelisPeriodeModel())->where('is_active', 1)->first() : null;
        $showMembers = ! empty($profileContent['leadership']['show_members']);
        $majelis = $databaseConfigured && $showMembers && $majelisPeriode !== null
            ? (new MajelisAnggotaModel())->findByPeriode((int) $majelisPeriode['id'])
            : [];
        $featuredActivities = $databaseConfigured
            ? $this->formatGalleryActivities((new KegiatanModel())->findFeaturedForHome())
            : [];

        return view('publik/beranda', [
            'title' => 'Beranda',
            'activePage' => 'beranda',
            'isSample' => $this->usesSampleNews(),
            'news' => $this->normalizeNews(array_slice($news, 0, 3)),
            'featuredActivities' => $featuredActivities,
            'schedules' => array_slice($upcomingSchedules, 0, 4),
            'nextService' => $upcomingSchedules[0] ?? null,
            'majelis' => $majelis,
            'majelisPeriode' => $majelisPeriode,
            'leadershipTitle' => $profileContent['leadership']['members_title'],
            'leadershipEmpty' => $profileContent['leadership']['members_empty'],
        ]);
    }

    public function profil(): string
    {
        $databaseConfigured = ! $this->usesSampleNews();
        $profile = $databaseConfigured ? ((new ProfilJemaatModel())->first() ?? []) : [];
        $content = PublicProfileContent::fromProfile($profile);
        $ageRows = array_map(static function (array $row): array {
            $row['male'] = (int) $row['male'];
            $row['female'] = (int) $row['female'];
            $row['total'] = $row['male'] + $row['female'];

            return $row;
        }, $content['census']['age_rows']);
        $censusSummary = ProfileDashboardModules::censusSummary($content['census']);
        $totalJiwa = $censusSummary['totalJiwa'];
        $largestAgeGroup = $ageRows === [] ? 0 : max(array_column($ageRows, 'total'));
        foreach ($ageRows as &$row) {
            $row['share'] = $largestAgeGroup > 0 ? (int) round($row['total'] / $largestAgeGroup * 100) : 0;
        }
        unset($row);
        $content['stats'][0]['value'] = (string) $totalJiwa;
        $content['stats'][1]['value'] = (string) $censusSummary['householdCount'];

        $activePeriod = $databaseConfigured ? (new MajelisPeriodeModel())->where('is_active', 1)->first() : null;
        $showMembers = ! empty($content['leadership']['show_members']);
        $members = $databaseConfigured && $showMembers && $activePeriod !== null
            ? (new MajelisAnggotaModel())->findByPeriode((int) $activePeriod['id'])
            : [];
        $areaTotal = array_sum(array_column($content['territory']['sectors'], 'area'));

        return view('publik/profil', [
            'title' => $content['hero']['page_title'],
            'metaDescription' => $content['hero']['meta_description'],
            'profilePreviewNotice' => [
                'lead' => $content['hero']['preview_notice_lead'],
                'body' => $content['hero']['preview_notice_body'],
            ],
            'activePage' => 'profil',
            'content' => $content,
            'profile' => $profile,
            'ageRows' => $ageRows,
            'totalJiwa' => $totalJiwa,
            'largestAgeGroups' => $censusSummary['largestAgeGroups'],
            'largestAgeGroupCount' => $censusSummary['largestAgeGroupCount'],
            'serviceMapPath' => ! empty($profile['service_map_path'])
                ? (string) $profile['service_map_path']
                : 'assets/images/gereja/Peta Pelayanan.png',
            'serviceMapTitle' => trim((string) ($profile['service_map_title'] ?? '')) !== ''
                ? (string) $profile['service_map_title']
                : 'Peta Pelayanan',
            'serviceMapAlt' => trim((string) ($profile['service_map_alt'] ?? '')) !== ''
                ? (string) $profile['service_map_alt']
                : 'Peta pelayanan Jemaat Leahari',
            'areaTotal' => $areaTotal,
            'showMembers' => $showMembers,
            'activePeriod' => $activePeriod,
            'members' => $members,
        ]);
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

    public function galeri(): string
    {
        $activities = $this->usesSampleNews() ? [] : (new KegiatanModel())->findPublished();

        return view('publik/galeri', [
            'title' => 'Galeri Kegiatan',
            'activePage' => 'galeri',
            'activities' => $this->formatGalleryActivities($activities),
        ]);
    }

    public function galeriDetail(string $slug): string
    {
        $activity = $this->usesSampleNews()
            ? null
            : (new KegiatanModel())->findPublishedBySlug($slug);

        if ($activity === null) {
            $this->response->setStatusCode(404);

            return view('publik/galeri_tidak_ditemukan', [
                'title' => 'Kegiatan tidak ditemukan',
                'activePage' => 'galeri',
            ]);
        }

        $media = (new KegiatanMediaModel())->findByKegiatan((int) $activity['id']);
        $activity = $this->formatGalleryActivities([$activity])[0];
        $activity['media'] = $this->formatGalleryMedia($media);

        return view('publik/galeri_detail', [
            'title' => $activity['title'],
            'activePage' => 'galeri',
            'activity' => $activity,
        ]);
    }

    public function galeriMedia(string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        if (
            $this->usesSampleNews()
            || preg_match('~\A[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)\z~i', $filename) !== 1
        ) {
            return $this->response->setStatusCode(404);
        }

        $storedPath = 'uploads/galeri/' . $filename;
        $activity = (new KegiatanModel())
            ->where('status', 'Terbit')
            ->where('poster_path', $storedPath)
            ->first();
        $media = $activity === null
            ? (new KegiatanMediaModel())->findPublishedByPath($storedPath)
            : true;

        if ($media === null || $media === false) {
            return $this->response->setStatusCode(404);
        }

        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'galeri' . DIRECTORY_SEPARATOR . $filename;
        if (! is_file($filePath) || ! is_readable($filePath)) {
            log_message('error', 'Gambar galeri terbit tidak tersedia: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(404);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            log_message('error', 'Tipe gambar galeri tidak didukung: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(404);
        }

        $image = @file_get_contents($filePath);
        if ($image === false) {
            log_message('error', 'Gagal membaca gambar galeri terbit: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(500);
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody($image);
    }

    private function formatGalleryActivities(array $activities): array
    {
        return array_map(function (array $activity): array {
            $activity['poster_url'] = $this->galleryMediaUrl((string) ($activity['poster_path'] ?? ''));
            $activity['date_label'] = $this->formatGalleryDate($activity['event_date'] ?? null);
            $activity['video_url'] = $this->safeExternalUrl((string) ($activity['video_url'] ?? ''));

            return $activity;
        }, $activities);
    }

    private function formatGalleryMedia(array $media): array
    {
        return array_values(array_filter(array_map(function (array $item): array {
            $item['url'] = $this->galleryMediaUrl((string) ($item['file_path'] ?? ''));
            $item['label'] = KegiatanModel::mediaTypeOptions()[$item['media_type'] ?? ''] ?? 'Foto';

            return $item;
        }, $media), static fn(array $item): bool => $item['url'] !== null));
    }

    private function galleryMediaUrl(string $storedPath): ?string
    {
        if (preg_match('~\Auploads/galeri/([A-Za-z0-9._-]+\.(?:jpe?g|png|webp))\z~i', $storedPath, $matches) !== 1) {
            return null;
        }

        return site_url('galeri/media/' . rawurlencode($matches[1]));
    }

    private function formatGalleryDate(?string $date): ?string
    {
        if ($date === null || trim($date) === '') {
            return null;
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

    private function safeExternalUrl(string $url): ?string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }

    public function beritaDetail(string $slug): string
    {
        $item = $this->usesSampleNews()
            ? null
            : (new BeritaModel())
                ->where('slug', $slug)
                ->where('status', 'Terbit')
                ->first();

        if ($item === null) {
            $this->response->setStatusCode(404);

            return view('publik/berita_tidak_ditemukan', [
                'title' => 'Berita tidak ditemukan',
                'activePage' => 'berita',
            ]);
        }

        $date = $item['created_at'] ?? $item['updated_at'] ?? null;
        $coverImageUrl = $this->newsCoverImageUrl($item['cover_image'] ?? null);

        return view('publik/berita_detail', [
            'title' => $item['title'],
            'activePage' => 'berita',
            'item' => $item,
            'isExternal' => ($item['article_type'] ?? 'internal') === 'external',
            'sourceUrl' => ($item['article_type'] ?? 'internal') === 'external'
                ? $this->safeExternalUrl((string) ($item['source_url'] ?? ''))
                : null,
            'dateLabel' => $this->formatNewsDate($date),
            'dateTime' => $date !== null ? substr((string) $date, 0, 10) : null,
            'coverImageUrl' => $coverImageUrl,
            'isSample' => $this->containsPlaceholderCopy($item),
        ]);
    }

    public function beritaGambar(string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        if (
            $this->usesSampleNews()
            || preg_match('~\A[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)\z~i', $filename) !== 1
        ) {
            return $this->response->setStatusCode(404);
        }

        $storedPath = 'uploads/berita/' . $filename;
        $item = (new BeritaModel())
            ->where('status', 'Terbit')
            ->where('cover_image', $storedPath)
            ->first();

        if ($item === null) {
            return $this->response->setStatusCode(404);
        }

        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'berita' . DIRECTORY_SEPARATOR . $filename;
        if (! is_file($filePath) || ! is_readable($filePath)) {
            log_message('error', 'Gambar berita terbit tidak tersedia: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(404);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            log_message('error', 'Tipe gambar berita terbit tidak didukung: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(404);
        }

        $image = @file_get_contents($filePath);
        if ($image === false) {
            log_message('error', 'Gagal membaca gambar berita terbit: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(500);
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($image);
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

        return array_map(fn(array $row): array => [
            'id' => $row['id'],
            'slug' => $row['slug'],
            'title' => $row['title'],
            'category' => ($row['article_type'] ?? 'internal') === 'external' ? 'Berita eksternal' : null,
            'date' => $this->formatNewsDate($row['updated_at'] ?? $row['created_at'] ?? null),
            'summary' => (string) ($row['summary'] ?? ''),
            'image' => $this->newsCoverImageUrl($row['cover_image'] ?? null),
            'url' => site_url('berita/' . $row['slug']),
            'source_name' => (string) ($row['source_name'] ?? ''),
            'is_sample' => $this->containsPlaceholderCopy($row),
        ], $rows);
    }

    private function newsCoverImageUrl(?string $storedPath): ?string
    {
        if (preg_match('~\Auploads/berita/([A-Za-z0-9._-]+\.(?:jpe?g|png|webp))\z~i', $storedPath ?? '', $matches) !== 1) {
            return null;
        }

        return site_url('berita/gambar/' . rawurlencode($matches[1]));
    }

    private function containsPlaceholderCopy(array $row): bool
    {
        $copy = implode(' ', [
            (string) ($row['title'] ?? ''),
            (string) ($row['summary'] ?? ''),
            (string) ($row['content'] ?? ''),
        ]);

        return preg_match('/\blorem\s+ipsum\b/i', $copy) === 1;
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
            'slug' => $row['slug'] ?? null,
            'title' => $row['title'],
            'summary' => $row['summary'],
            'date' => $row['date'],
            'category' => $row['category'] ?? null,
            'url' => $row['url'] ?? site_url('berita/' . ($row['slug'] ?? $row['id'])),
            'image' => $row['image'] ?? null,
            'is_sample' => ! empty($row['is_sample']),
        ], $rows);
    }

    private function publicSchedules(): array
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
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
                'note' => $row['keterangan'] ?? '',
                'cancelled' => false,
            ];
        }, $model->findPublicSchedules());
    }

    private function upcomingSchedules(array $schedules): array
    {
        $timezone = new \DateTimeZone('Asia/Jayapura');
        $now = new \DateTimeImmutable('now', $timezone);
        $upcoming = array_values(array_filter(
            $schedules,
            static function (array $schedule) use ($now, $timezone): bool {
                $startsAt = new \DateTimeImmutable(
                    $schedule['date'] . ' ' . str_replace('.', ':', $schedule['time']),
                    $timezone
                );

                return $startsAt >= $now;
            }
        ));

        usort(
            $upcoming,
            static fn (array $first, array $second): int => strcmp(
                $first['date'] . ' ' . $first['time'],
                $second['date'] . ' ' . $second['time']
            )
        );

        return $upcoming;
    }
}
