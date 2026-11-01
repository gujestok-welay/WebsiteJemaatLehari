<?php

namespace App\Controllers;

use App\Libraries\PublicSampleContent;

class Home extends BaseController
{
    public function index(): string
    {
        $schedules = $this->normalizeSchedules(PublicSampleContent::schedules());

        return view('publik/beranda', [
            'title'          => 'Beranda',
            'activePage'     => 'beranda',
            'isSample'       => true, // jadi false saat data database dipakai (Langkah 7)
            'news'           => $this->normalizeNews(array_slice(PublicSampleContent::news(), 0, 2)),
            'schedules'      => array_slice($schedules, 0, 4),
            'nextService'    => $schedules[0] ?? null,
            'majelis'        => [],
            'majelisPeriode' => null,
        ]);
    }

    public function profil(): string
    {
        return $this->page('profil', 'Profil');
    }

    public function berita(): string
    {
        return $this->page('berita', 'Berita');
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
            'schedules' => PublicSampleContent::schedules(),
        ]);
    }

    /** Bentuk seragam untuk kartu berita di Beranda. */
    private function normalizeNews(array $rows): array
    {
        return array_map(static fn (array $row): array => [
            'title'    => $row['title'],
            'summary'  => $row['summary'],
            'date'     => $row['date'],
            'category' => $row['category'] ?? null,
            'url'      => site_url('berita') . '#' . $row['id'],
            'image'    => null,
        ], $rows);
    }

    /** Bentuk seragam untuk daftar jadwal di Beranda. */
    private function normalizeSchedules(array $rows): array
    {
        $dow = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        return array_map(static function (array $row) use ($dow): array {
            $date = new \DateTimeImmutable($row['date']);

            return [
                'name'      => $row['name'],
                'date'      => $row['date'],
                'day_label' => $row['day'],
                'dow'       => $dow[(int) $date->format('w')],
                'dom'       => $date->format('j'),
                'time'      => $row['time'],
                'location'  => $row['location'],
                'minister'  => $row['minister'] ?? null,
                'cancelled' => false,
            ];
        }, $rows);
    }
}