<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminDashboardViewTest extends CIUnitTestCase
{
    public function testLatestNewsTitleIsReadOnlyTextOnDashboard(): void
    {
        $html = view('admin/dashboard', [
            'name' => 'Admin',
            'todayLabel' => 'Kamis, 5 November 2026',
            'publishedNewsCount' => 1,
            'draftNewsCount' => 0,
            'upcomingScheduleCount' => 0,
            'latestNews' => [[
                'id' => 4,
                'title' => 'Berita ringkasan',
                'slug' => 'berita-ringkasan',
                'summary' => 'Ringkasan berita',
                'content' => 'Isi berita',
                'status' => 'Terbit',
                'cover_image' => '',
                'date_label' => '8 November 2026',
            ]],
            'nextSchedule' => null,
            'scheduleError' => '',
            'newsError' => '',
            'success' => null,
            'error' => null,
            'newsModalOpen' => false,
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        $this->assertStringContainsString('<h3 class="news-title">Berita ringkasan</h3>', $html);
        $this->assertStringNotContainsString('button.news-title', $html);
        $this->assertStringNotContainsString('data-news-edit=', $html);
        $this->assertStringNotContainsString('aria-label="Ubah berita:', $html);
    }

    public function testDashboardShowsLiveClockWithoutTimezoneLabel(): void
    {
        $html = view('admin/dashboard', [
            'name' => 'Admin',
            'todayLabel' => 'Kamis, 5 November 2026',
            'publishedNewsCount' => 0,
            'draftNewsCount' => 0,
            'upcomingScheduleCount' => 0,
            'latestNews' => [],
            'nextSchedule' => null,
            'scheduleError' => '',
            'newsError' => '',
            'success' => null,
            'error' => null,
            'newsModalOpen' => false,
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        $this->assertStringContainsString('id="admin-live-clock"', $html);
        $this->assertStringNotContainsString('admin-timezone', $html);
        $this->assertStringNotContainsString('resolvedOptions().timeZone', $html);
    }

    public function testDashboardDoesNotShowScheduleListLinkInNextEvent(): void
    {
        $html = view('admin/dashboard', [
            'name' => 'Admin',
            'todayLabel' => 'Kamis, 5 November 2026',
            'publishedNewsCount' => 0,
            'draftNewsCount' => 0,
            'upcomingScheduleCount' => 1,
            'latestNews' => [],
            'nextSchedule' => [
                'date_label' => 'Minggu, 8 November 2026',
                'jenis_ibadah' => 'Ibadah Minggu',
                'time_label' => '09.00',
                'lokasi' => 'Gereja',
            ],
            'scheduleError' => '',
            'newsError' => '',
            'success' => null,
            'error' => null,
            'newsModalOpen' => false,
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        $this->assertStringNotContainsString('Periksa semua jadwal', $html);
    }

    public function testDashboardMetricsKeepCountsWithoutActionLinks(): void
    {
        $html = view('admin/dashboard', [
            'name' => 'Admin',
            'todayLabel' => 'Kamis, 5 November 2026',
            'publishedNewsCount' => 4,
            'draftNewsCount' => 2,
            'upcomingScheduleCount' => 3,
            'latestNews' => [],
            'nextSchedule' => null,
            'scheduleError' => '',
            'newsError' => '',
            'success' => null,
            'error' => null,
            'newsModalOpen' => false,
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        self::assertSame(1, preg_match('/<section class="metrics"[^>]*>(.*?)<\/section>/s', $html, $metrics));

        $this->assertStringNotContainsString('<a ', $metrics[1]);
        $this->assertStringContainsString('Berita terbit', $metrics[1]);
        $this->assertStringContainsString('Draf berita', $metrics[1]);
        $this->assertStringContainsString('Jadwal mendatang', $metrics[1]);
        foreach (['>4</strong>', '>2</strong>', '>3</strong>'] as $count) {
            $this->assertStringContainsString($count, $metrics[1]);
        }

        $this->assertStringContainsString('Semua berita', $html);
    }
}
