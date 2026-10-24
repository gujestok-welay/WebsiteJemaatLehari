<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return $this->page('beranda', 'Beranda');
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
            'news' => \App\Libraries\PublicSampleContent::news(),
            'schedules' => \App\Libraries\PublicSampleContent::schedules(),
        ]);
    }
}
